@extends('admin.master')

@section('content')
<!-- Добавь это в <head> для автозаполнения -->

<style>

.parent-container {
    overflow: visible !important;
}

</style>


	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Назначение водителя</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('order.index')}}">Заказы</a></li>
						<li class="breadcrumb-item active" aria-current="page">Назначение водителя</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('order.update',['order'=>$order->id])}}" method="POST">
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
									<div class="col-md-12">
										<h4>Информация о заказе:</h4>
										@if($order->client_id)
										<p><strong>Фамилия клиента:</strong> <span id="summary-client-first-name">{{$order->client->last_name}}</span></p>
										<p><strong>Имя клиента:</strong> <span id="summary-client-last-name">{{$order->client->first_name}}</span></p>
										<p><strong>Телефон клиента:</strong> <span id="summary-client-phone">{{$order->client->phone}}</span></p>
										@else
										<p><strong>Фамилия клиента:</strong> <span id="summary-client-first-name">{{$order->client_last_name}}</span></p>
										<p><strong>Имя клиента:</strong> <span id="summary-client-last-name">{{$order->client_first_name}}</span></p>
										<p><strong>Телефон клиента:</strong> <span id="summary-client-phone">{{$order->client_phone}}</span></p>
										@endif
										<p><strong>Адрес клиента:</strong> <span id="summary-from-address">{{$order->from_address}}</span></p>
										<p><strong>Конечный адрес:</strong> <span id="summary-to-address">{{$order->to_address}}</span></p>
										<p><strong>Класс обслуживания:</strong> <span id="summary-tariff">{{$order->tariffClass?->label ?? '—'}}</span></p>
										<p><strong>Автомобиль:</strong> <span id="summary-car">{{$order->car ? $order->car->label . ' (' . $order->car->number_plate . ')' : '—'}}</span></p>
										<p><strong>Источник:</strong> <span id="summary-source">{{$order->source_label}}</span></p>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Выберите водителя(Не обязательно):</label>
											<select class="form-control form-select text-dark" value="{{old('driver_id')}}" name="driver_id" id="client-salution1" data-placeholder="Выберите водителя">
												<option value="" @selected(old('driver_id', $order->driver_id) == '')>-</option>
												@foreach ($users as $user)
													<option value="{{ $user->id }}" @selected(old('driver_id', $order->driver_id) == $user->id)>{{ $user->first_name }} {{ $user->last_name }}</option>
												@endforeach
											</select>
										</div>
									</div>
								</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Класс обслуживания:</label>
											<select class="form-control form-select text-dark" name="tariff">
												<option value="">-</option>
												@foreach ($tariffs as $tariff)
													<option value="{{ $tariff->name }}" @selected(old('tariff', $order->tariff) == $tariff->name)>{{ $tariff->label }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Источник заказа:</label>
											@if($order->source === 'app')
												<input type="text" class="form-control text-dark" value="{{ $order->source_label }}" disabled>
												<small class="text-muted">Источник заказа из приложения не меняется</small>
											@else
												<select class="form-control form-select text-dark" name="source">
													@foreach ($sources as $sourceValue => $sourceLabel)
														<option value="{{ $sourceValue }}" @selected(old('source', $order->source) == $sourceValue)>{{ $sourceLabel }}</option>
													@endforeach
												</select>
											@endif
										</div>
									</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('order.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
										<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Назначить</button>
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

	<!-- bootstrap-datepicker js -->
	<script src="{{asset('assets/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>


	<script>$('#date1-datepicker input').datepicker({
		format: 'dd-mm-yyyy',
		clearBtn:true,
		language:'ru',
		todayHighlight: true,
  		autoclose: true,
	});</script>

@endsection
