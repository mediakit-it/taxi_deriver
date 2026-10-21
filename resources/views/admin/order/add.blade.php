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
					<h1 class="page-title">Добавление заказа</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('order.index')}}">Заказы</a></li>
						<li class="breadcrumb-item active" aria-current="page">Добавление заказа</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('order.store')}}" method="POST">
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
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Фамилия клиента</label>
											<input type="text" class="form-control text-dark @error('client_first_name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('client_first_name')}}" name="client_first_name" placeholder="Введите фамилию клиента">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('client_first_name') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Имя клиента</label>
											<input type="text" class="form-control text-dark @error('client_last_name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{old('client_last_name')}}" name="client_last_name" placeholder="Введите имя клиента">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('client_last_name') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Телефон клиента</label>
											<input type="text" class="form-control text-dark @error('client_phone') is-invalid @enderror" name="client_phone" value="{{old('client_phone')}}" placeholder="Введите номер телефона клиента">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('client_phone') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-7">
										<div class="form-group">
											<label class="form-label text-muted">Выберите водителя(Не обязательно):</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" value="{{old('driver_id')}}" name="driver_id" id="client-salution1" data-placeholder="Выберите водителя" tabindex="-1" aria-hidden="true">
												<option value="" selected>-</option>
												@foreach ($users as $user)
													<option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-5">
										<div class="form-group">
											<label class="form-label text-muted">Класс обслуживания:</label>
											<select class="form-control form-select text-dark" name="tariff">
												<option value="" selected>-</option>
												@foreach ($tariffs as $tariff)
													<option value="{{ $tariff->name }}" @selected(old('tariff') == $tariff->name)>{{ $tariff->label }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<!-- Адрес клиента -->
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Адрес клиента:</label>
											<input type="text" id="from_address" name="from_address"
												class="form-control text-dark @error('from_address') is-invalid @enderror"
												value="{{ old('from_address') }}" placeholder="Введите адрес клиента">
											<div class="invalid-feedback">@error('from_address') {{ $message }} @enderror</div>
										</div>
									</div>

									<!-- Конечный адрес -->
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Конечный адрес:</label>
											<input type="text" id="to_address" name="to_address"
												class="form-control text-dark @error('to_address') is-invalid @enderror"
												value="{{ old('to_address') }}" placeholder="Введите конечный адрес клиента">
											<div class="invalid-feedback">@error('to_address') {{ $message }} @enderror</div>
										</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Цена</label>
											<input type="text" class="form-control text-dark @error('price') is-invalid @enderror" name="price" value="{{old('price')}}" placeholder="Введите цену поездки">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('price') {{$message}} @enderror</div>
										</div>
									</div>
									<input type="hidden" name="from_lat" id="from_lat">
									<input type="hidden" name="from_lng" id="from_lng">
									<input type="hidden" name="to_lat" id="to_lat">
									<input type="hidden" name="to_lng" id="to_lng">
									<input type="hidden" name="distance" id="distance">
									<input type="hidden" name="duration" id="duration">
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('order.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
										<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Сохранить</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--ROW CLOSED-->
			</form>

			<!-- Order Information Summary -->
			<div class="order-summary">
				<div class="row">
					<div class="col-md-6">
						<h4>Информация о заказе:</h4>
						<p><strong>Фамилия клиента:</strong> <span id="summary-client-first-name">---</span></p>
						<p><strong>Имя клиента:</strong> <span id="summary-client-last-name">---</span></p>
						<p><strong>Телефон клиента:</strong> <span id="summary-client-phone">---</span></p>
						<p><strong>Адрес клиента:</strong> <span id="summary-from-address">---</span></p>
						<p><strong>Конечный адрес:</strong> <span id="summary-to-address">---</span></p>
						<p><strong>Водитель:</strong> <span id="summary-driver-name">---</span></p>
						<p><strong>Цена:</strong> <span id="summary-price">---</span></p>
						<p><strong>Дистанция:</strong> <span id="summary-distance">---</span> метров</p>
						<p><strong>Время в пути:</strong> <span id="summary-duration">---</span></p>
					</div>
					<div class="col-md-6">
						<div id="yandex-map" style="width: 100%; height: 400px; margin-top: 20px;"></div>
					</div>
				</div>
			</div>
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

<!-- <script src="//api-maps.yandex.ru/2.1/?apikey=ad38720a-e8c9-443c-b454-1f4d2499ad78&suggest_apikey=871a8f7b-85ec-4bc7-b5cb-f336458187b6&lang=ru_RU&load=SuggestView&onload=onLoad"></script> -->
<script src="https://api-maps.yandex.ru/2.1/?apikey=ad38720a-e8c9-443c-b454-1f4d2499ad78&suggest_apikey=871a8f7b-85ec-4bc7-b5cb-f336458187b6&lang=ru_RU&load=SuggestView,geocode,route,Map,Placemark"></script>

<script>
$(document).ready(function () {
	// Обработчик для изменения фамилии клиента
    $('input[name="client_first_name"]').on('input', function() {
        const clientFirstName = $(this).val();
        $('#summary-client-first-name').text(clientFirstName);
    });

    // Обработчик для изменения имени клиента
    $('input[name="client_last_name"]').on('input', function() {
        const clientLastName = $(this).val();
        $('#summary-client-last-name').text(clientLastName);
    });

    // Обработчик для изменения телефона клиента
    $('input[name="client_phone"]').on('input', function() {
        const clientPhone = $(this).val();
        $('#summary-client-phone').text(clientPhone);
    });

    // Обработчик для изменения водителя
    $('select[name="driver_id"]').on('change', function() {
        const driverId = $(this).val();
        const driverName = $(this).find("option:selected").text(); // Получаем имя выбранного водителя

        // Если водителя не выбрали, выводим пустое значение
        if (driverId) {
            $('#summary-driver-name').text(driverName); // Отображаем имя водителя в блоке информации о заказе
        } else {
            $('#summary-driver-name').text('-'); // Если водитель не выбран, выводим дефолтное значение
        }
    });

    ymaps.ready(function () {
		initMap();
		
        const fromInput = document.getElementById('from_address');
        const toInput = document.getElementById('to_address');

        const suggestFrom = new ymaps.SuggestView('from_address');
        const suggestTo = new ymaps.SuggestView('to_address');

        let coordsFrom = null;
        let coordsTo = null;

        function calculateDistance() {
            console.log("calculateDistance called");

            // Проверяем, пришли ли координаты
            if (coordsFrom && coordsTo) {
                console.log("coordsFrom:", coordsFrom);
                console.log("coordsTo:", coordsTo);

                ymaps.route([coordsFrom, coordsTo]).then(function (route) {
                    const distance = route.getLength(); // Расстояние в метрах
                    const time = route.getTime(); // Время в пути в миллисекундах

                    const km = distance; // Переводим в километры

                    // Время в пути в миллисекундах
                    const timeInHours = time / 3600000; // Время в часах
                    const hours = Math.floor(timeInHours); // Целое количество часов
                    const minutes = Math.round((timeInHours - hours) * 60); // Оставшиеся минуты

                    // Записываем значения в скрытые поля с использованием jQuery
                    $('#distance').val(Math.round(km)); // Расстояние
                    $('#duration').val(hours * 60 + minutes); // Время в пути в минутах

                    // Обновляем информацию о заказе
                    $('#summary-distance').text(km); // Расстояние
                    $('#summary-duration').text(`${hours} ч ${minutes} мин`); // Время в пути

					drawRouteOnMap(coordsFrom, coordsTo);
                }, function (error) {
                    console.error('Ошибка при построении маршрута:', error);
                });
            } else {
                console.log("Не хватает координат для расчета дистанции.");
            }
        }

        suggestFrom.events.add('select', function (e) {
            const selected = e.get('item').value;
            ymaps.geocode(selected).then(function (res) {
                const firstGeoObject = res.geoObjects.get(0);
                const coords = firstGeoObject.geometry.getCoordinates();
                const address = firstGeoObject.getAddressLine();

                console.log('Откуда:', address, coords);
                coordsFrom = coords;

                // Заполняем скрытые поля с координатами
                $('#from_lat').val(coords[1]);
                $('#from_lng').val(coords[0]);

                // Обновляем информацию о заказе
                $('#summary-from-address').text(address);

                calculateDistance(); // Пересчитываем дистанцию
            });
        });

        suggestTo.events.add('select', function (e) {
            const selected = e.get('item').value;
            ymaps.geocode(selected).then(function (res) {
                const firstGeoObject = res.geoObjects.get(0);
                const coords = firstGeoObject.geometry.getCoordinates();
                const address = firstGeoObject.getAddressLine();

                console.log('Куда:', address, coords);
                coordsTo = coords;

                // Заполняем скрытые поля с координатами
                $('#to_lat').val(coords[1]);
                $('#to_lng').val(coords[0]);

                // Обновляем информацию о заказе
                $('#summary-to-address').text(address);

                calculateDistance(); // Пересчитываем дистанцию
            });
        });
    });

	let map, routeLine;

	function initMap() {
        const center = [53.5211, 30.2457]; // Координаты Быхова

        map = new ymaps.Map("yandex-map", {
            center: center,
            zoom: 12,
            // controls: ['zoomControl']
        });

        const placemark = new ymaps.Placemark(center, {
            balloonContent: 'Быхов'
        });

        // map.geoObjects.add(placemark);
    }


	function drawRouteOnMap(fromCoords, toCoords) {
    if (!map) {
        console.error("Карта не инициализирована!");
        return;
    }

    if (!fromCoords || !toCoords) {
        console.error("Недостаточно координат для построения маршрута.");
        return;
    }

    if (routeLine) {
        map.geoObjects.remove(routeLine);
    }

	ymaps.route([fromCoords, toCoords]).then(function (route) {
    routeLine = route;
    map.geoObjects.add(routeLine);

    // Правильный способ получения границ
    const bounds = route.getPaths().getBounds();

    if (bounds) {
        map.setBounds(bounds, {
            checkZoomRange: true,
            zoomMargin: 50,
            duration: 1000
        });
    } else {
        console.warn("Не удалось получить границы маршрута.");
    }
}, function (error) {
    console.error("Ошибка построения маршрута:", error);
});
}

	});
</script>





@endsection
