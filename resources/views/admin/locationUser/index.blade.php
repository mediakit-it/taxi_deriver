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
					<h1 class="page-title">Водители на карте</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('order.index')}}">Заказы</a></li>
						<li class="breadcrumb-item active" aria-current="page">Водители на карте</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
		
			<div id="map" style="width: 100%; height: 600px;"></div>
			
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

<script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('services.yandex.js_key') }}&suggest_apikey={{ config('services.yandex.suggest_key') }}&lang=ru_RU&load=SuggestView,geocode,route,Map,Placemark"></script>


<script>
	$(document).ready(function () {
    let map;
    let driverPlacemarks = {};
    let viewportPositioned = false;

    ymaps.ready(init);

    function init() {
        map = new ymaps.Map("map", {
            // Запасной вид до загрузки точек - обзор всей Беларуси.
            // Как только придут водители, карта один раз встанет
            // по их точкам (positionViewport ниже).
            center: [53.7, 28.0],
            zoom: 7,
        });

        fetchAndUpdateDrivers();
        setInterval(fetchAndUpdateDrivers, 10000);

        // Живой сигнал смены статуса водителя (пакет №162):
        // не ждём очередного опроса - перечитываем водителей
        // сразу, метка перекрашивается в момент перехода.
        if (typeof window.onDriverStatusChanged === 'function') {
            window.onDriverStatusChanged(function () {
                fetchAndUpdateDrivers();
            });
        }
    }

    const colors = [
        'islands#redIcon',
        'islands#greenIcon',
        'islands#blueIcon',
        'islands#orangeIcon',
        'islands#violetIcon',
        'islands#yellowIcon',
        'islands#pinkIcon'
    ];

    function getColorByUserId(user_id) {
        return colors[user_id % colors.length];
    }

    // Пресет метки по статусу работы (пакет №162): не на линии
    // и не работает - серая метка, на линии - свой цвет водителя.
    function getPresetByDriver(driver) {
        if (driver.work_status === 'offline' || driver.work_status === 'not_working') {
            return 'islands#grayIcon';
        }
        return getColorByUserId(driver.user_id);
    }

    // Длительность текущего статуса строкой из ISO-метки начала
    // периода: «N мин» до часа, дальше «H ч MM мин».
    function formatStatusDuration(iso) {
        if (!iso) {
            return '';
        }
        const t = Date.parse(iso);
        if (isNaN(t)) {
            return '';
        }
        const mins = Math.max(0, Math.floor((Date.now() - t) / 60000));
        if (mins < 60) {
            return mins + ' мин';
        }
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return h + ' ч ' + (m < 10 ? '0' : '') + m + ' мин';
    }

    // Первичное позиционирование карты по точкам водителей -
    // один раз, когда точки впервые пришли с сервера. Дальше
    // карта диспетчеру не мешает: обновления только двигают
    // маркеры, вид не переустанавливается.
    function positionViewport(drivers) {
        if (viewportPositioned || !drivers.length) {
            return;
        }

        const points = drivers.map(driver => [parseFloat(driver.lat), parseFloat(driver.lng)]);

        if (points.length === 1) {
            map.setCenter(points[0], 13, {duration: 300});
        } else {
            let minLat = points[0][0], maxLat = points[0][0];
            let minLng = points[0][1], maxLng = points[0][1];

            points.forEach(function (point) {
                minLat = Math.min(minLat, point[0]);
                maxLat = Math.max(maxLat, point[0]);
                minLng = Math.min(minLng, point[1]);
                maxLng = Math.max(maxLng, point[1]);
            });

            const fit = map.setBounds([[minLat, minLng], [maxLat, maxLng]], {
                checkZoomRange: true,
                zoomMargin: 48
            });

            if (fit && typeof fit.then === 'function') {
                fit.then(function () {
                    // Водители вплотную друг к другу - не уходим
                    // на уровень домов.
                    if (map.getZoom() > 15) {
                        map.setZoom(15);
                    }
                });
            }
        }

        viewportPositioned = true;
    }

    async function fetchAndUpdateDrivers() {
        try {
            const response = await fetch('/admin/data/driver-locations', {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (result.status) {
                const drivers = result.data;
                const existingIds = new Set();

                drivers.forEach(driver => {
                    existingIds.add(driver.user_id);

                    const coords = [driver.lat, driver.lng];
                    const currentCar = driver.user.current_car;
                    const carLine = currentCar ? `<br>Авто: ${currentCar.brand} ${currentCar.model} · ${currentCar.number_plate}` : '';
                    const statusDuration = formatStatusDuration(driver.status_since);
                    const balloonContent = `
                        <b>Водитель ID:${driver.user_id}. ФИО: ${driver.user.last_name} ${driver.user.first_name}</b><br>
                        Статус: ${driver.work_status_label}${statusDuration ? ' (' + statusDuration + ')' : ''}<br>
                        Заказ: ${driver.is_zakaz && driver.order_id ? 'выполняет №' + driver.order_id : 'свободен'}${carLine}
                    `;
                    // Подсказка при наведении: кто это и в каком
                    // статусе, без клика и открытия балуна.
                    const hintContent = `${driver.user.last_name} ${driver.user.first_name} - ${driver.work_status_label}${driver.is_zakaz && driver.order_id ? ' · заказ №' + driver.order_id : ''}`;
                    const preset = getPresetByDriver(driver);

                    if (driverPlacemarks[driver.user_id]) {
                        driverPlacemarks[driver.user_id].geometry.setCoordinates(coords);
                        driverPlacemarks[driver.user_id].properties.set({
                            balloonContentBody: balloonContent,
                            hintContent: hintContent
                        });
                        driverPlacemarks[driver.user_id].options.set('preset', preset);
                    } else {
                        const placemark = new ymaps.Placemark(coords, {
                            balloonContentBody: balloonContent,
                            hintContent: hintContent
                        }, {
                            preset: preset,
                            balloonPanelMaxMapArea: 0
                        });

                        placemark.events.add('click', function (e) {
                            let target = e.get('target');
                            map.balloon.open(target.geometry.getCoordinates(), target.properties.get('balloonContentBody'));
                        });

                        driverPlacemarks[driver.user_id] = placemark;
                        map.geoObjects.add(placemark);
                    }
                });

                for (const id in driverPlacemarks) {
                    if (!existingIds.has(parseInt(id))) {
                        map.geoObjects.remove(driverPlacemarks[id]);
                        delete driverPlacemarks[id];
                    }
                }

                positionViewport(drivers);
            }
        } catch (e) {
            console.error('Ошибка при загрузке водителей:', e);
        }
    }
});

</script>


@endsection
