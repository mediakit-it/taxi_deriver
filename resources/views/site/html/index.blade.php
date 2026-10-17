<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="СитиТакси - заказ такси в вашем городе по короткому номеру 7777. Поездки по городу и за город, трансферы, корпоративные поездки. Заказ по телефону и в приложении.">
    <title>СитиТакси - заказ такси в вашем городе</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assetsSite2/img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assetsSite2/css/landing.css') }}">
</head>

<body>
    <header class="site-header" id="top">
        <div class="container header-inner">
            <a class="logo" href="/" aria-label="СитиТакси - на главную">
                <span class="logo-pin">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <span class="logo-text">Сити<span class="logo-accent">Такси</span></span>
            </a>

            <nav class="main-nav" id="main-nav" aria-label="Основная навигация">
                <a class="nav-link" href="/">Главная</a>
                <a class="nav-link" href="#about-link">О нас</a>
                <a class="nav-link" href="#services">Услуги</a>
                <a class="nav-link" href="#fleet">Автопарк</a>
                <a class="nav-link" href="#tariffs">Тарифы</a>
                <a class="nav-link" href="#app">Приложение</a>
            </nav>

            <div class="header-actions">
                <a class="btn btn-brand btn-call" href="tel:7777">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    7777
                </a>
                <button class="nav-toggle" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="main-nav">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <main>
        {{-- Герой --}}
        <section class="hero" style="background-image: url('{{ asset('assetsSite2/img/bg-1.webp') }}')">
            <div class="container hero-inner">
                <div class="hero-content">
                    <p class="eyebrow eyebrow-light">Безопасные поездки - лучшие маршруты</p>
                    <h1 class="hero-title">Ваше надёжное <span class="text-accent">такси</span></h1>
                    <p class="hero-lead">Поездки по городу и за его пределами - быстро, комфортно и по честной цене. Закажите по короткому номеру 7777 или в приложении: машина подаётся за 3-5 минут.</p>
                    <div class="hero-actions">
                        <a class="btn btn-brand btn-lg" href="tel:7777">Заказать такси</a>
                        <a class="btn btn-ghost btn-lg" href="#app">Скачать приложение</a>
                    </div>
                    <ul class="trust-chips">
                        <li class="trust-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Работаем 24/7
                        </li>
                        <li class="trust-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Опытные водители
                        </li>
                        <li class="trust-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Фиксированная цена
                        </li>
                    </ul>
                </div>
                <img class="hero-car" src="{{ asset('assetsSite2/img/taxi.png') }}" alt="Жёлтый автомобиль такси">
            </div>
        </section>

        {{-- Панель быстрого заказа --}}
        <div class="container booking-wrap">
            <form class="booking-bar" action="#" method="get">
                <label class="booking-field">
                    <span class="booking-label">Откуда</span>
                    <input type="text" name="from" placeholder="Введите адрес подачи">
                </label>
                <label class="booking-field">
                    <span class="booking-label">Куда</span>
                    <input type="text" name="to" placeholder="Введите адрес назначения">
                </label>
                <label class="booking-field">
                    <span class="booking-label">Дата и время</span>
                    <input type="text" name="when" placeholder="Сегодня, сейчас">
                </label>
                <a class="btn btn-brand booking-btn" href="tel:7777">Заказать</a>
            </form>
            <p class="booking-caption">Или просто позвоните: <a href="tel:7777">7777</a> - диспетчер на связи круглосуточно</p>
        </div>

        {{-- Услуги --}}
        <section class="section" id="services">
            <div class="container">
                <div class="section-head">
                    <p class="eyebrow">Наши услуги</p>
                    <h2 class="section-title">Поездка под любую задачу</h2>
                    <p class="section-lead">От короткой поездки по городу до трансфера в аэропорт - выберите формат, а мы позаботимся об остальном.</p>
                </div>

                <div class="services-grid">
                    <article class="service-card">
                        <div class="service-media">
                            <img src="{{ asset('assetsSite2/img/service-1.webp') }}" alt="Такси едет по городской улице" loading="lazy">
                        </div>
                        <div class="service-body">
                            <h3 class="service-title">Поездки по городу</h3>
                            <p class="service-text">Быстрые поездки по городу в любое время дня и ночи - подача машины за 3-5 минут.</p>
                            <a class="card-link" href="#">Подробнее
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </article>

                    <article class="service-card">
                        <div class="service-media">
                            <img src="{{ asset('assetsSite2/img/service-2.webp') }}" alt="Автомобиль такси на загородной трассе" loading="lazy">
                        </div>
                        <div class="service-body">
                            <h3 class="service-title">За город</h3>
                            <p class="service-text">Поездки в соседние города и деревни по прозрачному тарифу за километр - без сюрпризов в пути.</p>
                            <a class="card-link" href="#">Подробнее
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </article>

                    <article class="service-card">
                        <div class="service-media">
                            <img src="{{ asset('assetsSite2/img/service-3.webp') }}" alt="Встреча пассажира в аэропорту" loading="lazy">
                        </div>
                        <div class="service-body">
                            <h3 class="service-title">Трансфер в аэропорт</h3>
                            <p class="service-text">Встретим с табличкой, отследим рейс и поможем с багажом. Цена фиксируется при заказе.</p>
                            <a class="card-link" href="#">Подробнее
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </article>

                    <article class="service-card">
                        <div class="service-media">
                            <img src="{{ asset('assetsSite2/img/service-4.jpg') }}" alt="Деловая поездка на автомобиле комфорт-класса" loading="lazy">
                        </div>
                        <div class="service-body">
                            <h3 class="service-title">Корпоративные поездки</h3>
                            <p class="service-text">Поездки для сотрудников и гостей компании, отчётные документы и оплата по безналичному расчёту.</p>
                            <a class="card-link" href="#">Подробнее
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- О нас / почему мы --}}
        <section class="section section-alt" id="about-link">
            <div class="container about-grid">
                <div class="about-media">
                    <img class="about-photo-main" src="{{ asset('assetsSite2/img/cta-men.webp') }}" alt="Водитель такси за рулём автомобиля" loading="lazy">
                    <img class="about-photo-small" src="{{ asset('assetsSite2/img/about-2.webp') }}" alt="Салон автомобиля такси" loading="lazy">
                    <div class="about-badge">
                        <span class="about-badge-num">5+</span>
                        <span class="about-badge-text">лет опыта работы</span>
                    </div>
                </div>

                <div class="about-content">
                    <p class="eyebrow">Почему мы</p>
                    <h2 class="section-title">Больше, чем просто такси</h2>
                    <p class="about-text">Мы - локальная служба такси, которая знает город и ценит ваше время. Собственное приложение, круглосуточная диспетчерская и водители, для которых безопасность и вежливость - не пустые слова. Быстро. Безопасно. По честной цене.</p>

                    <div class="features-grid">
                        <div class="feature-item">
                            <span class="feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                            </span>
                            <div>
                                <h3 class="feature-title">Онлайн-отслеживание</h3>
                                <p class="feature-text">Видите движение машины на карте в реальном времени.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.7 8.95a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.72a1.17 1.17 0 0 1 1.6 0C14.5 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                            </span>
                            <div>
                                <h3 class="feature-title">Проверенные водители</h3>
                                <p class="feature-text">Опыт от 5 лет, проверка документов и состояния автомобиля.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15z"/></svg>
                            </span>
                            <div>
                                <h3 class="feature-title">Чистые и ухоженные машины</h3>
                                <p class="feature-text">Регулярная мойка и технический осмотр каждого автомобиля.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            </span>
                            <div>
                                <h3 class="feature-title">Поддержка 24/7</h3>
                                <p class="feature-text">Диспетчер на связи круглосуточно - по телефону и в приложении.</p>
                            </div>
                        </div>
                    </div>

                    <a class="btn btn-dark btn-lg" href="tel:7777">Позвонить: 7777</a>
                </div>
            </div>
        </section>

        {{-- Автопарк --}}
        <section class="section" id="fleet">
            <div class="container">
                <div class="section-head">
                    <p class="eyebrow">Автопарк</p>
                    <h2 class="section-title">Современные и ухоженные автомобили</h2>
                    <p class="section-lead">Наш таксопарк - современные автомобили, которые проходят осмотр перед каждой сменой. Выберите класс под свою поездку.</p>
                </div>

                <div class="fleet-grid">
                    <article class="car-card">
                        <div class="car-media">
                            <img src="{{ asset('assetsSite2/img/car-1.webp') }}" alt="Автомобиль такси эконом-класса" loading="lazy">
                            <span class="car-class">Эконом</span>
                        </div>
                        <div class="car-body">
                            <h3 class="car-title">Для ежедневных поездок</h3>
                            <ul class="spec-chips">
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    4 пассажира
                                </li>
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20"/><path d="m4.9 4.9 14.2 14.2"/><path d="M19.1 4.9 4.9 19.1"/></svg>
                                    Кондиционер
                                </li>
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                    Места для багажа
                                </li>
                            </ul>
                            <div class="car-foot">
                                <span class="car-price">от 7 руб.</span>
                                <a class="card-link" href="tel:7777">Заказать
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="car-card">
                        <div class="car-media">
                            <img src="{{ asset('assetsSite2/img/car-2.webp') }}" alt="Автомобиль такси комфорт-класса" loading="lazy">
                            <span class="car-class">Комфорт</span>
                        </div>
                        <div class="car-body">
                            <h3 class="car-title">Простор и тишина в салоне</h3>
                            <ul class="spec-chips">
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    4 пассажира
                                </li>
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20"/><path d="m4.9 4.9 14.2 14.2"/><path d="M19.1 4.9 4.9 19.1"/></svg>
                                    Кондиционер
                                </li>
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                    Места для багажа
                                </li>
                            </ul>
                            <div class="car-foot">
                                <span class="car-price">от 10 руб.</span>
                                <a class="card-link" href="tel:7777">Заказать
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="car-card">
                        <div class="car-media">
                            <img src="{{ asset('assetsSite2/img/car-3.png') }}" alt="Автомобиль такси бизнес-класса" loading="lazy">
                            <span class="car-class">Бизнес</span>
                        </div>
                        <div class="car-body">
                            <h3 class="car-title">Для деловых поездок и встреч</h3>
                            <ul class="spec-chips">
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    4 пассажира
                                </li>
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20"/><path d="m4.9 4.9 14.2 14.2"/><path d="M19.1 4.9 4.9 19.1"/></svg>
                                    Кондиционер
                                </li>
                                <li class="spec-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                    Места для багажа
                                </li>
                            </ul>
                            <div class="car-foot">
                                <span class="car-price">от 15 руб.</span>
                                <a class="card-link" href="tel:7777">Заказать
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- Цифры --}}
        <section class="stats-band">
            <div class="container stats-grid">
                <div class="stat">
                    <span class="stat-num">10K+</span>
                    <span class="stat-label">довольных клиентов</span>
                </div>
                <div class="stat">
                    <span class="stat-num">5K+</span>
                    <span class="stat-label">выполненных поездок</span>
                </div>
                <div class="stat">
                    <span class="stat-num">4.9</span>
                    <span class="stat-label">средняя оценка сервиса</span>
                </div>
                <div class="stat">
                    <span class="stat-num">24/7</span>
                    <span class="stat-label">всегда на связи</span>
                </div>
            </div>
        </section>

        {{-- Как заказать --}}
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <p class="eyebrow">Как заказать</p>
                    <h2 class="section-title">Четыре простых шага</h2>
                    <p class="section-lead">От адреса до поездки - меньше минуты. Никаких очередей и ожидания на линии.</p>
                </div>

                <div class="steps-grid">
                    <div class="step">
                        <span class="step-num">1</span>
                        <h3 class="step-title">Укажите адрес</h3>
                        <p class="step-text">Откуда и куда - в приложении или по телефону 7777.</p>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <h3 class="step-title">Выберите поездку</h3>
                        <p class="step-text">Класс автомобиля и пожелания: детское кресло, багаж, встреча с табличкой.</p>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <h3 class="step-title">Подтвердите заказ</h3>
                        <p class="step-text">Цена известна заранее и не меняется в пути.</p>
                    </div>
                    <div class="step">
                        <span class="step-num">4</span>
                        <h3 class="step-title">В путь</h3>
                        <p class="step-text">Водитель уже едет - отслеживайте машину на карте.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Приложение --}}
        <section class="section section-alt" id="app">
            <div class="container app-grid">
                <div class="app-content">
                    <p class="eyebrow">Мобильное приложение</p>
                    <h2 class="section-title">Заказ, отслеживание и поездка - всё в одном приложении</h2>
                    <p class="about-text">СитиТакси теперь в вашем смартфоне. Заказывайте поездку в пару касаний и следите за машиной на карте - без звонков диспетчеру.</p>

                    <ul class="app-features">
                        <li class="app-feature">
                            <span class="check-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            Онлайн-отслеживание машины на карте
                        </li>
                        <li class="app-feature">
                            <span class="check-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            Заказ без звонков - в пару касаний
                        </li>
                        <li class="app-feature">
                            <span class="check-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            Пожелания к поездке: детское кресло, багаж, встреча с табличкой
                        </li>
                        <li class="app-feature">
                            <span class="check-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            История поездок и чеки в одном месте
                        </li>
                    </ul>

                    <div class="store-badges">
                        <a class="store-badge" href="#" aria-label="Скачать в Google Play">
                            <img src="{{ asset('assetsSite2/img/google-play.webp') }}" alt="Доступно в Google Play" loading="lazy">
                        </a>
                        <a class="store-badge" href="#" aria-label="Скачать в App Store">
                            <img src="{{ asset('assetsSite2/img/app-store.webp') }}" alt="Доступно в App Store" loading="lazy">
                        </a>
                    </div>
                </div>

                <div class="app-media">
                    <div class="app-blob"></div>
                    <img class="app-phone" src="{{ asset('assetsSite2/img/mockup.png') }}" alt="Приложение СитиТакси на экране смартфона" loading="lazy">
                </div>
            </div>
        </section>

        {{-- Тарифы --}}
        <section class="section" id="tariffs">
            <div class="container">
                <div class="section-head">
                    <p class="eyebrow">Тарифы</p>
                    <h2 class="section-title">Прозрачные цены без сюрпризов</h2>
                    <img class="tariffs-car" src="{{ asset('assetsSite2/img/pricing-car.png') }}" alt="Жёлтый автомобиль такси" loading="lazy">
                    <p class="section-lead">Стоимость известна при заказе и не меняется в пути - ни из-за пробок, ни из-за погоды.</p>
                </div>

                <div class="pricing-grid">
                    <article class="pricing-card">
                        <h3 class="pricing-name">По городу</h3>
                        <p class="pricing-price">7 <span class="pricing-unit">руб.</span></p>
                        <p class="pricing-note">поездка по городу</p>
                        <ul class="pricing-list">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Подача за 3-5 минут
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Цена фиксируется при заказе
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Оплата картой или наличными
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Работаем круглосуточно
                            </li>
                        </ul>
                        <a class="btn btn-outline pricing-btn" href="tel:7777">Заказать</a>
                    </article>

                    <article class="pricing-card pricing-card-featured">
                        <span class="pricing-badge">Популярный</span>
                        <h3 class="pricing-name">За городом</h3>
                        <p class="pricing-price">1,50 <span class="pricing-unit">руб/км</span></p>
                        <p class="pricing-note">междугородние поездки</p>
                        <ul class="pricing-list">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Тариф за километр - без скрытых доплат
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Поездки в соседние города и деревни
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Опытные водители на трассе
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Детское кресло по запросу
                            </li>
                        </ul>
                        <a class="btn btn-brand pricing-btn" href="tel:7777">Заказать</a>
                    </article>

                    <article class="pricing-card">
                        <h3 class="pricing-name">Трансфер</h3>
                        <p class="pricing-price">25 <span class="pricing-unit">руб.</span></p>
                        <p class="pricing-note">фиксированная цена</p>
                        <ul class="pricing-list">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Встреча с табличкой
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Отслеживание рейса
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Помощь с багажом
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Цена не зависит от пробок
                            </li>
                        </ul>
                        <a class="btn btn-outline pricing-btn" href="tel:7777">Заказать</a>
                    </article>
                </div>
            </div>
        </section>

        {{-- Отзывы --}}
        <section class="section section-alt">
            <div class="container">
                <div class="section-head">
                    <p class="eyebrow">Отзывы</p>
                    <h2 class="section-title">Что говорят пассажиры</h2>
                </div>

                <div class="reviews-grid">
                    <article class="review-card">
                        <div class="review-stars" aria-label="Оценка 5 из 5">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <p class="review-text">Заказываю почти каждый день на работу. Машина приезжает за пять минут, водители вежливые, в салоне чисто. Лучшее такси в городе.</p>
                        <div class="review-author">
                            <span class="review-avatar">АК</span>
                            <span>
                                <span class="review-name">Анна К.</span>
                                <span class="review-role">постоянный клиент</span>
                            </span>
                        </div>
                    </article>

                    <article class="review-card">
                        <div class="review-stars" aria-label="Оценка 5 из 5">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <p class="review-text">Заказывал трансфер в аэропорт ночью. Водитель приехал заранее, помог с чемоданами, а цена не изменилась ни на копейку.</p>
                        <div class="review-author">
                            <span class="review-avatar">ДВ</span>
                            <span>
                                <span class="review-name">Дмитрий В.</span>
                                <span class="review-role">трансфер в аэропорт</span>
                            </span>
                        </div>
                    </article>

                    <article class="review-card">
                        <div class="review-stars" aria-label="Оценка 5 из 5">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <p class="review-text">Удобное приложение: видно, где едет машина, и не нужно никуда звонить. Детское кресло поставили без лишних вопросов.</p>
                        <div class="review-author">
                            <span class="review-avatar">ОС</span>
                            <span>
                                <span class="review-name">Ольга С.</span>
                                <span class="review-role">поездки с ребёнком</span>
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- Призыв к действию --}}
        <section class="cta-band" style="background-image: url('{{ asset('assetsSite2/img/map.png') }}')">
            <div class="container cta-inner">
                <div class="cta-content">
                    <h2 class="cta-title">Готовы к следующей поездке?</h2>
                    <p class="cta-text">Позвоните по короткому номеру 7777 - или скачайте приложение и закажите поездку в пару касаний. Машина уже ждёт.</p>
                    <a class="btn btn-brand btn-lg" href="tel:7777">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Позвонить: 7777
                    </a>
                </div>
                <img class="cta-car" src="{{ asset('assetsSite2/img/taxi.png') }}" alt="Жёлтый автомобиль такси" loading="lazy">
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a class="logo" href="/" aria-label="СитиТакси - на главную">
                    <span class="logo-pin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span class="logo-text">Сити<span class="logo-accent">Такси</span></span>
                </a>
                <p class="footer-about">Надёжное такси в вашем городе: быстрая подача, честные тарифы и заказ в пару касаний - по телефону или в приложении.</p>
                <div class="socials">
                    <a class="social-link" href="#" aria-label="Telegram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/></svg>
                    </a>
                    <a class="social-link" href="#" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a class="social-link" href="#" aria-label="WhatsApp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    </a>
                </div>
            </div>

            <nav class="footer-col" aria-label="Навигация в подвале">
                <h3 class="footer-title">Навигация</h3>
                <a class="footer-link" href="/">Главная</a>
                <a class="footer-link" href="#about-link">О нас</a>
                <a class="footer-link" href="#services">Услуги</a>
                <a class="footer-link" href="#fleet">Автопарк</a>
                <a class="footer-link" href="#tariffs">Тарифы</a>
                <a class="footer-link" href="#app">Приложение</a>
            </nav>

            <div class="footer-col">
                <h3 class="footer-title">Услуги</h3>
                <a class="footer-link" href="#services">Поездки по городу</a>
                <a class="footer-link" href="#services">За город</a>
                <a class="footer-link" href="#services">Трансфер в аэропорт</a>
                <a class="footer-link" href="#services">Корпоративные поездки</a>
            </div>

            <div class="footer-col">
                <h3 class="footer-title">Контакты</h3>
                <a class="footer-contact" href="tel:7777">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Короткий номер: 7777
                </a>
                <a class="footer-contact" href="mailto:zakaz@example.com">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                    zakaz@example.com
                </a>
                <p class="footer-contact">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Ваш город
                </p>
                <p class="footer-contact">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    Работаем круглосуточно
                </p>
            </div>
        </div>

        <div class="container footer-bottom">
            <p class="footer-copy">© <span id="current-year">2026</span> СитиТакси. Все права защищены.</p>
            <div class="footer-legal">
                <a class="footer-link" href="/user-agreement">Пользовательское соглашение</a>
                <a class="footer-link" href="/politika-konfidencialinosti">Политика конфиденциальности</a>
                <a class="footer-link" href="/manual-action">Инструкция</a>
            </div>
        </div>
    </footer>

    <script>
        // Мобильное меню и текущий год в подвале.
        (function () {
            var toggle = document.querySelector('.nav-toggle');
            var header = document.querySelector('.site-header');

            if (toggle && header) {
                toggle.addEventListener('click', function () {
                    var isOpen = header.classList.toggle('nav-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });

                header.querySelectorAll('.nav-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        header.classList.remove('nav-open');
                        toggle.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            var year = document.getElementById('current-year');
            if (year) {
                year.textContent = new Date().getFullYear();
            }
        })();
    </script>
</body>

</html>
