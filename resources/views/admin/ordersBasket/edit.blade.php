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
			<form action="{{route('ordersBasket.update',['ordersBasket'=>$basket->id])}}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о заказе</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<table class="table  border text-nowrap text-md-nowrap">
										<thead>
											<tr>
												<th>Наименование</th>
												<!-- <th>Бренд</th> -->
												<!-- <th>Артикул</th> -->
												<th>Борт</th>
												<th>Количесво</th>
												<th>Цена</th>
{{--												<th>Итог</th>--}}
												<th>Оплатили</th>
											</tr>
										</thead>
										<tbody>
											@foreach($basket_items as $item)
												<tr>
													<td>{{$item->product_name_ru}} {{$item->isVariation==1 ? '('.$item->nameAttributeRu.': '.$item->nameVariationRu.')' : ''}}</td>
													<td>{{$item->nameBrandRu}}</td>
													<td>{{$item->article}}</td>
													<td>{{$item->shopMagazine['name_ru']}}({{$item->shopMagazine['country']}})</td>
													<td>{{$item->quantity}}</td>
{{--													@if($item->discount<=0)--}}
{{--														<td>{{$item->price}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</td>--}}
{{--													@else--}}
{{--														<td><b>{{$item->discount}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></br><del>{{$item->price}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</del></td>--}}
{{--													@endif--}}
													@if($item->discount<=0)
														<td><b>{{$item->quantity * $item->price}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
													@else
														<td><b>{{$item->quantity * $item->discount}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
													@endif
													<td><input type="checkbox" name="oplata[]" value="{{$item->id}}" {{$item->no_payment == 1 ? 'checked' : ''}}></td>
												</tr>
											@endforeach
											<tr>
												<td colspan="5"><b>Итого</b></td>
												<td colspan="2"><b>{{$basket->getPriceItems()}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
											</tr>
											<tr>
												<td colspan="5"><b>Скидка</b></td>
												<td colspan="2"><b>{{$basket->discount}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
											</tr>
											<tr>
												<td colspan="5"><b>Скидка пользователя</b></td>
												<td colspan="2"><b>{{$basket->discountUser}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
											</tr>
											<tr>
												<td colspan="5"><b>Доставка</b></td>
												<td colspan="2"><b>{{$basket->deliveryPrice}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
											</tr>
											<tr>
												<td colspan="5"><b>Итого к оплате</b></td>
												<td colspan="2"><b>{{$basket->fullPrice}} @if($basket->country=="Беларусь") BYN @elseif($basket->country=="Россия") RUB @else KAZ @endif</b></td>
											</tr>
										</tbody>
									</table>


									<div class="row mt-5">
										<div class="col-sm-12 col-md-12 col-xl-6  border-right">
											<h4 class="mb-5">Информация о клиенте</h4>
											<dl class="row">
												<dt class="col-sm-3">Фамилия: </dt>
												<dd class="col-sm-9">{{$basket->first_name}}</dd>

												<dt class="col-sm-3">Имя: </dt>
												<dd class="col-sm-9">{{$basket->name}}</dd>

												<dt class="col-sm-3">E-mail: </dt>
												<dd class="col-sm-9">{{$basket->email}}</dd>

												<dt class="col-sm-3">Телефон: </dt>
												<dd class="col-sm-9">{{$basket->phone}}</dd>
											</dl>
										</div>
										<div class="col-sm-12 col-md-12 col-xl-6">
											<h4 class="mb-5">Информация по доставке</h4>
											<dl class="row">
												<dt class="col-sm-3">Тип доставки: </dt>
												<dd class="col-sm-9">{{$basket->nameDeliveryRu}}</dd>

												@if($basket->addressDelivery!=null)
														<dt class="col-sm-3">Фамилия: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['first_name']}}</dd>

														<dt class="col-sm-3">Имя: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['name']}}</dd>

														<dt class="col-sm-3">Страна: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['country']}}</dd>

														<dt class="col-sm-3">Область: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['region']}}</dd>

														<dt class="col-sm-3">Город: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['city']}}</dd>

														<dt class="col-sm-3">Улица: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['street']}}</dd>

														<dt class="col-sm-3">Дом: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['home']}}</dd>

														<dt class="col-sm-3">Корпус: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['corpus'] ? $basket->addressDelivery['corpus'] : '-'}}</dd>

														<dt class="col-sm-3">Квартира: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['flat']}}</dd>

														<dt class="col-sm-3">Почтовый код: </dt>
														<dd class="col-sm-9">{{$basket->addressDelivery['postCode']}}</dd>
												@endif
											</dl>
										</div>
									</div>








									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Статус заказа</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectCountry" name="obrabotka" data-placeholder="" tabindex="-1" aria-hidden="true">
													<option value="obrabotka" {{$basket->obrabotka == 'obrabotka' ? 'selected' : ''}}>Не обработан</option>
													<option value="obrabotan" {{$basket->obrabotka == 'obrabotan' ? 'selected' : ''}}>Обработан</option>
													<option value="otmena" {{$basket->obrabotka == 'otmena' ? 'selected' : ''}}>Отменен</option>
													<option value="otdan" {{$basket->obrabotka == 'otdan' ? 'selected' : ''}}>Получен</option>
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Коментарий к доставке или самовывозу русский</label>
											<input type="text" class="form-control text-dark @error('messageDostavka') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$basket->messageDostavka_ru}}" name="messageDostavka_ru" placeholder="Введите коментарий рус">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('messageDostavka_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Коментарий к доставке или самовывозу казахский</label>
											<input type="text" class="form-control text-dark @error('messageDostavka') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$basket->messageDostavka_kz}}" name="messageDostavka_kz" placeholder="Введите коментарий каз">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('messageDostavka_kz') {{$message}} @enderror</div>
										</div>
									</div>


								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('ordersBasket.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
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


@endsection
