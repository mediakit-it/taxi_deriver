@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Изменение баннер</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('banner.index')}}">Баннеры</a></li>
						<li class="breadcrumb-item active" aria-current="page">Изменение баннера</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('banner.update',['banner'=>$banner->id])}}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о баннере</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Добавить картинку баннеру</label>
										<input type="file" name="file" class="dropify" data-height="200" data-default-file="{{ asset($banner->image) }}"/>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Тип баннера:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="type_url" name="type_url" data-placeholder="Выберите тип" tabindex="-1" aria-hidden="true" onchange="toggleFields()">
													<option value="product" {{$banner->type_url=='product' ? 'selected' : ''}}>Товары</option>
													<option value="action" {{$banner->type_url=='action' ? 'selected' : ''}}>Акции</option>
													<option value="metka" {{$banner->type_url=='metka' ? 'selected' : ''}}>Метки</option>
													<option value="Brand" {{$banner->type_url=='Brand' ? 'selected' : ''}}>Бренд</option>
													<option value="noUrl" {{$banner->type_url=='noUrl' ? 'selected' : ''}}>Нет ссылки</option>
											</select>
										</div>
									</div>
									@if($banner->type_url=='product')
										<div id="product_field" class="col-sm-12 col-md-12 col-xl-6">
											<div class="form-group">
												<label class="form-label text-muted">Выберите товар:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="product_slug" name="product_slug" data-placeholder="Выберите товар" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Product::all() as $product)
														<option value="{{$product->slug}}" {{$banner->slug == $product->slug ? 'selected' : ''}}>{{$product->name_ru}}</option>
													@endforeach
												</select>
											</div>
										</div>
									@else
										<div id="product_field" class="col-sm-12 col-md-12 col-xl-6" style="display:none;">
											<div class="form-group">
												<label class="form-label text-muted">Выберите товар:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="product_slug" name="product_slug" data-placeholder="Выберите товар" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Product::all() as $product)
														@if($loop->first)
															<option value="{{$product->slug}}" selected>{{$product->name_ru}}</option>
														@else
															<option value="{{$product->slug}}">{{$product->name_ru}}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
									@endif


									@if($banner->type_url=='action')
										<div id="action_field" class="col-sm-12 col-md-12 col-xl-6">
											<div class="form-group">
												<label class="form-label text-muted">Выберите акцию:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="action_slug" name="action_slug" data-placeholder="Выберите акцию" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Discount::all() as $discount)
														<option value="{{$discount->slug}}" {{$banner->slug == $discount->slug ? 'selected' : ''}}>{{$discount->name_ru}}</option>
													@endforeach
												</select>
											</div>
										</div>
									@else
										<div id="action_field" class="col-sm-12 col-md-12 col-xl-6" style="display:none;">
											<div class="form-group">
												<label class="form-label text-muted">Выберите акцию:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="action_slug" name="action_slug" data-placeholder="Выберите акцию" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Discount::all() as $discount)
														@if($loop->first)
															<option value="{{$discount->slug}}" selected>{{$discount->name_ru}}</option>
														@else
															<option value="{{$discount->slug}}">{{$discount->name_ru}}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
									@endif

									@if($banner->type_url=='metka')
										<div id="metka_field" class="col-sm-12 col-md-12 col-xl-6">
											<div class="form-group">
												<label class="form-label text-muted">Выберите метку:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="metka_slug" name="metka_slug" data-placeholder="Выберите метку" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Metka::all() as $metka)
														<option value="{{$metka->id}}" {{$banner->slug==$metka->id ? 'selected' : ''}}>{{$metka->name_ru}}</option>
													@endforeach
												</select>
											</div>
										</div>
									@else
										<div id="metka_field" class="col-sm-12 col-md-12 col-xl-6" style="display:none;">
											<div class="form-group">
												<label class="form-label text-muted">Выберите метку:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="metka_slug" name="metka_slug" data-placeholder="Выберите метку" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Metka::all() as $metka)
														@if($loop->first)
															<option value="{{$metka->id}}" selected>{{$metka->name_ru}}</option>
														@else
															<option value="{{$metka->id}}">{{$metka->name_ru}}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
									@endif
									@if($banner->type_url=='Brand')
										<div id="Brand" class="col-sm-12 col-md-12 col-xl-6" style="display:none;">
											<div class="form-group">
												<label class="form-label text-muted">Выберите бренд:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="brand_slug" name="brand_slug" data-placeholder="Выберите Бренд" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Brand::all() as $brand)
														@if($loop->first)
															<option value="{{$brand->id}}" selected>{{$brand->name_ru}}</option>
														@else
															<option value="{{$brand->id}}">{{$brand->name_ru}}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
									@else
										<div id="Brand" class="col-sm-12 col-md-12 col-xl-6" style="display:none;">
											<div class="form-group">
												<label class="form-label text-muted">Выберите бренд:</label>
												<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="brand_slug" name="brand_slug" data-placeholder="Выберите Бренд" tabindex="-1" aria-hidden="true">
													@foreach(\App\Models\Brand::all() as $brand)
														@if($loop->first)
															<option value="{{$brand->id}}" selected>{{$brand->name_ru}}</option>
														@else
															<option value="{{$brand->id}}">{{$brand->name_ru}}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
										
									@endif
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Страна в которой показывать баннер:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectCountry" name="country" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
													<option value="Беларусь" {{$banner->country=='Беларусь' ? 'selected' : ''}}>Беларусь</option>
													<option value="Россия" {{$banner->country=='Россия' ? 'selected' : ''}}>Россия</option>
													<option value="Казахстан" {{$banner->country=='Казахстан' ? 'selected' : ''}}>Казахстан</option>
											</select>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('banner.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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

		function toggleFields() {
			let type = document.getElementById('type_url').value;
			document.getElementById('product_field').style.display = type === 'product' ? 'block' : 'none';
			document.getElementById('action_field').style.display = type === 'action' ? 'block' : 'none';
			document.getElementById('metka_field').style.display = type === 'metka' ? 'block' : 'none';
			document.getElementById('Brand').style.display = type === 'Brand' ? 'block' : 'none';
		}

		document.addEventListener('DOMContentLoaded', function () {
			toggleFields(); 
		});
	</script>
@endsection
			