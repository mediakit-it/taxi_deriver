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
				<div class="card">
					<div class="card-header border-bottom">
						<h3 class="card-title">Список заказов</h3>
					</div>
					<div class="card-body">
						<!-- <a href="{{route('ordersBasket.create')}}" class="btn btn-primary" style="margin-bottom:15px;">
							<svg xmlns="http://www.w3.org/2000/svg" class="w-inner-icn text-white" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path d="M16,11.5h-3.5V8c0-0.276123-0.223877-0.5-0.5-0.5S11.5,7.723877,11.5,8v3.5H8c-0.276123,0-0.5,0.223877-0.5,0.5s0.223877,0.5,0.5,0.5h3.5v3.5005493C11.5001831,16.2765503,11.723999,16.5001831,12,16.5h0.0006104C12.2765503,16.4998169,12.5001831,16.276001,12.5,16v-3.5H16c0.276123,0,0.5-0.223877,0.5-0.5S16.276123,11.5,16,11.5z M12,2C6.4771729,2,2,6.4771729,2,12s4.4771729,10,10,10c5.5202026-0.0062866,9.9937134-4.4797974,10-10C22,6.4771729,17.5228271,2,12,2z M12,21c-4.9705811,0-9-4.0294189-9-9s4.0294189-9,9-9c4.9682617,0.0056152,8.9943848,4.0317383,9,9C21,16.9705811,16.9705811,21,12,21z"/></svg>
							Добавить заказ
						</a> -->
						<div class="table-responsive">
							<table class="table table-bordered text-nowrap border-bottom" data-order='[[ 12, "desc" ]]' id="basic-datatable">
								<thead>
									<tr>
										<th class="wd-15p border-bottom-0">Id</th>
										<th class="wd-15p border-bottom-0">Фамилия</th>
										<th class="wd-15p border-bottom-0">Имя</th>
										<!-- <th class="wd-15p border-bottom-0">email</th> -->
										<th class="wd-15p border-bottom-0">Телефон</th>
										<th class="wd-15p border-bottom-0">Борт</th>
										<th class="wd-15p border-bottom-0">Стоимость доставки</th>
										<!-- <th class="wd-15p border-bottom-0">Скидка</th> -->
										<!-- <th class="wd-15p border-bottom-0">Скидка (% / кз,р)</th> -->
										<!-- <th class="wd-15p border-bottom-0">Стоимость доставки</th> -->
										<th class="wd-15p border-bottom-0">Тип доставки</th>
										<!-- <th class="wd-15p border-bottom-0">Страна заказа</th> -->
										<th class="wd-15p border-bottom-0">Заказ обработан?</th>

										<th class="wd-15p border-bottom-0">Действие</th>
										<th style="display:none;" class="wd-15p border-bottom-0">дата</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($baskets as $basket)
                                        @php
                                        $user = \App\Models\User::query()->where('email', '=', $basket->email)->first();
                                        $discount = '';
                                        if ($user){
                                            if ($user->country == 'Беларусь'){
                                                $discount = $user->discountUser . ' % ';
                                            }
                                            else{
                                                $discount = $user->discountMoney;
                                            }
                                        }
                                        @endphp
										<tr data-slug="{{$basket->id}}">
											<td># {{$basket->id}}</td>
											<td>{{$basket->first_name}}</td>
											<td>{{$basket->name}}</td>
											<!-- <td>{{$basket->email}}</td> -->
											<td>{{$basket->phone}}</td>
											<td>Борт1</td>
											<td>{{$basket->fullPrice}}</td>
											<!-- <td>{{$basket->discount}}</td> -->
											<!-- <td>{{$discount}}</td> -->
											<!-- <td>{{$basket->deliveryPrice}}</td> -->
											<td>{{$basket->nameDeliveryRu}}</td>
											<!-- <td>{{$basket->country}}</td> -->
											<td>@if($basket->obrabotka == "obrabotka") На обработке @elseif($basket->obrabotka == "obrabotan") Обработан @elseif($basket->obrabotka == "otmena") Отменен @else Получен @endif</td>
											<td>
												<a href="{{route('ordersBasket.edit',['ordersBasket'=>$basket->id])}}" class="btn btn-warning"><i class="fa fa-pencil"></i></a>
												<button class="btn btn-danger" data-slug="{{$basket->id}}" data-name="{{'#'.$basket->id}}" id="deleteBtn"><i class="fe fe-trash"></i></button>
											</td>
											<td style="display:none;">{{$basket->created_at}}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
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
					url: "/admin/ordersBasket/"+slug,
					headers: {
						'X-CSRF-TOKEN': "{{ csrf_token() }}"
					},
					success: function (data) {
						if(data == 'true'){
							$("tr[data-slug=" + slug + "]").remove();
							$('#modaldemo1').modal('hide');
							not7("Заказ успешно удален");
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

@endsection
