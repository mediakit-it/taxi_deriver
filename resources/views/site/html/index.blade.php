<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="СитиТакси - заказ такси в вашем городе по короткому номеру 7777. Современные электромобили, быстрая подача, заказ через приложение.">
    <title>СитиТакси - заказ такси в вашем городе</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assetsSite2/img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assetsSite2/css/landing.css') }}">
</head>

<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="logo" href="/">Сити<span class="logo-accent">Такси</span></a>

            <button class="nav-toggle" type="button" aria-label="Открыть меню" aria-expanded="false" data-nav-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="main-nav" data-nav>
                <a href="#about-link">О нас</a>
                <a href="#advantages">Преимущества</a>
                <a href="#tariffs">Тарифы</a>
                <a href="#app">Приложение</a>
            </nav>

            <a class="btn btn-call header-call" href="tel:7777">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                7777
            </a>
        </div>
    </header>

    <main>
        <section class="hero" style="background-image: url('{{ asset('assetsSite2/img/slider-bg.webp') }}')">
            <div class="container hero-inner">
                <p class="hero-kicker">Комфортные поездки с СитиТакси</p>
                <h1 class="hero-title">Закажите такси в вашем городе прямо сейчас</h1>
                <p class="hero-lead">Современные электромобили, опытные водители и подача за несколько минут. Работаем круглосуточно - закажите по короткому номеру или в приложении.</p>
                <div class="hero-actions">
                    <a class="btn btn-call btn-lg" href="tel:7777">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Вызвать: 7777
                    </a>
                    <div class="hero-badges">
                        <span class="hero-badges-label">Скачайте наше приложение</span>
                        <div class="badge-row">
                            <a class="badge-link" href="#"><img src="{{ asset('assetsSite2/img/google-play.webp') }}" alt="Скачать в Google Play"></a>
                            <a class="badge-link" href="#"><img src="{{ asset('assetsSite2/img/app-store.webp') }}" alt="Скачать в App Store"></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="about-link">
            <div class="container">
                <div class="section-head">
                    <p class="section-eyebrow">О нашем такси</p>
                    <h2 class="section-title">СитиТакси - ваш надёжный партнёр на дорогах вашего города</h2>
                </div>
                <div class="about-text">
                    <p>СитиТакси - это удобный, быстрый и безопасный способ передвижения по городу и окрестностям. Мы работаем круглосуточно, чтобы вы могли добраться туда, куда нужно, - в любое время дня и ночи.</p>
                    <p class="about-slogan">Быстро. Безопасно. СитиТакси!</p>
                </div>
                <a class="btn btn-call" href="tel:7777">Закажите такси: 7777</a>
            </div>
        </section>

        <section class="section section-alt" id="advantages">
            <div class="container">
                <div class="section-head section-head-center">
                    <h2 class="section-title">Преимущества поездок с нами</h2>
                    <p class="section-lead">СитиТакси - это ваш надёжный способ передвижения по городу и району. Мы делаем ставку на комфорт, безопасность и заботу об экологии.</p>
                </div>
                <div class="cards">
                    <article class="card">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                        </div>
                        <h3 class="card-title">Современный автопарк</h3>
                        <p class="card-text">Только новые электромобили для вашего комфорта</p>
                    </article>
                    <article class="card">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><path d="M12 15v5.5"/><path d="M9.6 10.2 3.2 7.6"/><path d="M14.4 10.2l6.4-2.6"/></svg>
                        </div>
                        <h3 class="card-title">Опытные водители</h3>
                        <p class="card-text">Наши водители всегда хорошо знают город</p>
                    </article>
                    <article class="card">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <h3 class="card-title">Быстрая подача машины</h3>
                        <p class="card-text">Среднее время ожидания от 3 до 5 минут</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-dark">
            <div class="container">
                <div class="section-head">
                    <p class="section-eyebrow">Electric car</p>
                    <h2 class="section-title">Наш таксопарк - современные электромобили</h2>
                    <p class="section-lead">В нашем парке - только электромобили: тихие, экологичные и недорогие в поездке.</p>
                </div>
                <ul class="chips">
                    <li class="chip">Экологичность</li>
                    <li class="chip">Тишина и комфорт</li>
                    <li class="chip">Бесшумная поездка</li>
                    <li class="chip">Меньше затрат</li>
                </ul>
            </div>
        </section>

        <section class="section" id="tariffs">
            <div class="container">
                <div class="section-head section-head-center">
                    <h2 class="section-title">Тарифы</h2>
                    <p class="section-lead">Прозрачные цены без сюрпризов: стоимость известна сразу.</p>
                </div>
                <div class="tariff-cards">
                    <article class="tariff-card">
                        <h3 class="tariff-name">По городу</h3>
                        <p class="tariff-price">7 <span class="tariff-unit">руб.</span></p>
                        <p class="tariff-note">Стоимость поездки в пределах города</p>
                    </article>
                    <article class="tariff-card">
                        <h3 class="tariff-name">За городом</h3>
                        <p class="tariff-price">1,50 <span class="tariff-unit">руб/км</span></p>
                        <p class="tariff-note">Стоимость за километр пути за городом</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-alt" id="app">
            <div class="container">
                <div class="section-head">
                    <p class="section-eyebrow">Скачайте приложение</p>
                    <h2 class="section-title">СитиТакси - теперь в вашем смартфоне</h2>
                    <p class="section-lead">Скачайте наше мобильное приложение и заказывайте такси в пару касаний. Удобный интерфейс, быстрая подача, история поездок и возможность выбрать электромобиль - всё для вашего комфорта.</p>
                </div>
                <ul class="app-features">
                    <li class="feature">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h3 class="feature-title">Онлайн-отслеживание машины</h3>
                    </li>
                    <li class="feature">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                        </div>
                        <h3 class="feature-title">Заказ без звонков</h3>
                    </li>
                    <li class="feature">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                        </div>
                        <h3 class="feature-title">Указание пожеланий к поездке</h3>
                    </li>
                </ul>
                <div class="badge-row">
                    <a class="badge-link" href="#"><img src="{{ asset('assetsSite2/img/google-play.webp') }}" alt="Скачать в Google Play"></a>
                    <a class="badge-link" href="#"><img src="{{ asset('assetsSite2/img/app-store.webp') }}" alt="Скачать в App Store"></a>
                </div>
            </div>
        </section>

        <section class="cta-strip">
            <div class="container cta-inner">
                <h2 class="cta-title">Нужно такси? Просто наберите 7777</h2>
                <p class="cta-text">Короткий номер легко запомнить. Машина приедет за несколько минут - в любое время дня и ночи.</p>
                <a class="btn btn-call btn-lg" href="tel:7777">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Позвонить: 7777
                </a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a class="logo" href="/">Сити<span class="logo-accent">Такси</span></a>
                <p class="footer-tagline">СитиТакси - всегда удобно и по делу. Быстро. Безопасно. Круглосуточно.</p>
            </div>
            <div class="footer-col">
                <h3 class="footer-heading">Контакты</h3>
                <ul class="footer-list">
                    <li><a href="tel:7777">Короткий номер: 7777</a></li>
                    <li><a href="mailto:zakaz@example.com">zakaz@example.com</a></li>
                    <li>Ваш город</li>
                    <li>Пн-Вс: круглосуточно</li>
                </ul>
            </div>
            <div class="footer-col">
                <h3 class="footer-heading">Информация</h3>
                <ul class="footer-list">
                    <li><a href="/user-agreement">Пользовательское соглашение</a></li>
                    <li><a href="/politika-konfidencialinosti">Политика конфиденциальности</a></li>
                    <li><a href="/manual-action">Удаление аккаунта</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3 class="footer-heading">Заказ такси</h3>
                <a class="btn btn-call" href="tel:7777">Закажите такси: 7777</a>
            </div>
        </div>
        <div class="container copyright">
            <p>© <span id="currentYear">2026</span> СитиТакси. Все права защищены.</p>
        </div>
    </footer>

    <script>
        (function () {
            var toggle = document.querySelector('[data-nav-toggle]');
            var nav = document.querySelector('[data-nav]');
            if (toggle && nav) {
                toggle.addEventListener('click', function () {
                    var open = nav.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            }
            var year = document.getElementById('currentYear');
            if (year) {
                year.textContent = new Date().getFullYear();
            }
        })();
    </script>
</body>

</html>
