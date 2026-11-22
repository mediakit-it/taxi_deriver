/* Realtime-сигналы диспетчера в админ-панели.
   Единственная глобальная точка Pusher для мастера (window.adminPusher):
   1) публичный канал 'admin' / событие 'sendMailAdmin' - новые сообщения
      тех. поддержки (логика перенесена из инлайн-блока мастера без
      изменений; на не-мессенджер страницах вместо удалённого с темой
      not7 теперь тост + звук);
   2) приватный канал 'admin.alerts' / событие 'admin.alert' - сигналы
      диспетчеру: водитель не ответил (driver_no_response), водитель
      отклонил заказ (driver_declined), новый заказ (new_order), новое
      сообщение (new_message).
   Сигналы driver_no_response и driver_declined живут в панели
   «Требуют внимания» БЕЗ кнопки закрытия: карточка заказа исчезает
   только когда заказ перестаёт быть проблемным. Эндпоинт
   GET /admin/data/overdue-orders возвращает оба типа проблем
   (поле reason: no_response / declined), синхронизация с ним -
   раз в минуту и при загрузке.
   Плюс: window.adminToast(title, text, type, url) - тосты для вьюх
   (замена not7/notif из удалённой темы).
   Сигналы о новом проблемном заказе (signalNewAlert) общие для
   живого события и для ежеминутной синхронизации, если та увидела
   РОСТ числа карточек (включая первую загрузку страницы, когда
   карточки уже ждут): звук; системное уведомление Notifications
   API (разрешение - по кнопке «Включить уведомления» в шапке
   панели); пока карточки живы, вкладка браузера мигает
   заголовком и красной фавиконкой.
   Тост о новом сообщении содержит имя отправителя и превью
   текста, ссылка «Открыть» ведёт в его диалог
   (/admin/messenger?user={id} - страница сама открывает диалог).
   Бейдж непрочитанных на пункте «Сообщения» в сайдбаре:
   источник правды - GET /admin/data/unread-messages (сообщения
   пользователей с is_active = 0), пересчёт при загрузке, по
   каждому событию sendMailAdmin и раз в минуту.
   Ключ и кластер Pusher берутся из meta-тегов мастера
   (config broadcasting - тот же набор, которым вещает сервер);
   константы ниже - только запасной вариант. */
(function () {
    "use strict";

    var PUSHER_KEY = "53901edd70c280d59a2f";
    var PUSHER_CLUSTER = "mt1";
    var ALERTS_AUTH_ENDPOINT = "/admin/broadcasting/auth";
    var OVERDUE_URL = "/admin/data/overdue-orders";
    var UNREAD_URL = "/admin/data/unread-messages";
    var SYNC_INTERVAL = 60000;
    var TICK_INTERVAL = 30000;
    var TOAST_TTL = 12000;

    /* ---------- служебное ---------- */

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute("content")) {
            return meta.getAttribute("content");
        }
        var input = document.getElementById("csrf_token");
        return input ? input.value : "";
    }

    function escapeHtml(value) {
        return String(value == null ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function pad2(n) {
        return (n < 10 ? "0" : "") + n;
    }

    function formatWaiting(totalSeconds) {
        var s = Math.max(0, Math.floor(totalSeconds || 0));
        var h = Math.floor(s / 3600);
        var m = Math.floor((s % 3600) / 60);
        var sec = s % 60;
        if (h > 0) {
            return h + ":" + pad2(m) + ":" + pad2(sec);
        }
        return pad2(m) + ":" + pad2(sec);
    }

    // Время строкой HH:MM - использует обработчик сообщений ниже
    // (раньше жила инлайн в мастере, оставлена глобальной для совместимости).
    function getCurrentTime() {
        var now = new Date();
        var minutes = now.getMinutes();
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        return now.getHours() + ":" + minutes;
    }
    window.getCurrentTime = getCurrentTime;

    /* ---------- звук: двухтональный бип WebAudio, без аудиофайлов ---------- */

    var audioCtx = null;

    function ensureAudio() {
        if (!audioCtx) {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (AC) {
                try {
                    audioCtx = new AC();
                } catch (e) {
                    audioCtx = null;
                }
            }
        }
        if (audioCtx && audioCtx.state === "suspended") {
            audioCtx.resume();
        }
    }

    function beep() {
        if (!audioCtx || audioCtx.state !== "running") {
            return;
        }
        try {
            var start = audioCtx.currentTime;
            [880, 659].forEach(function (freq, i) {
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();
                var t = start + i * 0.18;
                osc.type = "sine";
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, t);
                gain.gain.exponentialRampToValueAtTime(0.18, t + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.16);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(t);
                osc.stop(t + 0.18);
            });
        } catch (e) {
            /* звук не критичен - молча пропускаем */
        }
    }

    /* ---------- системные уведомления (Notifications API) ---------- */

    /* Запасной заголовок уведомления: название компании из шапки
       админки (.admin-brand в сайдбаре - то же название из «Настроек»,
       что видит диспетчер); нет элемента или он пуст - «Такси». */
    function fallbackNotifyTitle() {
        var brand = document.querySelector(".admin-brand");
        var name = brand ? (brand.textContent || "").trim() : "";
        return name || "Такси";
    }

    function systemNotify(title, text, tag, url) {
        if (!("Notification" in window) || Notification.permission !== "granted") {
            return;
        }
        try {
            var n = new Notification(title || fallbackNotifyTitle(), {
                body: text || "",
                tag: tag || undefined
            });
            if (url) {
                n.onclick = function () {
                    try {
                        window.focus();
                    } catch (e) {}
                    window.location.href = url;
                    n.close();
                };
            }
        } catch (e) {
            /* пропускаем */
        }
    }

    // Общий некритичный сигнал (новый заказ, новое сообщение):
    // попытка разбудить звук + системное уведомление.
    function signalInfo(title, text, tag, url) {
        ensureAudio();
        beep();
        systemNotify(title, text, tag, url);
    }

    // Общий сигнал «новый проблемный заказ» панели внимания: звук +
    // системное уведомление. Тег уведомления - по id заказа, поэтому
    // повторный сигнал по тому же заказу заменяет уведомление,
    // а не складывает стопку дублей.
    function signalNewAlert(info) {
        info = info || {};
        var body = info.text || "";
        if (info.from_address || info.to_address) {
            var route = (info.from_address || "…") + " → " + (info.to_address || "…");
            body = body ? body + "\n" + route : route;
        }
        if (!body && info.order_id != null) {
            body = "Заказ №" + info.order_id;
        }
        signalInfo(
            info.title || fallbackNotifyTitle(),
            body,
            info.order_id != null ? "admin-attention-" + info.order_id : undefined,
            info.url
        );
    }

    /* Кнопка «Включить уведомления» в шапке панели: разрешение на
       системные уведомления браузер даёт только по явному клику,
       сам по себе скрипт его не запрашивает. Кнопка видна, только
       пока разрешение ещё не выдано и не отклонено. */
    var notifyBtn = null;

    function syncNotifyButton() {
        var head = document.querySelector(".admin-alerts-panel__title");
        if (!head) {
            return;
        }
        var show = ("Notification" in window) && Notification.permission === "default";
        if (show && !notifyBtn) {
            notifyBtn = document.createElement("button");
            notifyBtn.type = "button";
            notifyBtn.className = "admin-alerts-panel__notify";
            notifyBtn.style.marginLeft = "8px";
            notifyBtn.style.cursor = "pointer";
            notifyBtn.textContent = "Включить уведомления";
            notifyBtn.addEventListener("click", function () {
                try {
                    var p = Notification.requestPermission();
                    if (p && p.then) {
                        p.then(syncNotifyButton, syncNotifyButton);
                    } else {
                        setTimeout(syncNotifyButton, 300);
                    }
                } catch (e) {
                    syncNotifyButton();
                }
            });
            head.appendChild(notifyBtn);
        } else if (!show && notifyBtn && notifyBtn.parentNode) {
            notifyBtn.parentNode.removeChild(notifyBtn);
            notifyBtn = null;
        }
    }

    function onFirstInteraction() {
        ensureAudio();
    }
    document.addEventListener("pointerdown", onFirstInteraction);
    document.addEventListener("keydown", onFirstInteraction);

    /* ---------- тосты ---------- */

    function dismissToast(el) {
        if (!el || !el.parentNode) {
            return;
        }
        el.classList.add("admin-toast--hide");
        setTimeout(function () {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }, 300);
    }

    window.adminToast = function (title, text, type, url) {
        var box = document.getElementById("admin-toasts");
        if (!box) {
            return;
        }
        var el = document.createElement("div");
        el.className = "admin-toast admin-toast--" + (type || "info");

        var html = '<div class="admin-toast__body">';
        if (title) {
            html += '<div class="admin-toast__title">' + escapeHtml(title) + "</div>";
        }
        if (text) {
            html += '<div class="admin-toast__text">' + escapeHtml(text) + "</div>";
        }
        if (url) {
            html += '<a class="admin-toast__link" href="' + escapeHtml(url) + '">Открыть</a>';
        }
        html += "</div>";
        html += '<button type="button" class="admin-toast__close" aria-label="Закрыть">&times;</button>';
        el.innerHTML = html;

        el.querySelector(".admin-toast__close").addEventListener("click", function () {
            dismissToast(el);
        });
        box.appendChild(el);
        setTimeout(function () {
            dismissToast(el);
        }, TOAST_TTL);
    };

    /* ---------- мигание вкладки, пока есть живые карточки ---------- */

    var originalTitle = null;
    var faviconState = null; // {el, href}
    var flashTimer = null;
    var flashAlt = false;
    var RED_FAVICON = "data:image/svg+xml," + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
        + '<circle cx="32" cy="32" r="30" fill="#e3342f"/>'
        + '<rect x="29" y="13" width="6" height="29" rx="3" fill="#ffffff"/>'
        + '<circle cx="32" cy="50" r="4.5" fill="#ffffff"/></svg>'
    );

    function captureTitleAndFavicon() {
        originalTitle = document.title;
        var el = document.querySelector('link[rel~="icon"]');
        if (!el) {
            el = document.createElement("link");
            el.setAttribute("rel", "icon");
            document.head.appendChild(el);
        }
        faviconState = { el: el, href: el.getAttribute("href") };
    }

    function setFaviconAlert(on) {
        if (!faviconState) {
            return;
        }
        if (on) {
            faviconState.el.setAttribute("href", RED_FAVICON);
        } else if (faviconState.href) {
            faviconState.el.setAttribute("href", faviconState.href);
        } else {
            faviconState.el.removeAttribute("href");
        }
    }

    function updateTabFlash() {
        var n = Object.keys(attention).length;
        if (n > 0) {
            if (!flashTimer) {
                setFaviconAlert(true);
                flashAlt = false;
                flashTimer = setInterval(function () {
                    flashAlt = !flashAlt;
                    document.title = flashAlt
                        ? "🔴 Требуют внимания: " + Object.keys(attention).length
                        : originalTitle;
                }, 1000);
            }
        } else if (flashTimer) {
            clearInterval(flashTimer);
            flashTimer = null;
            document.title = originalTitle;
            setFaviconAlert(false);
        }
    }

    /* ---------- панель «Требуют внимания» ---------- */

    // order_id -> {el, waitEl, baseWaiting, setAt, type}
    var attention = {};

    function panelEls() {
        return {
            panel: document.getElementById("admin-alerts-panel"),
            list: document.getElementById("admin-alerts-list"),
            count: document.getElementById("admin-alerts-count")
        };
    }

    function syncPanelVisibility() {
        updateTabFlash();
        syncNotifyButton();
        var els = panelEls();
        if (!els.panel) {
            return;
        }
        var n = Object.keys(attention).length;
        els.panel.hidden = n === 0;
        if (els.count) {
            els.count.textContent = n;
        }
    }

    function defaultCardTitle(type) {
        return type === "driver_declined" ? "Водитель отклонил заказ" : "Водитель не ответил";
    }

    function upsertAttentionCard(data, silent) {
        var els = panelEls();
        if (!els.list || data.order_id == null) {
            return false;
        }
        var id = String(data.order_id);
        var type = data.type || "driver_no_response";
        var isNew = !attention[id];

        if (!isNew && attention[id].type !== type) {
            // Тип проблемы по заказу сменился (например, водитель после
            // сигнала о молчании всё же отклонил заказ) - карточку
            // пересоздаём, чтобы подпись и тип были актуальны.
            removeAttentionCard(id);
            isNew = true;
        }
        var card;

        if (isNew) {
            card = document.createElement("div");
            card.className = "admin-alert-card admin-alert-card--danger";
            card.setAttribute("data-order-id", id);

            var title = data.title || defaultCardTitle(type);
            var text = data.text || "";
            if (!text) {
                text = "Заказ №" + id + " ждёт назначения нового водителя.";
            }

            var html = '<div class="admin-alert-card__head">'
                + '<span class="admin-alert-card__title">' + escapeHtml(title) + "</span>"
                + '<span class="admin-alert-card__wait"></span>'
                + "</div>"
                + '<div class="admin-alert-card__text">' + escapeHtml(text) + "</div>";
            if (data.from_address || data.to_address) {
                html += '<div class="admin-alert-card__route">'
                    + escapeHtml(data.from_address || "…")
                    + ' <span class="admin-alert-card__arrow">&rarr;</span> '
                    + escapeHtml(data.to_address || "…")
                    + "</div>";
            }
            var meta = "";
            if (data.driver_name) {
                meta += '<span class="admin-alert-card__driver">Водитель: '
                    + escapeHtml(data.driver_name) + "</span>";
            }
            if (data.url) {
                meta += '<a class="admin-alert-card__link" href="'
                    + escapeHtml(data.url) + '">Открыть заказ</a>';
            }
            if (meta) {
                html += '<div class="admin-alert-card__meta">' + meta + "</div>";
            }
            card.innerHTML = html;
            els.list.appendChild(card);
            attention[id] = {
                el: card,
                waitEl: card.querySelector(".admin-alert-card__wait"),
                type: type,
                baseWaiting: 0,
                setAt: Date.now()
            };
        } else {
            card = attention[id].el;
        }

        if (typeof data.waiting_seconds === "number") {
            attention[id].baseWaiting = data.waiting_seconds;
            attention[id].setAt = Date.now();
        }
        tickCard(id);
        syncPanelVisibility();

        if (isNew && !silent) {
            window.adminToast(
                data.title || defaultCardTitle(type),
                data.text || ("Заказ №" + id + " - нужен новый водитель."),
                "danger",
                data.url
            );
            signalNewAlert({
                title: data.title || defaultCardTitle(type),
                text: data.text || ("Заказ №" + id + " ждёт назначения нового водителя."),
                order_id: id,
                url: data.url,
                from_address: data.from_address,
                to_address: data.to_address
            });
        }
        return isNew;
    }

    function removeAttentionCard(orderId) {
        var id = String(orderId);
        var rec = attention[id];
        if (!rec) {
            return;
        }
        if (rec.el.parentNode) {
            rec.el.parentNode.removeChild(rec.el);
        }
        delete attention[id];
        syncPanelVisibility();
    }

    function tickCard(id) {
        var rec = attention[id];
        if (!rec || !rec.waitEl) {
            return;
        }
        var sec = rec.baseWaiting + Math.floor((Date.now() - rec.setAt) / 1000);
        rec.waitEl.textContent = "ждёт " + formatWaiting(sec);
    }

    function tickAllCards() {
        Object.keys(attention).forEach(tickCard);
    }

    function syncOverdue() {
        if (!window.fetch) {
            return;
        }
        fetch(OVERDUE_URL, {
            credentials: "same-origin",
            headers: { "Accept": "application/json" }
        }).then(function (resp) {
            return resp.ok ? resp.json() : null;
        }).then(function (list) {
            if (!Array.isArray(list)) {
                return;
            }
            var countBefore = Object.keys(attention).length;
            var newOnes = [];
            var seen = {};
            list.forEach(function (item) {
                if (item && item.order_id != null) {
                    seen[String(item.order_id)] = true;
                    item.type = item.reason === "declined"
                        ? "driver_declined"
                        : "driver_no_response";
                    if (upsertAttentionCard(item, true)) {
                        newOnes.push(item);
                    }
                }
            });
            // Исчезновение из списка = проблема решена (водитель
            // переназначен, заказ принят или закрыт): карточку убираем.
            // Эндпоинт возвращает оба типа проблем - и молчание, и
            // отказ, - поэтому снимаем любую карточку вне свежего списка.
            Object.keys(attention).forEach(function (id) {
                if (!seen[id]) {
                    removeAttentionCard(id);
                }
            });
            // Синхронизация карточки рисует молча (silent), но если
            // число карточек ВЫРОСЛО против прошлого снимка - это
            // новые проблемные заказы, о них сигналим как о живых
            // событиях. Сюда же попадает первая загрузка страницы:
            // карточки уже ждут - диспетчер получает сигнал сразу.
            if (newOnes.length && Object.keys(attention).length > countBefore) {
                newOnes.forEach(function (item) {
                    signalNewAlert({
                        title: item.title || defaultCardTitle(item.type),
                        text: item.text || ("Заказ №" + item.order_id + " ждёт назначения нового водителя."),
                        order_id: item.order_id,
                        url: item.url,
                        from_address: item.from_address,
                        to_address: item.to_address
                    });
                });
            }
        }).catch(function () {
            /* эндпоинта может ещё не быть (бэк придёт отдельным PR) - молчим */
        });
    }

    /* ---------- обработка сигналов admin.alert ---------- */

    function handleAlert(data) {
        if (!data || !data.type) {
            return;
        }
        if (data.type === "driver_no_response" || data.type === "driver_declined") {
            upsertAttentionCard(data, false);
        } else if (data.type === "new_order" || data.type === "new_message") {
            // Диалог с этим пользователем уже открыт в мессенджере:
            // сообщение подгружается прямо в ленту диалога, и тост со
            // звуком о нём же - лишний шум (замечание Кати 22.11.2026).
            // Открытый диалог помечает messenger/index.blade.php в
            // window.__messengerOpenUserId.
            if (data.type === "new_message"
                && window.__messengerOpenUserId != null
                && Number(data.user_id) === Number(window.__messengerOpenUserId)) {
                return;
            }
            var title = data.title || (data.type === "new_order" ? "Новый заказ" : "Новое сообщение");
            window.adminToast(title, data.text || "", "info", data.url);
            signalInfo(title, data.text || "", "admin-info-" + data.type, data.url);
        }
    }

    /* ---------- бейдж непрочитанных сообщений в сайдбаре ---------- */

    function renderMessageBadge(count) {
        var badge = document.getElementById("admin-messages-unread");
        if (!badge) {
            return;
        }
        // Показ и скрытие - инлайн-стилем, а не классом d-none:
        // в admin.css правило .badge { display: inline-flex } стоит
        // после .d-none при равной специфичности и без !important,
        // поэтому класс визуально не срабатывал и пустая плашка
        // висела всегда. Класс оставляем для порядка, механизм - стиль.
        if (count > 0) {
            badge.textContent = count > 99 ? "99+" : String(count);
            badge.style.display = "inline-flex";
            badge.classList.remove("d-none");
        } else {
            badge.textContent = "";
            badge.style.display = "none";
            badge.classList.add("d-none");
        }
    }

    function refreshMessageBadge() {
        if (!window.fetch || !document.getElementById("admin-messages-unread")) {
            return;
        }
        fetch(UNREAD_URL, {
            credentials: "same-origin",
            headers: { "Accept": "application/json" }
        }).then(function (resp) {
            return resp.ok ? resp.json() : null;
        }).then(function (data) {
            if (data && typeof data.count === "number") {
                renderMessageBadge(data.count);
            }
        }).catch(function () {
            /* эндпоинт мог ещё не задеплоиться - молчим */
        });
    }

    /* ---------- сигнал «заказы изменились» для страницы заказов ---------- */

    var ordersChangedHandlers = [];

    // Страница списка заказов (admin-orders-live.js) подписывается
    // сюда, чтобы перечитывать свой фрагмент без перезагрузки.
    // Событие orders.changed приходит в тот же приватный канал
    // admin.alerts (AdminOrdersChanged из OrderObserver).
    window.onOrdersChanged = function (fn) {
        if (typeof fn === "function") {
            ordersChangedHandlers.push(fn);
        }
    };

    function handleOrdersChanged(data) {
        ordersChangedHandlers.forEach(function (fn) {
            try {
                fn(data || {});
            } catch (e) {
                /* подписчик не должен ломать остальные сигналы */
            }
        });
    }

    /* ---------- сигнал «статус водителя изменился» ---------- */

    var driverStatusHandlers = [];

    // Страницы водителей, заказов и карты (admin-orders-live.js,
    // вьюхи водителей и карты) подписываются сюда, чтобы обновить
    // статус водителя без перезагрузки. Событие driver.status
    // приходит в тот же приватный канал admin.alerts
    // (DriverWorkStatusChanged из пинга геопозиции, пакет №162).
    window.onDriverStatusChanged = function (fn) {
        if (typeof fn === "function") {
            driverStatusHandlers.push(fn);
        }
    };

    function handleDriverStatus(data) {
        driverStatusHandlers.forEach(function (fn) {
            try {
                fn(data || {});
            } catch (e) {
                /* подписчик не должен ломать остальные сигналы */
            }
        });
    }

    /* ---------- Pusher: единственная глобальная точка мастера ---------- */

    // Ключ/кластер - из meta-тегов мастера (конфиг вещания сервера):
    // захардкоженная пара в константах выше принадлежит донорскому
    // приложению Pusher и с сервером не совпадает.
    function pusherKey() {
        var meta = document.querySelector('meta[name="pusher-key"]');
        var value = meta && meta.getAttribute("content");
        return value ? value : PUSHER_KEY;
    }

    function pusherCluster() {
        var meta = document.querySelector('meta[name="pusher-cluster"]');
        var value = meta && meta.getAttribute("content");
        return value ? value : PUSHER_CLUSTER;
    }

    function initPusher() {
        if (typeof Pusher === "undefined") {
            return;
        }
        Pusher.logToConsole = false;

        var pusher = new Pusher(pusherKey(), {
            cluster: pusherCluster(),
            authEndpoint: ALERTS_AUTH_ENDPOINT,
            auth: {
                headers: {
                    "X-CSRF-TOKEN": csrfToken()
                }
            }
        });
        window.adminPusher = pusher;

        // Канал сообщений тех. поддержки (перенесено из инлайн-блока мастера).
        var channel = pusher.subscribe("admin");

        channel.bind("sendMailAdmin", function (data) {
            if (window.location.pathname.startsWith("/admin/messenger")) {
                var outUserId = data.message.user_id;

                var $item = $('.message_item_user[data-id="' + outUserId + '"]');

                $item.prependTo(".message_list");

                if ($(".message_area").hasClass("d-none")) {
                    $item.find(".unread_message").removeClass("d-none");
                } else {
                    if ($(".message_area").find(".message_area_footer .send_button_area button").data("id") == outUserId) {
                        var elementAdminMessage = `<div class="w-100 mb-2">
                                                <div class="user_message">
                                                    ${escapeHtml(data.message.message)}
                                                    <div style="text-align: right; font-size: 10px;">${getCurrentTime()}</div>
                                                </div>
                                            </div>`;
                        $("#messageAreaMain").append(elementAdminMessage);
                        scrollToBottom();
                        $.ajax({
                            type: "POST",
                            url: "/admin/readMessageUser",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken()
                            },
                            data: { "userId": outUserId },
                            success: function (data) {

                            },
                            error: function (error) {
                                console.log(error);
                            }
                        });
                    } else {
                        $item.find(".unread_message").removeClass("d-none");
                    }
                }
            } else {
                var senderName = (data.user && data.user.name) ? data.user.name : "";
                var rawText = (data.message && data.message.message) ? String(data.message.message) : "";
                var preview = rawText.length > 120 ? rawText.slice(0, 120) + "…" : rawText;
                var toastText = senderName ? senderName + ": " + preview : preview;
                var chatUrl = "/admin/messenger" + (data.message && data.message.user_id ? "?user=" + data.message.user_id : "");
                window.adminToast("Новое сообщение", toastText, "info", chatUrl);
                signalInfo("Новое сообщение", toastText, "admin-mail-" + (data.message && data.message.id ? data.message.id : "new"), chatUrl);
            }
            refreshMessageBadge();
        });

        // Приватный канал сигналов диспетчера.
        var alertsChannel = pusher.subscribe("private-admin.alerts");
        alertsChannel.bind("admin.alert", handleAlert);
        alertsChannel.bind("orders.changed", handleOrdersChanged);
        alertsChannel.bind("driver.status", handleDriverStatus);
    }

    /* ---------- старт ---------- */

    function init() {
        captureTitleAndFavicon();
        syncNotifyButton();
        initPusher();
        syncOverdue();
        refreshMessageBadge();
        setInterval(syncOverdue, SYNC_INTERVAL);
        setInterval(refreshMessageBadge, SYNC_INTERVAL);
        setInterval(tickAllCards, TICK_INTERVAL);
        if (window.location.pathname.startsWith("/admin/messenger") && window.jQuery) {
            // Открытие диалога на странице сообщений помечает его
            // прочитанным (getMessagesUser на стороне страницы) -
            // пересчитать бейдж следом за кликом.
            window.jQuery(document).on("click", ".message_item_user", function () {
                setTimeout(refreshMessageBadge, 800);
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
