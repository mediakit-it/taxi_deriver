<!doctype html>
<html lang="en" dir="ltr">

<head>

	<!-- META DATA -->
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
<meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">

	<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

	<!-- FAVICON -->
	<link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/brand/favicon.ico')}}"/>

	<!-- TITLE -->
	<title>Админ панель </title>

	<!-- Собственные стили админки (тема Noa больше не подключается) -->
	<link href="{{asset('assets/css/admin.css')}}" rel="stylesheet" />

	<!-- Иконочные шрифты: Font Awesome (fa) и Feather (fe) используются в разметке вьюх -->
	<link href="{{asset('assets/iconfonts/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/iconfonts/feather/feather.css')}}" rel="stylesheet" />

	@yield('style')

</head>

<body class="app">

	<!-- PAGE -->
	<div class="page">
		<div class="page-main">

			<!-- app-Header -->
            @include('admin.elementMain.header')
            <!-- /app-Header -->

            <!--APP-SIDEBAR-->
            @include('admin.elementMain.appSideBar')
			<!--/APP-SIDEBAR-->

			<!--app-content open-->
			<div class="app-content main-content mt-0">
				@yield('content')
			</div>
			<!-- CONTAINER END -->
		</div>

		<!-- FOOTER -->
            @include('admin.elementMain.footer')
		<!-- FOOTER END -->
	</div>

	<!-- BACK-TO-TOP -->
	<a href="#top" id="back-to-top"><i class="fa fa-long-arrow-up"></i></a>

	<!-- Realtime-сигналы диспетчера: панель «Требуют внимания» и тосты (admin-alerts.js) -->
	<div id="admin-alerts-panel" class="admin-alerts-panel" hidden>
		<div class="admin-alerts-panel__title">Требуют внимания <span id="admin-alerts-count" class="admin-alerts-panel__count">0</span></div>
		<div id="admin-alerts-list" class="admin-alerts-panel__list"></div>
	</div>
	<div id="admin-toasts" class="admin-toasts"></div>

	<!-- JQUERY JS: нужен инлайн-скриптам вьюх и плагинам (DataTables, Select2, datepicker) -->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>

	<!-- BOOTSTRAP JS: дропдаун профиля, модалки и алерты во вьюхах (data-bs-*) -->
	<script src="{{asset('assets/plugins/bootstrap/js/popper.min.js')}}"></script>
	<script src="{{asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>

	<!-- SELECT2 JS: инициализируется инлайн-скриптами форм -->
	<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>

	<!-- Собственный JS админки: сайдбар, фулскрин, активный пункт меню -->
	<script src="{{asset('assets/js/admin.js')}}"></script>

	<!-- Realtime-сигналы диспетчера: Pusher, панель внимания, тосты, звук -->
	<script src="{{asset('assets/js/admin-alerts.js')}}"></script>

	@yield('scripts')

</body>

</html>
