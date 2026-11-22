@extends('admin.master')

@section('content')
<div class="side-app">
	<!-- CONTAINER -->
	<div class="main-container container-fluid">

		<!-- PAGE-HEADER -->
		<div class="page-header">
			<div>
				<h1 class="page-title">Заказы</h1>
			</div>
			<div class="ms-auto pageheader-btn">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Главная</a></li>
					<li class="breadcrumb-item active" aria-current="page">Заказы</li>
				</ol>
			</div>
		</div>
		<!-- PAGE-HEADER END -->

		<!-- Row -->
		<div class="row row-sm">
			<div class="col-lg-12">
				<div class="card ">
					<div class="card-header border-bottom">
						<h3 class="card-title">Список заказов</h3>
					</div>
					<div class="card-body">
						<a href="{{route('order.create')}}" class="btn btn-primary" style="margin-bottom:15px;">
							<svg xmlns="http://www.w3.org/2000/svg" class="w-inner-icn text-white" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path d="M16,11.5h-3.5V8c0-0.276123-0.223877-0.5-0.5-0.5S11.5,7.723877,11.5,8v3.5H8c-0.276123,0-0.5,0.223877,0.5,0.5s0.223877,0.5,0.5,0.5h3.5v3.5005493C11.5001831,16.2765503,11.723999,16.5001831,12,16.5h0.0006104C12.2765503,16.4998169,12.5001831,16.276001,12.5,16v-3.5H16c0.276123,0,0.5-0.223877,0.5-0.5S16.276123,11.5,16,11.5z M12,2C6.4771729,2,2,6.4771729,2,12s4.4771729,10,10,10c5.5202026-0.0062866,9.9937134-4.4797974,10-10C22,6.4771729,17.5228271,2,12,2z M12,21c-4.9705811,0-9-4.0294189-9-9s4.0294189-9,9-9c4.9682617,0.0056152,8.9943848,4.0317383,9,9C21,16.9705811,16.9705811,21,12,21z"/></svg>
							Добавить заказ
						</a>

						{{-- Фильтры списка: обычный GET, выбранные значения
						     живут в URL - живое обновление фрагмента читает
						     параметры оттуда же и фильтры не теряет. --}}
						<form method="GET" action="{{route('order.index')}}" class="row g-2 align-items-end mb-3" id="orders-filter-form">
							<div class="col-md-2">
								<label class="form-label" for="filter-status">Статус</label>
								<select name="status" id="filter-status" class="form-control">
									<option value="">Все статусы</option>
									@foreach($statusLabels as $statusValue => $statusLabel)
										<option value="{{$statusValue}}" @selected($filters['status'] === $statusValue)>{{$statusLabel}}</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label" for="filter-driver">Водитель</label>
								<select name="driver_id" id="filter-driver" class="form-control">
									<option value="">Все водители</option>
									<option value="none" @selected($filters['driver_id'] === 'none')>Без водителя</option>
									@foreach($drivers as $driver)
										<option value="{{$driver->id}}" @selected($filters['driver_id'] === $driver->id)>{{ $driver->first_name }} {{ $driver->last_name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label" for="filter-date">Дата</label>
								<input type="date" name="date" id="filter-date" class="form-control" value="{{$filters['date']}}">
							</div>
							<div class="col-md-3">
								<label class="form-label" for="filter-search">Поиск</label>
								<input type="text" name="search" id="filter-search" class="form-control" value="{{$filters['search']}}" placeholder="Номер, клиент, телефон, адрес">
							</div>
							<div class="col-md-2">
								<label class="form-label" for="filter-sort">Сортировка</label>
								<select name="sort" id="filter-sort" class="form-control">
									<option value="new" @selected($filters['sort'] === 'new')>Сначала новые</option>
									<option value="old" @selected($filters['sort'] === 'old')>Сначала старые</option>
								</select>
							</div>
							<div class="col-md-1">
								<button type="submit" class="btn btn-primary w-100">Показать</button>
							</div>
							<div class="col-md-12">
								<a href="{{route('order.index')}}">Сбросить фильтры</a>
							</div>
						</form>

						{{-- Живая зона: плашки + таблица. Её содержимое
						     подменяет admin-orders-live.js по сигналу
						     Pusher и по таймеру, без перезагрузки. --}}
						<div id="orders-live">
							@include('admin.order.fragment')
						</div>
					</div>

				</div>
			</div>
		</div>
		<!-- End Row -->

		<div class="modal fade"  id="modaldemo1">

		</div>
	</div>
</div>
@endsection


@section('scripts')

	<!-- DATA TABLE JS-->
	<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/buttons.bootstrap5.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/jszip.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/pdfmake/pdfmake.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/pdfmake/vfs_fonts.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/buttons.html5.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/buttons.print.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/buttons.colVis.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/dataTables.responsive.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/responsive.bootstrap5.min.js')}}"></script>
	<script src="{{asset('assets/js/table-data.js')}}"></script>

	<!-- NOTIFICATION -->

		<script src="{{asset('assets/plugins/notify/js/rainbow.js')}}"></script>
		<script src="{{asset('assets/plugins/notify/js/sample.js')}}"></script>
		<!-- <script src="{{asset('assets/plugins/notify/js/jquery.growl.js')}}"></script> -->
		<script src="{{asset('assets/plugins/notify/js/notifIt.js')}}"></script>

	<script>
		$(document).ready(function(){

			$(document).on('click','#deleteBtn',function(e){
				e.preventDefault();
				var slug = $(this).data('slug');
				var name = $(this).data('name');
				var html = `<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content modal-content-demo">
					<div class="modal-header">
						<h6 class="modal-title">Удаление</h6><button aria-label="Close" class="btn-close" data-bs-dismiss="modal" ><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<h6>Вы уверены, что хотите удалить заказ <strong>${name}</strong></h6>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary" id="delete" data-slug="${slug}">Удалить</button> <button class="btn btn-light" data-bs-dismiss="modal" >Отмена</button>
					</div>
				</div>
			</div>`;


				$('#modaldemo1').html('').append(html);
				$('#modaldemo1').modal('show');

			});

			$(document).on('click','#delete',function(e){
				e.preventDefault();
				var slug = $(this).data('slug');

				$.ajax({
					type: 'DELETE',
					url: "/admin/order/"+slug,
					headers: {
						'X-CSRF-TOKEN': "{{ csrf_token() }}"
					},
					success: function (data) {
						if(data == 'true'){
							$("tr[data-slug=" + slug + "]").remove();
							$('#modaldemo1').modal('hide');
							window.adminToast("Заказ был успешно удален", "", "success");
						}else{
							$('#modaldemo1').modal('hide');
						}
					},
					error: function (error) {
						console.log(error);
					}
				});
        	});
		});
	</script>

	{{-- Живой список заказов: адреса быстрых действий и CSRF для
	     admin-orders-live.js. Отдельного Pusher здесь больше нет -
	     единственная точка Pusher админки живёт в admin-alerts.js
	     (канал admin.alerts, событие orders.changed). --}}
	<script>
		window.adminOrdersLiveConfig = {
			indexUrl: "{{route('order.index')}}",
			assignUrlTemplate: "{{route('order.assign-driver', ['order' => '__ORDER__'])}}",
			priceUrlTemplate: "{{route('order.update-price', ['order' => '__ORDER__'])}}",
			csrf: "{{ csrf_token() }}"
		};
	</script>
	<script src="{{asset('assets/js/admin-orders-live.js')}}?v={{ file_exists(public_path('assets/js/admin-orders-live.js')) ? filemtime(public_path('assets/js/admin-orders-live.js')) : 1 }}"></script>

@endsection
