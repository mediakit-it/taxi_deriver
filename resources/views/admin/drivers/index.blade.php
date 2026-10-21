@extends('admin.master')

@section('content')
<div class="side-app">
    <!-- CONTAINER -->
    <div class="main-container container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Водители</h1>
            </div>
            <div class="ms-auto pageheader-btn">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Главная</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Водители</li>
                </ol>
            </div>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- Row -->
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">Водители и их статусы</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0">Водитель</th>
                                        <th class="border-bottom-0">Телефон</th>
                                        <th class="border-bottom-0">Автомобиль</th>
                                        <th class="border-bottom-0">Статус</th>
                                        <th class="border-bottom-0">Последний сигнал</th>
                                        <th class="border-bottom-0">Текущий заказ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($drivers as $driver)
                                        @php
                                            $location = $driver->location;
                                            $status = $location?->work_status ?? \App\Models\DriverLocation::STATUS_NOT_WORKING;
                                        @endphp
                                        <tr>
                                            <td>{{ trim($driver->first_name . ' ' . $driver->last_name) }}</td>
                                            <td>{{ $driver->phone }}</td>
                                            <td>
                                                @if($driver->car)
                                                    {{ trim($driver->car->brand . ' ' . $driver->car->model) }}<br>
                                                    <span class="text-muted">{{ $driver->car->number_plate }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($status === \App\Models\DriverLocation::STATUS_ONLINE && $location?->is_zakaz)
                                                    <span class="badge bg-success">На линии · на заказе</span>
                                                @elseif($status === \App\Models\DriverLocation::STATUS_ONLINE)
                                                    <span class="badge bg-success">На линии</span>
                                                @elseif($status === \App\Models\DriverLocation::STATUS_OFFLINE)
                                                    <span class="badge bg-secondary">Не на линии</span>
                                                @else
                                                    <span class="badge bg-dark">Не работает</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($location?->updated_at)
                                                    {{ $location->updated_at->diffForHumans() }}<br>
                                                    <span class="text-muted">{{ $location->updated_at->format('d.m.Y H:i') }}</span>
                                                @else
                                                    нет данных
                                                @endif
                                            </td>
                                            <td>
                                                @if($location?->is_zakaz && $location?->order_id)
                                                    <a href="{{ route('order.edit', ['order' => $location->order_id]) }}">Заказ #{{ $location->order_id }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Водителей пока нет</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->

    </div>
</div>
@endsection
