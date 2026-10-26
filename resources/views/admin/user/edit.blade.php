@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Изменение пользователя</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('user.index')}}">Пользователи</a></li>
						<li class="breadcrumb-item active" aria-current="page">Изменение пользователя</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('user.update',['user'=>$user->id])}}" method="POST">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о пользователе</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Фамилия</label>
											<input type="text" class="form-control text-dark @error('last_name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$user->last_name}}" name="last_name" placeholder="Введите фамилию пользователя">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('last_name') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Имя</label>
											<input type="text" class="form-control text-dark @error('first_name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$user->first_name}}" name="first_name" placeholder="Введите имя пользователя">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('first_name') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Телефон</label>
											<input type="text" class="form-control text-dark @error('phone') is-invalid @enderror" name="phone" value="{{$user->phone}}" placeholder="Введите номер телефона пользователя">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('phone') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Email:</label>
											<input type="email" class="form-control text-dark @error('email') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$user->email}}" name="email" placeholder="Введите почту пользователя">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('email') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Password:</label>
											<input type="text" class="form-control text-dark @error('password') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="" name="password" placeholder="Введите пароль" readonly>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('password') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-2">
										<div class="form-group">
											<label class="form-label text-muted">Роль:</label>
											<select class="form-control form-select text-dark" value="{{old('role')}}" name="role" id="client-salution1" data-placeholder="Choose Category...">
												@foreach (\App\Models\User::assignableRoles() as $roleValue => $roleLabel)
													<option value="{{ $roleValue }}" @selected(old('role', $user->role) == $roleValue)>{{ $roleLabel }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Дата верификации:</label>
											<div id="date1-datepicker" class="input-group date" data-date-format="dd-mm-yyyy">
												<span class="input-group-addon input-group-text bg-primary-transparent"><i class="fe fe-calendar text-primary-dark"></i></span>
												<input data-provide="datepicker" data-date-end-date="0d" class="form-control task-start-date" type="text" placeholder="Выберите дату верификации"/>
											</div>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('user.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
										<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Изменить</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--ROW CLOSED-->
			</form>
		</div>
	</div>
@endsection

@section('scripts')
	<!--Internal Fileuploads js-->
	<script src="{{asset('assets/plugins/fileuploads/js/fileupload.js')}}"></script>
	<script src="{{asset('assets/plugins/fileuploads/js/file-upload.js')}}"></script>

	<!--Internal Fancy uploader js-->
	<script src="{{asset('assets/plugins/fancyuploder/jquery.ui.widget.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/jquery.fileupload.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/jquery.iframe-transport.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/jquery.fancy-fileupload.js')}}"></script>
	<!-- <script src="{{asset('assets/plugins/fancyuploder/fancy-uploader.js')}}"></script> -->

	<!-- bootstrap-datepicker js -->
	<script src="{{asset('assets/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>
@endsection
