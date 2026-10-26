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

<!-- <script src="//api-maps.yandex.ru/2.1/?apikey=ad38720a-e8c9-443c-b454-1f4d2499ad78&suggest_apikey=871a8f7b-85ec-4bc7-b5cb-f336458187b6&lang=ru_RU&load=SuggestView&onload=onLoad"></script> -->
<script src="https://api-maps.yandex.ru/2.1/?apikey=ad38720a-e8c9-443c-b454-1f4d2499ad78&suggest_apikey=871a8f7b-85ec-4bc7-b5cb-f336458187b6&lang=ru_RU&load=SuggestView,geocode,route,Map,Placemark"></script>


<script>
	// $(document).ready(function () {
	// 	console.log('document ready');

	// 	let map; // объявляем в родительской области видимости
	// 	let driverPlacemarks = {}; // тоже здесь

	// 	ymaps.ready(init);

	// 	function init() {
	// 		console.log('ymaps initialized');
	// 		map = new ymaps.Map("map", {
	// 			center: [54.19, 28.48], // Центр — Минск
	// 			zoom: 12,
	// 		});

	// 		fetchAndUpdateDrivers();
	// 		setInterval(fetchAndUpdateDrivers, 10000); // обновлять каждые 10 секунд
	// 	}

	// 	async function fetchAndUpdateDrivers() {
	// 		try {
	// 			const response = await fetch('/admin/data/driver-locations', {
	// 				headers: {
	// 					'Accept': 'application/json'
	// 				}
	// 			});
	// 			const result = await response.json();
				
	// 			if (result.status) {
	// 				const drivers = result.data;

	// 				// Помечаем на карте каждого водителя
	// 				const existingIds = new Set();

	// 				drivers.forEach(driver => {
	// 					existingIds.add(driver.user_id);

	// 					const coords = [driver.lat, driver.lng];
	// 					const balloonContent = `
	// 						<b>Водитель ID: ${driver.user_id}</b><br>
	// 						Статус: ${driver.is_zakaz ? 'Занят' : 'Свободен'}<br>
	// 						Заказ: ${driver.order_id ? driver.order_id : 'Нет'}
	// 					`;

	// 					if (driverPlacemarks[driver.user_id]) {
	// 						// Обновляем позицию и балун
	// 						driverPlacemarks[driver.user_id].geometry.setCoordinates(coords);
	// 						driverPlacemarks[driver.user_id].properties.set('balloonContent', balloonContent);
	// 					} else {
	// 						// Создаем новый маркер
	// 						driverPlacemarks[driver.user_id] = new ymaps.Placemark(coords, {
	// 							balloonContent: balloonContent,
	// 						}, {
	// 							preset: driver.is_zakaz ? 'islands#redIcon' : 'islands#greenIcon'
	// 						});

	// 						map.geoObjects.add(driverPlacemarks[driver.user_id]);
	// 					}
	// 				});

	// 				// Удаляем маркеры водителей, которых больше нет в ответе
	// 				for (const id in driverPlacemarks) {
	// 					if (!existingIds.has(parseInt(id))) {
	// 						map.geoObjects.remove(driverPlacemarks[id]);
	// 						delete driverPlacemarks[id];
	// 					}
	// 				}
	// 			}
	// 		} catch (e) {
	// 			console.error('Ошибка при загрузке водителей:', e);
	// 		}
	// 	}
	// });

	$(document).ready(function () {
    let map;
    let driverPlacemarks = {};

    ymaps.ready(init);

    function init() {
        map = new ymaps.Map("map", {
            center: [54.19, 28.48],
            zoom: 12,
        });

        fetchAndUpdateDrivers();
        setInterval(fetchAndUpdateDrivers, 10000);
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
                    const balloonContent = `
                        <b>Водитель ID:${driver.user_id}. ФИО: ${driver.user.last_name} ${driver.user.first_name}</b><br>
                        Статус: ${driver.is_zakaz ? 'Занят' : 'Свободен'}<br>
                        Заказ: ${driver.order_id ? '# '+driver.order_id : 'Нет'}
                    `;

                    if (driverPlacemarks[driver.user_id]) {
                        driverPlacemarks[driver.user_id].geometry.setCoordinates(coords);
                        driverPlacemarks[driver.user_id].properties.set({
                            balloonContentBody: balloonContent,
                            hintContent: `Водитель ID: ${driver.user_id}`
                        });
                    } else {
                        const colorPreset = getColorByUserId(driver.user_id);
                        const placemark = new ymaps.Placemark(coords, {
                            balloonContentBody: balloonContent,
                            hintContent: `Водитель ID: ${driver.user_id}`
                        }, {
                            preset: colorPreset,
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
            }
        } catch (e) {
            console.error('Ошибка при загрузке водителей:', e);
        }
    }
});

</script>


@endsection
