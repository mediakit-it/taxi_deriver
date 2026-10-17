@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Информация по заказу</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('shopProduct.index')}}">Заказы</a></li>
						<li class="breadcrumb-item active" aria-current="page">Информация о заказе</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('shopProduct.store')}}" method="POST" enctype="multipart/form-data">
				@csrf
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о заказе</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Страна в которой находится магазин:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectCountry" name="country" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
													<option value="Беларусь">Беларусь</option>
													<option value="Россия">Россия</option>
													<option value="Казахстан">Казахстан</option>
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Выберите магазин:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectShop" name="shop_id" data-placeholder="Выберите магазин" tabindex="-1" aria-hidden="true">
												@foreach ($shops as $shop)
													<option value="{{$shop->id}}">{{$shop->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Выберите товар:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="product_id" data-placeholder="Выберите товар" tabindex="-1" aria-hidden="true">
												@foreach ($products as $product)
													<option value="{{$product->id}}">{{$product->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input end-date-checkbox" name="variable" id="variable" value="0">
												<span class="custom-control-label">Есть ли вариант?</span>
											</label>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12" id="variable_true" style="display: none;">
									<h3 style="margin-bottom:25px;">Создание вариантов:</h3>
										<table data-dynamicrows>
											<thead>
												<tr>
												<th>Атрибут рус</th>
												<th>Атрибут каз</th>
												<th>Вариант рус</th>
												<th>Вариант каз</th>
												<th>Цена</th>
												<th>Цена со скидкой</th>
												<th>Количество</th>
												<th>Действие</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><input class="form-control" type="text" name="contacts[0][nameAttribute_ru]"></td>
													<td><input class="form-control" type="text" name="contacts[0][nameAttribute_kz]"></td>
													<td><input class="form-control" type="text" name="contacts[0][name_ru]"></td>
													<td><input class="form-control" type="text" name="contacts[0][name_kz]"></td>
													<td><input class="form-control" type="number" step="any" name="contacts[0][price]"></td>
													<td><input class="form-control" type="number" step="any" name="contacts[0][priceDiscount]"></td>
													<td><input class="form-control" type="number" name="contacts[0][quantity]"></td>
													<td>
														<i class="fa fa-minus" data-remove></i>
														<i class="fa fa-arrows" data-move></i>
														<i class="fa fa-plus" data-add></i>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4 variable_false">
										<div class="form-group">
											<label class="form-label text-muted">Цена</label>
											<input type="number" step="any" class="form-control text-dark @error('price') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('price')}}" name="price" placeholder="Введите цену">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('price') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4 variable_false">
										<div class="form-group">
											<label class="form-label text-muted">Цена со скидкой</label>
											<input type="number" step="any" class="form-control text-dark @error('discounted_price') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('discounted_price')}}" name="discounted_price" placeholder="Введите цену со скидкой">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('discounted_price') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4 variable_false">
										<div class="form-group">
											<label class="form-label text-muted">Количество</label>
											<input  type="number" class="form-control text-dark @error('quantity') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('quantity')}}" name="quantity" placeholder="Введите количество">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('quantity') {{$message}} @enderror</div>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('shopProduct.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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


	<script src="{{asset('assets/js/formelementadvnced.js')}}"></script>

	<script src="{{asset('assets/plugins/summernote-editor/summernote1.js')}}"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery-ui-sortable@1.0.0/jquery-ui.min.js"></script>

	<script src="{{asset('assets/js/dynamicrows.js')}}"></script>

	<!-- NOTIFICATION -->

	<script src="{{asset('assets/plugins/notify/js/rainbow.js')}}"></script>
	<script src="{{asset('assets/plugins/notify/js/sample.js')}}"></script>
	<script src="{{asset('assets/plugins/notify/js/notifIt.js')}}"></script>


	<script>

		document.addEventListener("DOMContentLoaded", function() {
            @if(session('errorTextAdd'))
				not13("{{ session('errorTextAdd') }}");
                // Ваш дополнительный JavaScript код здесь
            @endif
        });

		$(function() {
			$('[data-dynamicrows]').dynamicrows();
		});

		$("#variable").change(function() {
			if($(this).prop('checked')) {
				$('#variable_true').show();
				$('.variable_false').hide();
				
			} else {
				$('#variable_true').hide();
				$('.variable_false').show();

			}
		});


		$(document).on('change','#selectCountry',function(e){
			var selectCountry = $(this).val();
			var returnHtml = ``;
			$.ajax({
				type: 'POST',
				url: "/admin/shopCountryReturn",
				headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},
				data: {'selectCountry': selectCountry},
				success: function (data) {
					data.forEach((element) => {
						returnHtml+=`<option value="${element.id}">${element.name_ru}</option>`;
					});
					$('#selectShop').html('').append(returnHtml);
				},
				error: function (error) {
					console.log(error);
				}
			});
		});
	</script>
@endsection
			