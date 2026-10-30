{{-- Плашки-сводка по активным статусам: счётчики глобальные (не
     сужены фильтрами), состав и числа рисует AdminOrdersQuery.
     Завершённых и отменённых здесь нет. Клик по плашке ставит
     фильтр по статусу (обрабатывает admin-orders-live.js). --}}
<div class="orders-status-chips mb-3">
    @foreach($statusCounts as $status => $count)
        <button type="button"
                class="btn btn-sm {{ ($filters['status'] ?? null) === $status ? 'btn-primary' : 'btn-outline-primary' }} me-1 mb-1"
                data-status-chip="{{ $status }}">
            {{ $statusLabels[$status] }}
            <span class="badge {{ ($filters['status'] ?? null) === $status ? 'bg-light text-dark' : 'bg-primary' }}">{{ $count }}</span>
        </button>
    @endforeach
</div>
