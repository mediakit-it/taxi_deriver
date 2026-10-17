@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Изменение страницы</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('redactor.index')}}">Страницы</a></li>
						<li class="breadcrumb-item active" aria-current="page">Изменение страницы</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('redactor.update',['redactor'=>$pageconstruct->id])}}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о странице</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название рус</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$pageconstruct->name_ru}}" name="name_ru" placeholder="Введите русское название страницы">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название каз</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$pageconstruct->name_kz}}" name="name_kz" placeholder="Введите казахское название страницы">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Slug</label>
											<textarea class="form-control text-dark @error('slug') is-invalid @enderror" id="keywords" aria-describedby="validationServer03Feedback" name="slug" placeholder="slug" rows="3">{{$pageconstruct->slug}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('slug') {{$message}} @enderror</div>
										</div>
									</div>
									<!-- Редактор текста начало  -->
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Содержание на русском</label>	
											<textarea id="content_ru" name="content_ru">{{$pageconstruct->content_ru}}</textarea>					
										</div>
										<div class="form-group">
											<label class="form-label text-muted">Содержание на казахском</label>	
											<textarea id="content_kz" name="content_kz">{{$pageconstruct->content_kz}}</textarea>					
										</div>
									</div>
									<!-- Редактор текста конец  -->
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Meta-title (русский)</label>
											<input type="text" class="form-control text-dark @error('meta_title_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$pageconstruct->meta_title_ru}}" name="meta_title_ru" placeholder="Введите meta-title (русский)">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_title_ru') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Meta-title (казахский)</label>
											<input type="text" class="form-control text-dark @error('meta_title_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$pageconstruct->meta_title_kz}}" name="meta_title_kz" placeholder="Введите meta-title (казахский)">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_title_kz') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Мета описание (русский)</label>
											<textarea type="text" class="form-control text-dark @error('meta_description_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" name="meta_description_ru" placeholder="Мета описание">{{$pageconstruct->meta_description_ru}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_description_ru') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Мета описание (казахский)</label>
											<textarea type="text" class="form-control text-dark @error('meta_description_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" name="meta_description_kz" placeholder="Мета описание">{{$pageconstruct->meta_description_kz}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_description_kz') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">ключевые слова (русский)</label>
											<textarea type="text" class="form-control text-dark @error('meta_key_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" name="meta_key_ru" placeholder="Введите ключевые слова на русском">{{$pageconstruct->meta_key_ru}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_key_ru') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">ключевые слова (казахский)</label>
											<textarea type="text" class="form-control text-dark @error('meta_key_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" name="meta_key_kz" placeholder="Введите ключевые слова на казахском">{{$pageconstruct->meta_key_kz}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_key_kz') {{$message}} @enderror</div>
										</div>
									</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('redactor.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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
	<!-- <script src="{{asset('assets/plugins/fancyuploder/fancy-uploader.js')}}"></script> -->

	<script src="{{asset('assets/js/formelementadvnced.js')}}"></script>


	<!-- картинка при редактировании -->
	<!-- data-default-file="../assets/images/photos/1.jpg" -->

	<script src="{{asset('assets/plugins/summernote-editor/summernote1.js')}}"></script>
	<script src="{{asset('assets/js/summernote.js')}}"></script>

	
@endsection
			