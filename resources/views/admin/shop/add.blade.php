@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Добавление магазина</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('shop.index')}}">Магазины</a></li>
						<li class="breadcrumb-item active" aria-current="page">Добавление магазина</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('shop.store')}}" method="POST" enctype="multipart/form-data">
				@csrf
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о магазине</h4>
							</div>
							<div class="col-sm-12 col-md-12 col-xl-6">
								<div class="form-group">
									<label class="form-label text-muted">Изображение магазина</label>
									<input type="file" class="form-control text-dark @error('image') is-invalid @enderror" name="image" accept="image/*">
									<div class="invalid-feedback">@error('image') {{$message}} @enderror</div>
								</div>
							</div>

							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название магазина русское</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_ru')}}" name="name_ru" placeholder="Введите русское название магазина">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название магазина казахское</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_kz')}}" name="name_kz" placeholder="Введите казахское название магазина">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название город русское</label>
											<input type="text" class="form-control text-dark @error('city_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('city_ru')}}" name="city_ru" placeholder="Введите русское название города">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('city_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Нзвание города казахское</label>
											<input type="text" class="form-control text-dark @error('city_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('city_kz')}}" name="city_kz" placeholder="Введите казахское название города">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('city_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание рус</label>
										<textarea name="short_description_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;"></textarea>								
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание каз</label>
										<textarea name="short_description_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;"></textarea>								
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Адрес на русском</label>
											<input type="text" class="form-control text-dark @error('address_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('address_ru')}}" name="address_ru" placeholder="Введите адресс на русском">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('address_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Адрес на казахском</label>
											<input type="text" class="form-control text-dark @error('address_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('address_kz')}}" name="address_kz" placeholder="Введите адрес на казахском">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('address_kz') {{$message}} @enderror</div>
										</div>
									</div>
									
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Телефон магазина</label>
											<input type="text" class="form-control text-dark @error('phone') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('phone')}}" name="phone" placeholder="Введите номер телефона магазина">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('phone') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Страна в которой находится магазин:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="country" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
													<option value="Беларусь">Беларусь</option>
													<option value="Россия">Россия</option>
													<option value="Казахстан">Казахстан</option>
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Время работы</label>
											<input type="text" class="form-control text-dark @error('oclock') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('oclock')}}" name="oclock" placeholder="Введите время работы">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('oclock') {{$message}} @enderror</div>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('shop.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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

	<!-- картинка при редактировании -->
	<!-- data-default-file="../assets/images/photos/1.jpg" -->

	<script src="{{asset('assets/plugins/summernote-editor/summernote1.js')}}"></script>
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
	</script>
@endsection
			