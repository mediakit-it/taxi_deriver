@extends('admin.master')

@section('content')
<div class="side-app">
    <!-- CONTAINER -->
    <div class="main-container container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Дашборд</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Дашборд</li>
                </ol>
            </div>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- СЧЁТЧИКИ -->
        <div class="row" style="margin-bottom: 22px;">
            <div class="col-12 col-md-6 col-xl">
                <div class="card overflow-hidden admin-stat admin-stat--blue">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $stats['orders_today'] }}</h3>
                                <p class="text-muted fs-13 mb-0">Заказы сегодня</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-primary dash ms-auto box-shadow-primary">
                                    <i class="fa fa-shopping-cart text-white" style="font-size:22px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="card overflow-hidden admin-stat admin-stat--green">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $stats['completed_today'] }}</h3>
                                <p class="text-muted fs-13 mb-0">Выполнено сегодня</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-success dash ms-auto box-shadow-success">
                                    <i class="fa fa-check text-white" style="font-size:22px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="card overflow-hidden admin-stat admin-stat--peach">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ number_format($stats['revenue_today'], 2, ',', ' ') }} {{ $currency }}</h3>
                                <p class="text-muted fs-13 mb-0">Выручка сегодня</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-warning dash ms-auto box-shadow-warning">
                                    <i class="fa fa-money text-white" style="font-size:22px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="card overflow-hidden admin-stat admin-stat--pink">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $stats['drivers_online'] }}</h3>
                                <p class="text-muted fs-13 mb-0">Водители на линии</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-info dash ms-auto box-shadow-info">
                                    <i class="fa fa-car text-white" style="font-size:22px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl">
                <div class="card overflow-hidden admin-stat admin-stat--violet">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $stats['active_orders'] }}</h3>
                                <p class="text-muted fs-13 mb-0">Активные заказы сейчас</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-danger dash ms-auto box-shadow-danger">
                                    <i class="fa fa-clock-o text-white" style="font-size:22px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- СЧЁТЧИКИ END -->

        <div class="row">
            <!-- ЗАКАЗЫ ПО СТАТУСАМ -->
            <div class="col-xl-4 col-md-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">Заказы сегодня по статусам</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered border-bottom">
                                <tbody>
                                    @foreach($ordersByStatus as $row)
                                        <tr>
                                            <td>{{ $row['label'] }}</td>
                                            <td class="text-end"><span class="badge bg-primary">{{ $row['total'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ЗАКАЗЫ ПО СТАТУСАМ END -->

            <!-- ПОСЛЕДНИЕ ЗАКАЗЫ -->
            <div class="col-xl-8 col-md-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">Последние заказы</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $statusColors = [
                                'pending' => 'orange',
                                'searching' => 'blue',
                                'searching_other' => '#e65100',
                                'assigned' => 'teal',
                                'on_place' => 'purple',
                                'in_progress' => 'navy',
                                'completed' => 'green',
                                'canceled' => 'red',
                            ];
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0">№</th>
                                        <th class="border-bottom-0">Клиент</th>
                                        <th class="border-bottom-0">Маршрут</th>
                                        <th class="border-bottom-0">Статус</th>
                                        <th class="border-bottom-0">Цена</th>
                                        <th class="border-bottom-0">Создан</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestOrders as $order)
                                        <tr>
                                            <td># {{ $order->id }}</td>
                                            <td>{{ trim($order->client_first_name . ' ' . $order->client_last_name) }}</td>
                                            <td>{{ $order->from_address }} &rarr; {{ $order->to_address }}</td>
                                            <td><span class="badge" style="background:{{ $statusColors[$order->status] ?? 'orange' }}; color:white;">{{ $order->status_label }}</span></td>
                                            <td>{{ $order->price !== null ? number_format((float) $order->price, 2, ',', ' ') . ' ' . $currency : '—' }}</td>
                                            <td>{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Заказов пока нет</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ПОСЛЕДНИЕ ЗАКАЗЫ END -->
        </div>

    </div>
</div>
@endsection
