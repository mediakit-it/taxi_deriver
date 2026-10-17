@extends('master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Редактирование тега продукта</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('tag.index')}}">Категории</a></li>
						<li class="breadcrumb-item active" aria-current="page">Редактирование тега продукта</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('tag.update',['tag'=>$tag->slug])}}" method="POST">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о теге продукта</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Название</label>
											<input type="text" class="form-control text-dark @error('name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$tag->name}}" name="name" placeholder="Введите название категории">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание</label>
										<textarea id="summernote" name="description">{{ $tag->description }}</textarea>								
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Краткое описание</label>
										<textarea id="summernote2" name="short_description">{{ $tag->short_description }}</textarea>
									</div>
								</div>
								<div class="row p-5 border-bottom">
									<div class="col-xl-12">
										<h4 class="mb-4">SEO</h4>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Meta title:</label>
											<input type="text" class="form-control text-dark @error('meta_title') is-invalid @enderror" name="meta_title" value="{{$tag->meta_title}}" placeholder="Введите мета заголовок">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_title') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Meta description:</label>
											<textarea class="form-control @error('meta_description') is-invalid @enderror" name="meta_description" id="company-address" cols="30" rows="4" placeholder="Введите мета описание">{{$tag->meta_description}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_description') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Meta keywords:</label>
											<textarea class="form-control @error('meta_keywords') is-invalid @enderror" name="meta_keywords" id="company-shippingaddress" cols="30" rows="4" placeholder="Введите мета ключи">{{$tag->meta_keywords}}</textarea>
											<div id="validationServer03Feedback" class="invalid-feedback">@error('meta_keywords') {{$message}} @enderror</div>
										</div>
									</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('tag.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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
			