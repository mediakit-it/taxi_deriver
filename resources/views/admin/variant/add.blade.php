@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Добавление варианта</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('variant.index')}}">Варианты</a></li>
						<li class="breadcrumb-item active" aria-current="page">Добавление варианта</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('variant.store')}}" method="POST" enctype="multipart/form-data">
				@csrf
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация об варианте</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название русское</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_ru')}}" name="name_ru" placeholder="Введите русское название варианта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название казахское</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_kz')}}" name="name_kz" placeholder="Введите казахское название варианта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Порядок</label>
											<input type="number" class="form-control text-dark @error('order') is-invalid @enderror" name="order" value="{{old('order')}}" placeholder="Введите порядок в списке">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('order') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">К какому атрибуту относится:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" value="{{old('attribute_id')}}" name="attribute_id" id="client-salution1" data-placeholder="Choose Category..." tabindex="-1" aria-hidden="true">
												@foreach($attributes as $attribute)
													@if($loop->first)
														<option value="{{$attribute->id}}" selected>{{$attribute->name_ru}}</option>
													@else
														<option value="{{$attribute->id}}">{{$attribute->name_ru}}</option>	
													@endif
												@endforeach					
											</select>
										</div>
									</div>					
									<!-- <div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Добавить картинку варианту(Если атрибут имеет отображение в виде картинок)</label>
										<input type="file" name="file" class="dropify" data-height="200"/>
									</div> -->
								</div>
							</div>
							<div class="row p-5">
								<div class="btn-list text-end">
									<a class="btn btn-outline-danger" href="{{route('variant.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
									<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Сохранить</button>
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
@endsection
			