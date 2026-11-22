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
                                            $period = $statusPeriods->get($driver->id);
                                        @endphp
                                        <tr data-driver-row="{{ $driver->id }}">
                                            <td>{{ trim($driver->first_name . ' ' . $driver->last_name) }}</td>
                                            <td>{{ $driver->phone }}</td>
                                            <td>
                                                @if($driver->currentCar)
                                                    {{ trim($driver->currentCar->brand . ' ' . $driver->currentCar->model) }}<br>
                                                    <span class="text-muted">{{ $driver->currentCar->number_plate }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($status === \App\Models\DriverLocation::STATUS_ONLINE && $location?->is_zakaz)
                                                    <span data-driver-status-badge class="badge bg-success">На линии · на заказе</span>
                                                @elseif($status === \App\Models\DriverLocation::STATUS_ONLINE)
                                                    <span data-driver-status-badge class="badge bg-success">На линии</span>
                                                @elseif($status === \App\Models\DriverLocation::STATUS_OFFLINE)
                                                    <span data-driver-status-badge class="badge bg-secondary">Не на линии</span>
                                                @else
                                                    <span data-driver-status-badge class="badge bg-dark">Не работает</span>
                                                @endif
                                                <br>
                                                <small class="text-muted" data-driver-status-since@if($period) data-status-since-at="{{ $period->started_at->toIso8601String() }}"@endif>@if($period)с {{ $period->started_at->format('H:i') }} · {{ $period->durationLabel() }}@endif</small>
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

@section('scripts')
<script>
/* Живой статус водителей (пакет №162): сигнал driver.status из
   admin-alerts.js (window.onDriverStatusChanged) обновляет бейдж
   и начало периода в строке водителя без перезагрузки; раз в
   минуту длительности пересчитываются из data-status-since-at. */
(function () {
    "use strict";

    function pad2(n) {
        return (n < 10 ? "0" : "") + n;
    }

    function formatDuration(sinceMs) {
        var mins = Math.max(0, Math.floor((Date.now() - sinceMs) / 60000));
        if (mins < 60) {
            return mins + " мин";
        }
        return Math.floor(mins / 60) + " ч " + pad2(mins % 60) + " мин";
    }

    function renderSince(el) {
        var iso = el.getAttribute("data-status-since-at");
        var t = iso ? Date.parse(iso) : NaN;
        if (isNaN(t)) {
            el.textContent = "";
            return;
        }
        var d = new Date(t);
        el.textContent = "с " + pad2(d.getHours()) + ":" + pad2(d.getMinutes())
            + " · " + formatDuration(t);
    }

    function refreshDurations() {
        document.querySelectorAll("[data-driver-status-since]").forEach(renderSince);
    }

    function applyStatus(data) {
        if (!data || data.user_id == null) {
            return;
        }
        var row = document.querySelector('[data-driver-row="' + data.user_id + '"]');
        if (!row) {
            return;
        }
        var badge = row.querySelector("[data-driver-status-badge]");
        if (badge) {
            var cls = "badge bg-dark";
            var text = data.work_status_label || "";
            if (data.work_status === "online") {
                cls = "badge bg-success";
                if (data.is_zakaz) {
                    text += " · на заказе";
                }
            } else if (data.work_status === "offline") {
                cls = "badge bg-secondary";
            }
            badge.className = cls;
            badge.textContent = text;
        }
        var since = row.querySelector("[data-driver-status-since]");
        if (since) {
            if (data.status_since) {
                since.setAttribute("data-status-since-at", data.status_since);
            } else {
                since.removeAttribute("data-status-since-at");
            }
            renderSince(since);
        }
    }

    if (typeof window.onDriverStatusChanged === "function") {
        window.onDriverStatusChanged(applyStatus);
    }
    refreshDurations();
    setInterval(refreshDurations, 60000);
})();
</script>
@endsection
