@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Добавление товара</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('product.index')}}">Товары</a></li>
						<li class="breadcrumb-item active" aria-current="page">Добавление товара</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('product.store')}}" method="POST" enctype="multipart/form-data">
				@csrf
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о товаре</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-12">
										<input type="hidden" id="uploadedUrls" name="uploadedUrls" value="[]">
										<label class="form-label text-muted">Добавить картинки товара</label>
										<div>
											<input id="demo" type="file" name="files" accept=" image/jpeg, image/png, text/html, application/zip, text/css, text/js" multiple />
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название рус</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_ru')}}" name="name_ru" placeholder="Введите русское название продукта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название каз</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('name_kz')}}" name="name_kz" placeholder="Введите казахское название продукта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание рус</label>
										<textarea class="form-control @error('description_ru') is-invalid @enderror" id="validationTextarea" name="description_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{old('description_ru')}}</textarea>								
										<div class="invalid-feedback">@error('description_ru') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание каз</label>
										<textarea class="form-control @error('description_kz') is-invalid @enderror" id="validationTextarea" name="description_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{old('description_kz')}}</textarea>								
										<div class="invalid-feedback">@error('description_kz') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Состав рус</label>
										<textarea class="form-control @error('sostav_ru') is-invalid @enderror" id="validationTextarea" name="sostav_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{old('sostav_ru')}}</textarea>								
										<div class="invalid-feedback">@error('sostav_ru') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Состав каз</label>
										<textarea class="form-control @error('sostav_kz') is-invalid @enderror" id="validationTextarea" name="sostav_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{old('sostav_kz')}}</textarea>								
										<div class="invalid-feedback">@error('sostav_kz') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Дополнительная информация рус</label>
										<textarea class="form-control @error('dop_info_ru') is-invalid @enderror" id="validationTextarea" name="dop_info_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{old('dop_info_ru')}}</textarea>								
										<div class="invalid-feedback">@error('dop_info_ru') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Дополнительная информация каз</label>
										<textarea class="form-control @error('dop_info_kz') is-invalid @enderror" id="validationTextarea" name="dop_info_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{old('dop_info_kz')}}</textarea>								
										<div class="invalid-feedback">@error('dop_info_kz') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Метки</label>
											<select multiple="multiple" class="form-control select2 form-select select2-client-search select2-hidden-accessible text-dark" name="metkas[]" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
												@foreach($metkas as $metka)
													<option value="{{$metka->id}}">{{$metka->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Артикул</label>
											<input type="text" class="form-control text-dark @error('article') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('article')}}" name="article" placeholder="Введите артикул продукта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('article') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">К какой категории относится:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="category_id" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
												@foreach ($categorys as $category)
													<option value="{{$category->id}}">{{$category->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">К какому бренду относится:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="brand_id" data-placeholder="Выберите бренд" tabindex="-1" aria-hidden="true">
												@foreach ($brands as $brand)
													<option value="{{$brand->id}}">{{$brand->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
								</div>

								<div class="row p-5 border-bottom">
									<h4 class="mb-4">Характеристики</h4>
									<div class="add_attribute">
										
									</div>
									<div>
										<button class="btn btn-danger" id="add_attribute_btn"> + Добавить характеристику </button>
									</div>
								</div>

								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('product.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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

	
	<!-- SELECT2 JS -->
	<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>

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
	<script>
		$(document).ready(function () {

			(function($) {
				// FancyFileUpload
				$('#demo').FancyFileUpload({
					maxfilesize: 1000000,
					url: '/admin/productCreateImages',
					edit:false,
					params: {
						_token: $('#csrf_token').val()
					},
					added : function(e, data) {
						// It is okay to simulate clicking the start upload button.
						this.find('.ff_fileupload_actions button.ff_fileupload_start_upload').click();
					},
					uploadcompleted: function(e, data) {
						var response = data.result;
						if (response.success) {
							var urlsInput = $('#uploadedUrls');
							var currentUrls = urlsInput.val() ? JSON.parse(urlsInput.val()) : [];
							currentUrls.push(response.file_url);
							urlsInput.val(JSON.stringify(currentUrls));
							// Добавляем обработчик нажатия на кнопку "Корзина"
							// alert('Файл успешно загружен: ' + response.file_url);
						} else {
							// alert('Ошибка загрузки файла: ' + response.error);
						}
					},
					removefile: function(e,data){
						$.ajax({
							type: 'POST',
							url: "/admin/deleteImage",
							headers: {
								'X-CSRF-TOKEN': "{{ csrf_token() }}"
							},
							data: {'image_url': data._response.result.file_url},
							success: function (resultData) {
								var urlsInput = $('#uploadedUrls');
								var currentUrls = urlsInput.val() ? JSON.parse(urlsInput.val()) : [];
								var index = currentUrls.indexOf(data._response.result.file_url); // Находим индекс элемента в массиве
								
								if (index !== -1) {
									currentUrls.splice(index, 1); // Удаляем элемент из массива
								}
								
								urlsInput.val(JSON.stringify(currentUrls)); 

								data.ff_info.inforow.remove();
							},
							error: function (error) {
								console.log(error);
							}
						});		
					}
				});
			})(jQuery);


			let iter = 1;
			

			$('#add_attribute_btn').on('click', function(e) {
				e.preventDefault();

				character = `
				<div class="parent_charact row">
					<div class="form-group сol-12 col-md-5">
						<label class="form-label">Атрибут</label>
						<select class="form-control select2-show-search form-select selector_attribute" name="select_attribute[name${iter}][]" data-placeholder="Choose one">
							<option value="-" selected>Не задано</option>
							@foreach($attributes as $attribute)
								<option value="{{$attribute->id}}">{{$attribute->name_ru}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group сol-12 col-md-5">
						<label class="form-label">Вариант</label>
						<select multiple class="form-control select2-show-search form-select selector_varaint" name="select_variant[name${iter}][]" data-placeholder="Choose one">

						</select>
					</div>
					<div class="form-group сol-12 col-md-2 d-flex">
						<button class="btn btn-danger" id="deleteBtnCharacter" style="align-self: flex-end"><i class="fe fe-trash"></i></button>
					</div>
				</div>
			`;

				$('.add_attribute').append(character);

				// Select2 by showing the search
				$('.select2-show-search').select2({
					minimumResultsForSearch: '',
					width: '100%'
				});

				iter++;

			});

			$(document).on('change','.selector_attribute', function (e) {
				var selected = $(this).val();
				var ishod = $(this);

				$.ajax({
					type: 'POST',
					url: "/admin/product-variant",
					headers: {
						'X-CSRF-TOKEN': "{{ csrf_token() }}"
					},
					data: {'id': selected},
					success: function (data) {

						var variants = ``;
						data.forEach((element) => {
							// console.log(element, element.id, element.name)
							variants += `<option value="${element.id}">${element.name_ru}</option>`
						});

						$(ishod).closest('.parent_charact').find('.selector_varaint').html('').append(variants);
					},
					error: function (error) {
						console.log(error);
					}
				});
			});

			$(document).on('click','#deleteBtnCharacter', function(e){
				e.preventDefault();
				$(this).closest('.parent_charact').remove();
			});
		});
	</script>
@endsection
			