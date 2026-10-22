						<a href="{{route('order.create')}}" class="btn btn-primary" style="margin-bottom:15px;">
							<svg xmlns="http://www.w3.org/2000/svg" class="w-inner-icn text-white" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path d="M16,11.5h-3.5V8c0-0.276123-0.223877-0.5-0.5-0.5S11.5,7.723877,11.5,8v3.5H8c-0.276123,0-0.5,0.223877-0.5,0.5s0.223877,0.5,0.5,0.5h3.5v3.5005493C11.5001831,16.2765503,11.723999,16.5001831,12,16.5h0.0006104C12.2765503,16.4998169,12.5001831,16.276001,12.5,16v-3.5H16c0.276123,0,0.5-0.223877,0.5-0.5S16.276123,11.5,16,11.5z M12,2C6.4771729,2,2,6.4771729,2,12s4.4771729,10,10,10c5.5202026-0.0062866,9.9937134-4.4797974,10-10C22,6.4771729,17.5228271,2,12,2z M12,21c-4.9705811,0-9-4.0294189-9-9s4.0294189-9,9-9c4.9682617,0.0056152,8.9943848,4.0317383,9,9C21,16.9705811,16.9705811,21,12,21z"/></svg>
							Добавить заказ
						</a>
						<div class="table-responsive">
							<table class="table table-bordered text-nowrap border-bottom">
								<thead>
									<tr>
										<th class="wd-20p border-bottom-0">Статус заказа</th>
										<th class="wd-15p border-bottom-0">Id</th>
										<th class="wd-15p border-bottom-0">Фамилия клиента</th>
										<th class="wd-15p border-bottom-0">Имя клиента</th>
										<th class="wd-15p border-bottom-0">Телефон клиента</th>
										<th class="wd-15p border-bottom-0">Водитель</th>
										<th class="wd-20p border-bottom-0">Адрес нахождения клиента</th>
										<th class="wd-20p border-bottom-0">Адрес куда клиента</th>
										<th class="wd-20p border-bottom-0">Дистанция</th>
										<th class="wd-20p border-bottom-0">Время в пути</th>
										<th class="wd-20p border-bottom-0">Цена</th>
										<th class="wd-15p border-bottom-0">Класс</th>
										<th class="wd-15p border-bottom-0">Действие</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($orders as $order)
										<tr data-slug="{{$order->id}}">
												<!-- @if($order->status=="assigned")
													<td style="background:blue; color:white;">Водитель назначен</td>
												@elseif($order->status=="completed")
													<td style="background:green; color:white;">Завершено</td>
												@elseif($order->status=="canceled")
													<td style="background:red; color:white;">Отменено</td>
																										<td style="background:orange; color:white;">Ожидание</td>
												@endif

												@else -->

												<!-- Новые статусы RAY -->
												@if($order->status=="assigned")
													<td style="background:teal; color:white;">Водитель в пути</td>
												@elseif($order->status=="completed")
													<td style="background:green; color:white;">Завершено</td>
												@elseif($order->status=="canceled")
													<td style="background:red; color:white;">Отменено</td>
												@elseif($order->status=="pending")
													<td style="background:orange; color:white;">Ожидание</td>
												@elseif($order->status=="searching")
													<td style="background:blue; color:white;">Ищем водителя</td>
												@elseif($order->status=="searching_other")
													<td style="background:#e65100; color:white;">Ищем другого водителя</td>
												@elseif($order->status=="on_place")
													<td style="background:purple; color:white;">Водитель на месте</td>
												@elseif($order->status=="in_progress")
													<td style="background:navy; color:white;">В дороге</td>	
												@else
													<td style="background:orange; color:white;">Ожидание</td>
												@endif
											<td># {{$order->id}}</td>
											<td>{{ $order->client_first_name }}</td>
											<td>{{ $order->client_last_name }}</td>
											<td>{{ $order->client_phone }}</td>
											<td>{{ $order->driver ? $order->driver->first_name.' '.$order->driver->last_name : "Не назначен" }}</td>
											<td>{{ $order->from_address }}</td>
											<td>{{ $order->to_address }}</td>
											<td>{{ $order->distance_km !== null ? number_format($order->distance_km, 2, ',', ' ') . ' км' : '—' }}</td>
											<td>{{ $order->duration_min !== null ? $order->duration_min . ' мин' : '—' }}</td>
											<td>{{ $order->price }}</td>
											<td>{{ $order->tariff ? ($order->tariffClass?->label ?? $order->tariff) : '' }}</td>
											<td>
												 <!-- раскоментить -->
												@if($order->status=="pending")<a href="{{route('order.edit',['order'=>$order->id])}}" class="btn btn-warning"><i class="fa fa-pencil"></i></a>@endif
												<button class="btn btn-danger" data-slug="{{$order->id}}" data-name="{{$order->id}}" id="deleteBtn"><i class="fe fe-trash"></i></button>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
							
						</div>
						<div class="mt-5">{{$orders->links()}}</div>