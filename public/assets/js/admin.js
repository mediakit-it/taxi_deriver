/* ОК ТАКСИ - собственный JS админ-панели (без темы Noa).
   Делает три вещи, которые раньше делал sidemenu.js/custom.js темы:
   1) кнопка-гамбургер и оверлей: класс sidenav-toggled на body
      (на десктопе - узкий сайдбар, на мобильном - выдвижной);
   2) полноэкранная кнопка в шапке и кнопка "наверх";
   3) подсветка активного пункта меню по текущему URL.
   Остальное поведение (дропдауны, модалки, алерты) - Bootstrap JS,
   таблицы и селекты инициализируют сами вьюхи. */
(function () {
    "use strict";

    document.addEventListener("click", function (event) {
        var toggle = event.target.closest('[data-bs-toggle="sidebar"]');
        if (toggle) {
            event.preventDefault();
            document.body.classList.toggle("sidenav-toggled");
            return;
        }

        var fullScreen = event.target.closest(".full-screen-link");
        if (fullScreen) {
            event.preventDefault();
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            }
        }
    });

    // Активный пункт меню: точное совпадение пути
    var currentPath = window.location.pathname.replace(/\/+$/, "") || "/";
    document.querySelectorAll(".side-menu__item").forEach(function (item) {
        var href = item.getAttribute("href");
        if (!href) {
            return;
        }
        var itemPath;
        try {
            itemPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, "") || "/";
        } catch (e) {
            return;
        }
        if (itemPath === currentPath) {
            item.classList.add("active");
        }
    });

    // Кнопка "наверх"
    var backToTop = document.getElementById("back-to-top");
    if (backToTop) {
        window.addEventListener("scroll", function () {
            backToTop.classList.toggle("show", window.scrollY > 200);
        }, { passive: true });

        backToTop.addEventListener("click", function (event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }
})();
