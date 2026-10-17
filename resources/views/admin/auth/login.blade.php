<!doctype html>
<html lang="en" dir="ltr">
  <head>

		<!-- META DATA -->
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="Noa – Bootstrap 5 Admin & Dashboard Template">
		<meta name="author" content="Spruko Technologies Private Limited">
		<meta name="keywords" content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/favicon.ico" />

		<!-- TITLE -->
		<title>Noa – Bootstrap 5 Admin & Dashboard Template</title>

		<!-- BOOTSTRAP CSS -->
		<link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

		<!-- STYLE CSS -->
		<link href="../assets/css/style.css" rel="stylesheet"/>
		<link href="../assets/css/skin-modes.css" rel="stylesheet" />

		<!--- FONT-ICONS CSS -->
		<link href="../assets/css/icons.css" rel="stylesheet"/>

	</head>

	<body class="ltr login-img">

			<!-- GLOABAL LOADER -->
			<div id="global-loader">
				<img src="../assets/images/loader.svg" class="loader-img" alt="Loader">
			</div>
			<!-- /GLOABAL LOADER -->

			<!-- PAGE -->
			<div class="page">
				<div>
				    <!-- CONTAINER OPEN -->
					<div class="col col-login mx-auto text-center">
						<a href="index.html" class="text-center">
							<img src="{{asset('assets/images/logo/logo1.png')}}" class="header-brand-img" alt="">
						</a>
					</div>
					<div class="container-login100">
						<div class="wrap-login100 p-0">
							<div class="card-body">
								<form class="login100-form validate-form" method="POST" action="{{route('login_auth')}}">
								@csrf	
									<span class="login100-form-title">
										Авторизация
									</span>
									@if($errors->any())
										<div class="alert alert-danger" role="alert">
											Неверный email или пароль
										</div>
									@endif
									<div class="wrap-input100 validate-input" data-bs-validate = "Введите email">
										<input class="input100" type="text" name="email" placeholder="Email">
										<span class="focus-input100"></span>
										<span class="symbol-input100">
											<i class="zmdi zmdi-email" aria-hidden="true"></i>
										</span>
									</div>
									<div class="wrap-input100 validate-input" data-bs-validate = "Введите пароль">
										<input class="input100" type="password" name="password" placeholder="Пароль">
										<span class="focus-input100"></span>
										<span class="symbol-input100">
											<i class="zmdi zmdi-lock" aria-hidden="true"></i>
										</span>
									</div>
									<div style="float:right;">
											<label class="ckbox" for="ckbox-checked"><input name="remember" type="checkbox" id="ckbox-checked"><span>Остаться в сети</span></label>
									</div>
									<div class="container-login100-form-btn">
										<button type="submit" class="login100-form-btn btn-primary">Войти</button>
									</div>
									<!-- <div class="text-center pt-3">
										<p class="text-dark mb-0">Not a member?<a href="register.html" class="text-primary ms-1">Create an Account</a></p>
									</div> -->
								</form>
							</div>
						</div>
					</div>
					<!-- CONTAINER CLOSED -->
				</div>
			</div>
			<!-- End PAGE -->


		<!-- BACKGROUND-IMAGE CLOSED -->

		<!-- JQUERY JS -->
		<script src="../assets/js/jquery.min.js"></script>

		<!-- BOOTSTRAP JS -->
		<script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
		<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

		<!-- Perfect SCROLLBAR JS-->
		<script src="../assets/plugins/p-scroll/perfect-scrollbar.js"></script>

		<!-- STICKY JS -->
		<script src="../assets/js/sticky.js"></script>

		<!-- COLOR THEME JS -->
		<script src="../assets/js/themeColors.js"></script>

		<!-- CUSTOM JS -->
		<script src="../assets/js/custom.js"></script>

	</body>
</html>
