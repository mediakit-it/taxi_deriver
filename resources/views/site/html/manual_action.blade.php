<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="ru">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ridek Online Taxi Booking HTML5 Template">
    <meta name="author" content="DynamicLayers">

    <title>ОК Такси - заказ такси в Быхове</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assetsSite2/img/favicon.png')}}">
	
    <link rel="stylesheet" href="{{asset('assetsSite2/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/animate.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/fontawesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/line-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/keyframe-animation.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/jquery.datetimepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/nice-select.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/venobox.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/swiper.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/elements.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/header.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/slider.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/blog.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite2/css/responsive.css')}}">
</head>
<style>
    .manual-action img {
        max-width: 300px;
        height: auto;
    }

    .manual-action > p {
        font-size: 20px;
        color: #000;
    }

    .manual-action ul {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
</style>

<body style="display: flex; flex-direction: column; min-height: 100dvh">
   <div class="site-preloader">
        <div class="car">
            <div class="strike"></div>
            <div class="strike strike2"></div>
            <div class="strike strike3"></div>
            <div class="strike strike4"></div>
            <div class="strike strike5"></div>
            <div class="car-detail spoiler"></div>
            <div class="car-detail back"></div>
            <div class="car-detail center"></div>
            <div class="car-detail center1"></div>
            <div class="car-detail front"></div>
            <div class="car-detail wheel"></div>
            <div class="car-detail wheel wheel2"></div>
        </div>
    </div><!--/.site-preloader-->

    <header class="main-header" id="main">
        <!-- <div class="top-header">
            <div class="container">
                <div class="top-header-wrap">
                    <div class="top-left">
                        <p>Reliable Taxi Service & Transport Solutions!</p>
                    </div>
                    <div class="top-right">
                        <ul class="top-header-nav">
                            <li><a href="faqs.html">помощь</a></li>
                            <li><a href="contact.html">Support</a></li>
                            <li><a href="faqs.html">Вопросы</a></li>
                        </ul>
                        <ul class="header-social-share">
                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div> -->
        <!--/.top-header-->
        <div class="mid-header">
            <div class="container">
                <div class="mid-header-wrap">
                    <div class="site-logo">
                        <a href="/"><img src="{{asset('assetsSite2/img/logo-dark.png')}}" width="75" alt="Logo"></a>
                    </div><!--/.site-logo-->
                    <ul class="header-info">
                        <li>
                            <div class="header-info-icon">
                                <i class="las la-phone-volume"></i>
                            </div>
                            <div class="header-info-text">
                                <h3><span>Закажите такси</span><a href="tel:7668">7668</a></h3>
                            </div>
                        </li>
                        <!-- <li>
                            <div class="header-info-icon">
                                <i class="las la-envelope-open"></i>
                            </div>
                            <div class="header-info-text">
                                <h3><span>Email now</span>Info.ridek@mail.com</h3>
                            </div>
                        </li> -->
                        <li>
                            <div class="header-info-icon">
                                <i class="las la-map-marked-alt"></i>
                            </div>
                            <div class="header-info-text">
                                <h3><span>Беларусь,</span>Быхов</h3>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="nav-menu-wrapper">
            <div class="container">
                <div class="nav-menu-inner">
                    <div class="site-logo">
                        <a href="/"><img src="{{asset('assetsSite2/img/logo-dark.png')}}" width="75" alt="Logo"></a>
                    </div><!--/.site-logo-->
                    <div class="header-menu-wrap">
                        <ul class="nav-menu">
                            <li class="dropdown_menu">
                                <a href="/">Главная</a>
                                <!-- <ul>
                                    <li><a href="index.html">Home Default</a></li>
                                    <li><a href="index-2.html">Home Modern</a></li>
                                </ul> -->
                            </li>
                            <li class="dropdown_menu">
                                <a href="/#about-link">О нас</a>
                                <!-- <ul>
                                    <li><a href="about-us.html">About Us</a></li>
                                    <li><a href="about-company.html">About Company</a></li>
                                    <li><a href="our-services.html">Our Services</a></li>
                                    <li><a href="service-details.html">Services Details</a></li>
                                    <li><a href="book-taxi.html">Book a Ride</a></li>
                                </ul> -->
                            </li>
                            <li class="dropdown_menu">
                                <a href="/#advantages">Преимущества</a>
                                <!-- <ul>
                                    <li><a href="our-taxi.html">Taxi Lists</a></li>
                                    <li><a href="taxi-details.html">Taxi Details</a></li>
                                </ul> -->
                            </li>
                            <li class="dropdown_menu">
                                <a href="/#app">О Приложении</a>
                                <!-- <ul>
                                    <li><a href="/manual-action">Удаление аккаунта</a></li>
                                </ul> -->
                            </li>
                            <li class="dropdown_menu">
                                <a href="#">Информация</a>
                                <ul>
                                    <li><a href="/user-agreement">Пользовательское соглашение</a></li>
                                    <li><a href="/politika-konfidencialinosti">Политика конфиденциальности</a></li>
                                    <li><a href="/manual-action">Удаление аккаунта</a></li>
                                </ul>
                            </li>
                            <!-- <li><a href="contact.html">Contact</a></li> -->
                        </ul>
                    </div>
                    <!-- <div class="menu-right-item">
                        <div class="search-icon dl-search-icon">
                            <i class="las la-search"></i>
                        </div>
                        <div class="sidebox-icon dl-sidebox-icon">
                            <i class="las la-bars"></i>
                        </div>
                        <a href="book-taxi.html" class="menu-btn">Скачать приложение</a>
                    </div>
                    <div class="mobile-menu-icon">
                        <div class="burger-menu">
                            <div class="line-menu line-half first-line"></div>
                            <div class="line-menu"></div>
                            <div class="line-menu line-half last-line"></div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </header>

    <section class="container manual-action" style="flex-grow: 1; margin-top: 50px">
        <h1>Удаление аккаунта пользователя</h1>
        <p>Следуйте инструкциям ниже, чтобы удалить аккаунт пользователя.</p>
        <ul>
            <li>
                <h3>Шаг 1</h3>
                <p>Откройте приложение, пройдите авторизацию и нажмите кнопку <b>«Войти»</b>.</p>
                <img  src="{{asset('assetsSite2/img/remove-account/step-1.jpg')}}" alt="Шаг 1.">
            </li>
            <li>
                <h3>Шаг 2</h3>
                <p>
                    В окне <b>«Ваша история заказов»</b> нажмите на значок с тремя точками в правом верхнем углу.<br>
                    В появившемся меню выберите пункт <b>«Профиль»</b>.
                </p>
                <img  src="{{asset('assetsSite2/img/remove-account/step-2.jpg')}}" alt="Шаг 2.">
                <img  src="{{asset('assetsSite2/img/remove-account/step-3.jpg')}}" alt="Шаг 3.">
            </li>
            <li>
                <h3>Шаг 3</h3>
                <p>
                    В окне <b>«Профиль»</b> выберите пункт <b>«Удалить аккаунт»</b>.<br>
                    В открывшемся модальном окне нажмите кнопку <b>«Удалить»</b>.
                </p>
                <img  src="{{asset('assetsSite2/img/remove-account/step-4.jpg')}}" alt="Шаг 4.">
                <img  src="{{asset('assetsSite2/img/remove-account/step-5.jpg')}}" alt="Шаг 5.">
            </li>
            <li>
                <h3>Шаг 4</h3>
                <p>Ваш аккаунт будет удалён.</p>
            </li>
        </ul>
    </section>

    <footer class="footer-section">
        <div class="footer-top-wrap bg-grey">
            <div class="container">
                <div class="footer-top">
                    <div class="row align-items-end">
                        <div class="col-lg-8">
                            <div class="brand">
                                <a class="footer-logo" href="index.html"><img src="{{asset('assetsSite2/img/logo_light.webp')}}" width="75" alt="logo"></a>
                                <p>Ок Такси — это всегда удобно и по делу.<br/>Быстро. Безопасно. Ок!</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="footer-call">
                                <i class="las la-phone-volume"></i>
                                <p><span>Закажите такси</span><a href="tel:7668">7668</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/.footer-top-wrap-->
        <!-- <div class="footer-mid-wrap">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 sm-padding">
                        <div class="footer-item">
                            <div class="widget-title">
                                <h3>ИНФО</h3>
                            </div>
                            <ul class="footer-contact">
                                <li><span>Вторник - Friday:</span>9.00am To 8.00pm</li>
                                <li><span>Saturday:</span>10.00am To 7.30pm</li>
                                <li><span>Sunday:</span>Close Day!</li>
                            </ul>
                        </div>
                    </div> -->
                    <!-- <div class="col-lg-3 col-md-6 sm-padding">
                        <div class="footer-item footer-list">
                            <div class="widget-title">
                                <h3>Usefull Links</h3>
                            </div>
                            <ul class="footer-links">
                                <li><a href="book-taxi.html">Taxi Booking</a></li>
                                <li><a href="faqs.html">Help Center</a></li>
                                <li><a href="about-us.html">Privacy and Policy</a></li>
                                <li><a href="about-company.html">Terms of Use</a></li>
                                <li><a href="contact.html">Contact Us</a></li>
                            </ul>
                        </div>
                    </div> -->
                    <!-- <div class="col-lg-3 col-md-6 sm-padding">
                        <div class="footer-item">
                            <div class="widget-title">
                                <h3>Контакты</h3>
                            </div>
                            <ul class="footer-contact">
                                <li>Беларусь, Быхов</li>
                                <li>ПН-ВС: круглосуточно</li>
                            </ul>
                        </div>
                    </div> -->
                    <!-- <div class="col-lg-3 col-md-6 sm-padding">
                        <div class="footer-item subscribe-wrap">
                            <div class="widget-title">
                                <h3>Newsletter Signup</h3>
                            </div>
                            <form action="#" class="subscribe-form">
                                <input class="form-control" type="email" name="email" placeholder="Your Email"
                                    required="">
                                <input type="hidden" name="action" value="mailchimpsubscribe">
                                <button class="submit">Subscribe Now</button>
                                <div class="clearfix"></div>
                                <div id="subscribe-result">
                                    <div class="subscription-success"></div>
                                    <div class="subscription-error"></div>
                                </div>
                            </form>
                        </div>
                    </div> -->
                <!-- </div>
            </div>
            <div class="running-taxi">
                <div class="taxi"></div>
                <div class="taxi-2"></div>
                <div class="taxi-3"></div>
            </div>
        </div> -->
        <!--/.footer-mid-wrap-->
        <div class="copyright-wrap">
            <div class="container">
                <p>© <span id="currentYear"></span> ОК Такси. Все права защищены. Разработано в <a style="color: #ccc" href="https://mediakit.by/">Mediakit</a></p>
            </div>
        </div>
        <!--/.copyright-wrap-->
    </footer>
    <!--/.footer-section-->

    <div id="scrollup">
        <button id="scroll-top" class="scroll-to-top">
            <i class="las la-arrow-up"></i>
        </button>
    </div>
    <!--scrollup-->

    <div class="dl-cursor">
        <div class="cursor-icon-holder"><i class="las la-times"></i></div>
    </div>
    <!--/.dl-cursor-->

    <!--jQuery Lib-->
    <script src="{{asset('assetsSite2/js/vendor/jquary-3.6.0.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/jquery.ajaxchimp.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/bootstrap.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/popper.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/swiper.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/jquery.datetimepicker.full.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/jquery.nice-select.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/venobox.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/smooth-scroll.js')}}"></script>
    <script src="{{asset('assetsSite2/js/vendor/wow.min.js')}}"></script>
    <script src="{{asset('assetsSite2/js/book-ride.js')}}"></script>
    <script src="{{asset('assetsSite2/js/main.js')}}"></script>
</body>

</html>