@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Изменение товара в магазине</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('shopProduct.index')}}">Магазины и их товары</a></li>
						<li class="breadcrumb-item active" aria-current="page">Измененик товара в магазине</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('shopProduct.update',['shopProduct'=>$shop_and_product->id])}}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о магазине с товаром</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Страна в которой находится магазин:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectCountry" name="country" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
													<option value="Беларусь" {{$shop_and_product->country=="Беларусь" ? "selected" : ""}}>Беларусь</option>
													<option value="Россия" {{$shop_and_product->country=="Россия" ? "selected" : ""}}>Россия</option>
													<option value="Казахстан" {{$shop_and_product->country=="Казахстан" ? "selected" : ""}}>Казахстан</option>
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Выберите магазин:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectShop" name="shop_id" data-placeholder="Выберите магазин" tabindex="-1" aria-hidden="true">
												@foreach ($shops as $shop)
													<option value="{{$shop->id}}" {{$shop_and_product->shop_id == $shop->id ? "selected" : ""}}>{{$shop->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Выберите товар:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="product_id" data-placeholder="Выберите товар" tabindex="-1" aria-hidden="true">
												@foreach ($products as $product)
													<option value="{{$product->id}}" {{$shop_and_product->product_id == $product->id ? "selected" : ""}}>{{$product->name_ru}}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="custom-control custom-checkbox">
												<input type="checkbox" class="custom-control-input end-date-checkbox" name="variable" id="variable" value="0" {{$shop_and_product->variable == "true" ? "checked" : ""}}>
												<span class="custom-control-label">Есть ли вариант?</span>
											</label>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12" id="variable_true" @if($shop_and_product->variable=="false") style="display: none;" @endif>
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
												@if(!is_null($shop_and_product->variations))
													@forelse($shop_and_product->variations as $variationItem)
													<tr>
														<td><input class="form-control" type="text" name="contacts[0][nameAttribute_ru]" value="{{$variationItem['nameAttribute_ru']}}"></td>
														<td><input class="form-control" type="text" name="contacts[0][nameAttribute_kz]" value="{{$variationItem['nameAttribute_kz']}}"></td>
														<td><input class="form-control" type="text" name="contacts[0][name_ru]" value="{{$variationItem['name_ru']}}"></td>
														<td><input class="form-control" type="text" name="contacts[0][name_kz]" value="{{$variationItem['name_kz']}}"></td>
														<td><input class="form-control" type="number" step="any" name="contacts[0][price]" value="{{$variationItem['price']}}"></td>
														<td><input class="form-control" type="number" step="any" name="contacts[0][priceDiscount]" value="{{$variationItem['priceDiscount']}}"></td>
														<td><input class="form-control" type="number" name="contacts[0][quantity]" value="{{$variationItem['quantity']}}"></td>
														<td>
															<i class="fa fa-minus" data-remove></i>
															<i class="fa fa-arrows" data-move></i>
															<i class="fa fa-plus" data-add></i>
														</td>
													</tr>
													@empty
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
													@endforelse
												@else
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
												@endif
											</tbody>
										</table>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4 variable_false" @if($shop_and_product->variable=="true") style="display: none;" @endif>
										<div class="form-group">
											<label class="form-label text-muted">Цена</label>
											<input type="number" step="any" class="form-control text-dark @error('price') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$shop_and_product->price}}" name="price" placeholder="Введите цену">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('price') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4 variable_false" @if($shop_and_product->variable=="true") style="display: none;" @endif>
										<div class="form-group">
											<label class="form-label text-muted">Цена со скидкой</label>
											<input type="number" step="any" class="form-control text-dark @error('discounted_price') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$shop_and_product->discounted_price}}" name="discounted_price" placeholder="Введите цену со скидкой">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('discounted_price') {{$message}} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-4 variable_false" @if($shop_and_product->variable=="true") style="display: none;" @endif>
										<div class="form-group">
											<label class="form-label text-muted">Количество</label>
											<input  type="number" class="form-control text-dark @error('quantity') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$shop_and_product->quantity}}" name="quantity" placeholder="Введите количество">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('quantity') {{$message}} @enderror</div>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('shopProduct.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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
			