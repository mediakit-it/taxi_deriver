@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Добавление метода доставки</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('methodDelivery.index')}}">Методы доставки</a></li>
						<li class="breadcrumb-item active" aria-current="page">Добавление метода доставки</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('methodDelivery.store')}}" method="POST" enctype="multipart/form-data">
				@csrf
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о методе доставки</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Название русское</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_ru')}}" name="name_ru" placeholder="Введите русское название акции">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Название казахское</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_kz')}}" name="name_kz" placeholder="Введите казахское название акции">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Страна в которой доступна доставка:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectCountry" name="country" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
													<option value="Беларусь">Беларусь</option>
													<option value="Россия">Россия</option>
													<option value="Казахстан">Казахстан</option>
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input end-date-checkbox" name="isMoney" id="isMoney" value="0">
												<span class="custom-control-label">Взимать плату за доставку?</span>
											</label>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Цена за доставку</label>
											<input type="number" class="form-control text-dark @error('price') is-invalid @enderror" name="price" value="{{old('price')}}" placeholder="Введите цену за доставку">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('price') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">От какой цены доставка бесплатная</label>
											<input type="number" class="form-control text-dark @error('otPrice') is-invalid @enderror" name="otPrice" value="{{old('otPrice')}}" placeholder="Введите от какой цены доставка бесплатная">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('otPrice') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input end-date-checkbox" name="isAddressUser" id="isAddressUser" value="0">
												<span class="custom-control-label">Использовать аддрес доставки?</span>
											</label>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('methodDelivery.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
										<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Сохранить</button>
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
	<script src="{{asset('assets/plugins/fancyuploder/fancy-uploader.js')}}"></script>

	<!-- <script src="{{asset('assets/plugins/summernote-editor/summernote1.js')}}"></script>
	<script>
		(function (e) {
			'use strict';
			$('#summernote').summernote({
				height:120,
			});
			$('#summernote2').summernote({
				height:120,
			});
		})();
	</script> -->
@endsection
			