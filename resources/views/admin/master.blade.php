<!doctype html>
<html lang="en" dir="ltr">

<head>

	<!-- META DATA -->
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

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

	<!-- JQUERY JS: нужен инлайн-скриптам вьюх и плагинам (DataTables, Select2, datepicker) -->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>

	<!-- BOOTSTRAP JS: дропдаун профиля, модалки и алерты во вьюхах (data-bs-*) -->
	<script src="{{asset('assets/plugins/bootstrap/js/popper.min.js')}}"></script>
	<script src="{{asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>

	<!-- SELECT2 JS: инициализируется инлайн-скриптами форм -->
	<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>

	<!-- Собственный JS админки: сайдбар, фулскрин, активный пункт меню -->
	<script src="{{asset('assets/js/admin.js')}}"></script>

	@yield('scripts')

	<script>

		function getCurrentTime() {
            var now = new Date();

            var hours = now.getHours();
            var minutes = now.getMinutes();

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            var time = hours + ':' + minutes;

            return time;
        }

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('53901edd70c280d59a2f', {
      cluster: 'mt1'
    });

    pusher.connection.bind('connected', function() {
      console.log('Connected to Pusher');
    });

    var channel = pusher.subscribe('admin');

    channel.bind('sendMailAdmin', function(data) {
      	if (window.location.pathname.startsWith('/admin/messenger')) {
			var outUserId = data.message.user_id;

			var $item = $('.message_item_user[data-id="' + outUserId + '"]');

			$item.prependTo('.message_list');

			if ($('.message_area').hasClass('d-none')) {
				$item.find('.unread_message').removeClass('d-none');
			}else{
				if($('.message_area').find('.message_area_footer .send_button_area button').data('id')==outUserId){
					var elementAdminMessage = `<div class="w-100 mb-2">
													<div class="user_message">
														${data.message.message}
														<div style="text-align: right; font-size: 10px;">${getCurrentTime()}</div>
													</div>
												</div>`;
					$('#messageAreaMain').append(elementAdminMessage);
					scrollToBottom();
					$.ajax({
						type: 'POST',
						url: "/admin/readMessageUser",
						headers: {
							'X-CSRF-TOKEN': "{{ csrf_token() }}"
						},
						data: {'userId': outUserId},
						success: function (data) {
						
						},
						error: function (error) {
							console.log(error);
						}
					});
				}else{
					$item.find('.unread_message').removeClass('d-none');
				}
			}
		} else {
			not7("Новое сообщение для тех. поддержки");
		}
    });
  </script>

</body>

</html>
