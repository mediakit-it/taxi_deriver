@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Изменение бренда</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('product.index')}}">Продукты</a></li>
						<li class="breadcrumb-item active" aria-current="page">Изменение бренда</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('product.update',['product'=>$product->slug])}}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
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
										<input type="hidden" id="uploadedUrls" name="uploadedUrls" value="{{json_encode($product->gallery ?? [])}}">
										<label class="form-label text-muted">Добавить картинки товара</label>
										<div>
											<input id="demo" type="file" name="files" accept=" image/jpeg, image/png, text/html, application/zip, text/css, text/js" multiple />
										</div>
										<!-- <input type="file" name="files[]" class="dropify" data-height="200" multiple/> -->
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название рус</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$product->name_ru}}" name="name_ru" placeholder="Введите русское название продукта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название каз</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$product->name_kz}}" name="name_kz" placeholder="Введите казахское название продукта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание рус</label>
										<textarea name="description_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$product->description_ru}}</textarea>								
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание каз</label>
										<textarea name="description_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$product->description_kz}}</textarea>								
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Состав рус</label>
										<textarea class="form-control @error('sostav_ru') is-invalid @enderror" id="validationTextarea" name="sostav_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$product->sostav_ru}}</textarea>								
										<div class="invalid-feedback">@error('sostav_ru') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Состав каз</label>
										<textarea class="form-control @error('sostav_kz') is-invalid @enderror" id="validationTextarea" name="sostav_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$product->sostav_kz}}</textarea>								
										<div class="invalid-feedback">@error('sostav_kz') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Дополнительная информация рус</label>
										<textarea class="form-control @error('dop_info_ru') is-invalid @enderror" id="validationTextarea" name="dop_info_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$product->dop_info_ru}}</textarea>								
										<div class="invalid-feedback">@error('dop_info_ru') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<label class="form-label text-muted">Дополнительная информация каз</label>
										<textarea class="form-control @error('dop_info_kz') is-invalid @enderror" id="validationTextarea" name="dop_info_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$product->dop_info_kz}}</textarea>								
										<div class="invalid-feedback">@error('dop_info_kz') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Метки</label>
											<select multiple="multiple" class="form-control select2 form-select select2-client-search select2-hidden-accessible text-dark" name="metkas[]" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
												@foreach($metkas as $metka)
													<option value="{{$metka->id}}" @if(in_array($metka->id, $productMetkas)) selected @endif>{{$metka->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Артикул</label>
											<input type="text" class="form-control text-dark @error('article') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$product->article}}" name="article" placeholder="Введите артикул продукта">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('article') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">К какой категории относится:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="category_id" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
												@foreach ($categorys as $category)
													<option value="{{$category->id}}" {{$product->category_id == $category->id ? 'selected' : ''}}>{{$category->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">К какому бренду относится:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="brand_id" data-placeholder="Выберите бренд" tabindex="-1" aria-hidden="true">
												@foreach ($brands as $brand)
													<option value="{{$brand->id}}" {{$product->brand_id == $brand->id ? 'selected' : ''}}>{{$brand->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
								</div>

								<div class="row p-5 border-bottom">
									<h4 class="mb-4">Характеристики</h4>
									<div class="add_attribute">
										@php
											$position = 0;
										@endphp
										@foreach($productAttributes as $productAttribute)
											@php
												$position++;
											@endphp
											
											<div class="parent_charact row">
												<div class="form-group сol-12 col-md-5">
													<label class="form-label">Атрибут</label>
													<select class="form-control select2-show-search form-select selector_attribute" name="select_attribute[name{{$position}}][]" data-placeholder="Choose one">
														<option value="-">Не задано</option>
														@foreach($attributes as $attribute)
															<option value="{{$attribute->id}}" {{$productAttribute['attribute_id'] == $attribute->id ? 'selected' : ''}}>{{$attribute->name_ru}}</option>
														@endforeach
													</select>
												</div>
												<div class="form-group сol-12 col-md-5">
													<label class="form-label">Вариант</label>
													<select multiple class="form-control select2-show-search form-select selector_varaint" name="select_variant[name{{$position}}][]" data-placeholder="Choose one">
														@foreach(\App\Models\AttributeVariant::where('attribute_id',$productAttribute['attribute_id'])->get() as $variant)
															<option value="{{$variant->id}}" {{in_array($variant->id, $productAttribute['attribute_variant_ids']->toArray()) ? 'selected' : ''}}>{{$variant->name_ru}}</option>
														@endforeach
													</select>
												</div>
												<div class="form-group сol-12 col-md-2 d-flex">
													<button class="btn btn-danger" id="deleteBtnCharacter" style="align-self: flex-end"><i class="fe fe-trash"></i></button>
												</div>
											</div>
										@endforeach
									</div>
									<div>
										<button class="btn btn-danger" id="add_attribute_btn"> + Добавить характеристику </button>
									</div>
								</div>

								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('product.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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

	<script src="{{asset('assets/js/formelementadvnced.js')}}"></script>


	<!-- картинка при редактировании -->
	<!-- data-default-file="../assets/images/photos/1.jpg" -->

	<script src="{{asset('assets/plugins/summernote-editor/summernote1.js')}}"></script>
	<script>
		$(document).ready(function () {

			

			(function($) {
				//возврат размера файла
				function getFileSize(url) {
					var xhr = new XMLHttpRequest();
					xhr.open('HEAD', url, false);
					xhr.send(null);
					if (xhr.status === 200) {
						var contentLength = xhr.getResponseHeader('Content-Length');
						var fileSizeInKB = contentLength / 1024;
						return fileSizeInKB;
					} else {
						throw new Error('Не удалось получить размер файла');
					}
				}

				function addExistingFiles(files) {
					files.forEach(function(file_url) {
						// var inforow = $('<tr class="ff_fileupload_done"><td class="ff_fileupload_preview"><img src="' + file_url + '" alt="" style="width:100px;"></td><td class="ff_fileupload_name">' + file_url.split('/').pop() + '</td><td class="ff_fileupload_remove"><button class="ff_fileupload_remove_file btn btn-danger">Remove</button></td></tr>');
						var inforow =`
						<tr class="">
							<td class="ff_fileupload_preview">
								<button class="ff_fileupload_preview_image ff_fileupload_preview_image_has_preview" type="button" aria-label="Preview" style="background-image: url(${file_url});">
									<span class="ff_fileupload_preview_text"></span>
								</button>
								<div class="ff_fileupload_actions_mobile">
									<button class="ff_fileupload_remove_file" type="button" aria-label="Remove from list"></button>
								</div>
							</td>
							<td class="ff_fileupload_summary">
								<div class="ff_fileupload_filename">${file_url.split('/').pop()}</div>
								<div class="ff_fileupload_fileinfo">${getFileSize(file_url).toFixed(2)} KB | Upload completed</div>
								<div class="ff_fileupload_buttoninfo ff_fileupload_hidden">Start uploading</div>
								<div class="ff_fileupload_errors ff_fileupload_hidden"></div>
								<div class="ff_fileupload_progress_background ff_fileupload_hidden">
									<div class="ff_fileupload_progress_bar" style="width: 100%;"></div>
								</div>
							</td>
							<td class="ff_fileupload_actions">
								<button class="ff_fileupload_remove_file" type="button" aria-label="Remove from list"></button>
							</td>
						</tr>
						`;

						var $inforow = $(inforow);

						$inforow.find('.ff_fileupload_remove_file').on('click', function(e) {
							e.preventDefault();
							// Удаляем файл из списка
							var urlsInput = $('#uploadedUrls');
							var currentUrls = urlsInput.val() ? JSON.parse(urlsInput.val()) : [];
							var index = currentUrls.indexOf(file_url);
							if (index !== -1) {
								currentUrls.splice(index, 1);
							}
							urlsInput.val(JSON.stringify(currentUrls));
							$inforow.remove();
						});
						$('table.ff_fileupload_uploads').append($inforow);
					});
				}

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
							console.log(urlsInput.val());
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
						var urlsInput = $('#uploadedUrls');
						var currentUrls = urlsInput.val() ? JSON.parse(urlsInput.val()) : [];
						var index = currentUrls.indexOf(data._response.result.file_url); // Находим индекс элемента в массиве
						
						if (index !== -1) {
							currentUrls.splice(index, 1); // Удаляем элемент из массива
						}
						
						urlsInput.val(JSON.stringify(currentUrls)); 

						data.ff_info.inforow.remove();
					}
				});
				var existingFiles = @json($product->gallery ?? []);
        		addExistingFiles(existingFiles);
			})(jQuery);
			

			let iter = {{$position}} + 1;
			

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
			