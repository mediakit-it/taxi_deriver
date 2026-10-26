@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Изменение автомобиля</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('car.index')}}">Машины</a></li>
						<li class="breadcrumb-item active" aria-current="page">Изменение автомобиля</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('car.update',['car'=>$car->id])}}" method="POST">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о машине</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Марка машины</label>
											<input type="text" class="form-control text-dark @error('brand') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$car->brand}}" name="brand" placeholder="Введите марку машины">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('brand') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Модель машины</label>
											<input type="text" class="form-control text-dark @error('model') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$car->model}}" name="model" placeholder="Введите модель машины">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('model') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Регистрационный номер</label>
											<input type="text" class="form-control text-dark @error('number_plate') is-invalid @enderror" name="number_plate" value="{{$car->number_plate}}" placeholder="Введите регистрационный номер машины">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('number_plate') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Водители:</label>
											<select class="form-control form-select text-dark" value="{{old('user_id')}}" name="user_id" id="client-salution1" data-placeholder="Выберите водителя">
											<option value="" selected>-</option>	
												@foreach($users as $user)
													<option value="{{ $user->id }}" {{$car->user_id == $user->id ? 'selected' : ''}}>{{ $user->last_name }} {{ $user->first_name }}</option>
												@endforeach
											</select>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('car.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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
