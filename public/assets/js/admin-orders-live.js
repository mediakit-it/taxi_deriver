/* Живой список заказов админки (страница «Заказы»).
   Обновление без перезагрузки двумя путями:
   1) сигнал Pusher 'orders.changed' в приватном канале admin.alerts
      (событие AdminOrdersChanged, шлёт OrderObserver при создании
      заказа и смене его статуса, водителя или цены) - подписка идёт
      через window.onOrdersChanged из admin-alerts.js, единственной
      точки Pusher в админке;
   2) запасной опрос того же фрагмента раз в 30 секунд.
   Сервер отдаёт фрагмент (плашки-сводка + таблица) по GET на адрес
   списка с текущими фильтрами из URL и параметром fragment=1;
   содержимое #orders-live подменяется на месте. Пока диспетчер
   правит строку (фокус в селекте/поле внутри списка), подмена
   откладывается, чтобы не сбить ввод. */
(function () {
    "use strict";

    var POLL_INTERVAL = 30000;
    var SIGNAL_DEBOUNCE = 350;

    var config = window.adminOrdersLiveConfig || {};
    var container = null;
    var currentPage = 1;
    var inFlight = false;
    var refreshPending = false;
    var debounceTimer = null;

    function csrfToken() {
        if (config.csrf) {
            return config.csrf;
        }
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute("content") : "";
    }

    function toast(title, text, type) {
        if (typeof window.adminToast === "function") {
            window.adminToast(title, text || "", type || "info");
        }
    }

    function buildUrl(page) {
        var params = new URLSearchParams(window.location.search);
        params.delete("fragment");
        if (page && page > 1) {
            params.set("page", String(page));
        } else {
            params.delete("page");
        }
        params.set("fragment", "1");
        return config.indexUrl + "?" + params.toString();
    }

    function isEditing() {
        if (!container) {
            return false;
        }
        var el = document.activeElement;
        return !!(el && container.contains(el)
            && (el.tagName === "SELECT" || el.tagName === "INPUT" || el.tagName === "TEXTAREA"));
    }

    function refresh() {
        if (!container) {
            return;
        }
        if (inFlight || isEditing()) {
            refreshPending = true;
            return;
        }
        inFlight = true;

        var scrollY = window.scrollY;

        fetch(buildUrl(currentPage), {
            credentials: "same-origin",
            headers: { "Accept": "text/html" }
        }).then(function (resp) {
            return resp.ok ? resp.text() : null;
        }).then(function (html) {
            if (html !== null && container) {
                container.innerHTML = html;
                if (window.scrollY !== scrollY) {
                    window.scrollTo(0, scrollY);
                }
            }
        }).catch(function () {
            /* сеть моргнула - следующий сигнал или опрос подтянет */
        }).then(function () {
            inFlight = false;
            if (refreshPending) {
                refreshPending = false;
                scheduleRefresh();
            }
        });
    }

    function scheduleRefresh() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(refresh, SIGNAL_DEBOUNCE);
    }

    /* ---------- быстрые действия в строках ---------- */

    function postForm(url, formData, onOk, onError) {
        fetch(url, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": csrfToken(),
                "Accept": "application/json"
            },
            body: formData
        }).then(function (resp) {
            return resp.json().catch(function () { return {}; }).then(function (body) {
                return { ok: resp.ok, status: resp.status, body: body };
            });
        }).then(function (result) {
            if (result.ok) {
                onOk(result.body);
            } else {
                onError(result);
            }
        }).catch(function () {
            onError({ status: 0, body: {} });
        });
    }

    function firstErrorText(result) {
        var errors = result && result.body && result.body.errors;
        if (errors) {
            var keys = Object.keys(errors);
            if (keys.length && errors[keys[0]] && errors[keys[0]][0]) {
                return errors[keys[0]][0];
            }
        }
        return "Не удалось сохранить. Попробуйте ещё раз.";
    }

    function onAssignChange(select) {
        var form = select.closest("[data-assign-form]");
        if (!form) {
            return;
        }
        var orderId = form.getAttribute("data-order-id");
        var url = (config.assignUrlTemplate || "").replace("__ORDER__", orderId);
        var data = new FormData();
        data.append("driver_id", select.value);

        select.disabled = true;
        postForm(url, data, function () {
            toast("Водитель обновлён", "Заказ №" + orderId, "success");
            scheduleRefresh();
        }, function (result) {
            toast("Ошибка назначения", firstErrorText(result), "danger");
            scheduleRefresh();
        });
    }

    function onPriceSubmit(form, event) {
        event.preventDefault();
        var orderId = form.getAttribute("data-order-id");
        var url = (config.priceUrlTemplate || "").replace("__ORDER__", orderId);
        var input = form.querySelector('input[name="price"]');
        var button = form.querySelector('button[type="submit"]');
        var data = new FormData();
        data.append("price", input ? input.value : "");

        if (button) {
            button.disabled = true;
        }
        postForm(url, data, function () {
            toast("Цена обновлена", "Заказ №" + orderId, "success");
            scheduleRefresh();
        }, function (result) {
            if (button) {
                button.disabled = false;
            }
            toast("Ошибка цены", firstErrorText(result), "danger");
        });
    }

    /* ---------- события внутри живой зоны (делегирование) ---------- */

    function bindContainerEvents() {
        container.addEventListener("change", function (event) {
            var select = event.target.closest("[data-assign-select]");
            if (select) {
                onAssignChange(select);
            }
        });

        container.addEventListener("submit", function (event) {
            var form = event.target.closest("[data-price-form]");
            if (form) {
                onPriceSubmit(form, event);
            }
        });

        container.addEventListener("click", function (event) {
            var chip = event.target.closest("[data-status-chip]");
            if (chip) {
                var params = new URLSearchParams(window.location.search);
                params.set("status", chip.getAttribute("data-status-chip"));
                params.delete("page");
                params.delete("fragment");
                window.location.href = config.indexUrl + "?" + params.toString();
                return;
            }

            var link = event.target.closest(".pagination a");
            if (link) {
                event.preventDefault();
                var href = link.getAttribute("href");
                if (!href) {
                    return;
                }
                var page = new URLSearchParams(href.split("?")[1] || "").get("page");
                currentPage = page ? parseInt(page, 10) || 1 : 1;
                refresh();
            }
        });

        // Отложенное из-за правки обновление - сразу, как фокус ушёл.
        container.addEventListener("focusout", function () {
            if (refreshPending && !isEditing()) {
                scheduleRefresh();
            }
        });
    }

    /* ---------- старт ---------- */

    function init() {
        container = document.getElementById("orders-live");
        if (!container || !config.indexUrl) {
            return;
        }

        var page = new URLSearchParams(window.location.search).get("page");
        currentPage = page ? parseInt(page, 10) || 1 : 1;

        bindContainerEvents();

        if (typeof window.onOrdersChanged === "function") {
            window.onOrdersChanged(function () {
                scheduleRefresh();
            });
        }

        setInterval(refresh, POLL_INTERVAL);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
