@extends('admin.master')

@section('content')
<!-- Добавь это в <head> для автозаполнения -->

<style>

.parent-container {
    overflow: visible !important;
}

.address-suggest {
    display: none;
    margin-top: 4px;
    max-height: 260px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #e6ebf1;
    border-radius: 6px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.address-suggest .suggest-item {
    padding: 8px 12px;
    cursor: pointer;
}

.address-suggest .suggest-item:hover {
    background: #f2f6ff;
}

.address-suggest .suggest-title {
    color: #1d273b;
    font-weight: 600;
}

.address-suggest .suggest-sub {
    color: #8492a6;
    font-size: 12px;
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
											<select class="form-control form-select text-dark" value="{{old('driver_id')}}" name="driver_id" id="client-salution1" data-placeholder="Выберите водителя">
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
									<div class="col-sm-12 col-md-12 col-xl-5">
										<div class="form-group">
											<label class="form-label text-muted">Источник заказа:</label>
											<select class="form-control form-select text-dark" name="source">
												@foreach ($sources as $sourceValue => $sourceLabel)
													<option value="{{ $sourceValue }}" @selected(old('source', 'phone') == $sourceValue)>{{ $sourceLabel }}</option>
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
												<div class="address-suggest" id="from_address_suggest"></div>
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
												<div class="address-suggest" id="to_address_suggest"></div>
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
						<p><strong>Дистанция:</strong> <span id="summary-distance">---</span> км</p>
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

<script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('services.yandex.js_key') }}&lang=ru_RU&load=geocode,route,Map,Placemark"></script>

<script>
$(document).ready(function () {
	const GEOCODE_SEARCH_URL = '{{ route('admin.geocode.search') }}';
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
                    $('#summary-distance').text((distance / 1000).toFixed(2).replace('.', ',')); // Расстояние в километрах
                    $('#summary-duration').text(`${hours} ч ${minutes} мин`); // Время в пути

					drawRouteOnMap(coordsFrom, coordsTo);
                }, function (error) {
                    console.error('Ошибка при построении маршрута:', error);
                });
            } else {
                console.log("Не хватает координат для расчета дистанции.");
            }
        }

        function clearDistance() {
            $('#distance').val('');
            $('#duration').val('');
            $('#summary-distance').text('---');
            $('#summary-duration').text('---');
        }

        // Подсказки адресов - от собственного бэкенд-геокодера
        // (GET /admin/data/geocode-search -> YandexGeocodeService),
        // а не от SuggestView: его suggest_apikey (Geosuggest)
        // отвечает 403, и список не появлялся вовсе. Выбор пункта
        // сразу пишет адрес и координаты в скрытые поля; ручная
        // правка текста после выбора сбрасывает координаты этого
        // поля, чтобы текст и точка не разъезжались.
        function bindAddressSuggest(opts) {
            const $input = $(opts.input);
            const $box = $(opts.box);
            let timer = null;
            let requestSeq = 0;
            let lastSelected = null;

            function hide() {
                $box.hide().empty();
            }

            function select(point) {
                const address = point.display_address || point.address || '';
                lastSelected = address;
                $input.val(address);
                $(opts.latField).val(point.lat);
                $(opts.lngField).val(point.lng);
                $(opts.summary).text(address);
                hide();
                opts.onSelect([point.lat, point.lng]);
            }

            function render(points) {
                $box.empty();

                if (!points.length) {
                    hide();
                    return;
                }

                points.forEach(function (point) {
                    const isOrg = point.kind === 'org' && point.name;
                    const title = isOrg ? point.name : (point.display_address || point.address || '');
                    const sub = isOrg ? (point.display_address || '') : (point.locality || '');
                    const $item = $('<div class="suggest-item"></div>');

                    $('<div class="suggest-title"></div>').text(title).appendTo($item);

                    if (sub && sub !== title) {
                        $('<div class="suggest-sub"></div>').text(sub).appendTo($item);
                    }

                    $item.on('mousedown', function (e) {
                        e.preventDefault();
                        select(point);
                    });

                    $box.append($item);
                });

                $box.show();
            }

            $input.on('input', function () {
                const value = $input.val();

                if (lastSelected !== null && value !== lastSelected) {
                    lastSelected = null;
                    $(opts.latField).val('');
                    $(opts.lngField).val('');
                    opts.onClear();
                }

                clearTimeout(timer);

                const query = value.trim();

                if (query.length < 3) {
                    hide();
                    return;
                }

                timer = setTimeout(function () {
                    const seq = ++requestSeq;

                    fetch(GEOCODE_SEARCH_URL + '?address=' + encodeURIComponent(query) + '&limit=5', {
                        headers: { 'Accept': 'application/json' }
                    }).then(function (response) {
                        return response.ok ? response.json() : null;
                    }).then(function (json) {
                        if (seq !== requestSeq || !json || !json.success) {
                            return;
                        }

                        render(json.data || []);
                    }).catch(function () {
                        // Подсказки - best-effort: форма работает и без них
                    });
                }, 300);
            });

            $input.on('blur', function () {
                setTimeout(hide, 150);
            });
        }

        bindAddressSuggest({
            input: fromInput,
            box: document.getElementById('from_address_suggest'),
            latField: '#from_lat',
            lngField: '#from_lng',
            summary: '#summary-from-address',
            onSelect: function (coords) {
                coordsFrom = coords;
                calculateDistance();
            },
            onClear: function () {
                coordsFrom = null;
                clearDistance();
            }
        });

        bindAddressSuggest({
            input: toInput,
            box: document.getElementById('to_address_suggest'),
            latField: '#to_lat',
            lngField: '#to_lng',
            summary: '#summary-to-address',
            onSelect: function (coords) {
                coordsTo = coords;
                calculateDistance();
            },
            onClear: function () {
                coordsTo = null;
                clearDistance();
            }
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
