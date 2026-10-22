@extends('admin.master')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Редактирование цен на поездку</h2>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('cost-of-trip.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="display_name_city" class="form-label fw-bold">Название класса «В городе»</label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="display_name_city"
                               name="display_name_city"
                               value="{{ $city->display_name ?? '' }}"
                               placeholder="Например: По городу">
                        <small class="text-muted">Показывается диспетчеру и на сайте. Пустое - подставится City.</small>
                    </div>

                    <div class="mb-4">
                        <label for="price_city" class="form-label fw-bold">В городе</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               class="form-control form-control-lg"
                               id="price_city"
                               name="price_city"
                               value="{{ $city->price ?? '' }}"
                               placeholder="Например: 8.00">
                        <small class="text-muted">Текущая цена: {{ $city->price ?? 'не указана' }}</small>
                    </div>

                    <div class="mb-4">
                        <label for="display_name_outside" class="form-label fw-bold">Название класса «За городом»</label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="display_name_outside"
                               name="display_name_outside"
                               value="{{ $outsideCity->display_name ?? '' }}"
                               placeholder="Например: За городом">
                        <small class="text-muted">Показывается диспетчеру и на сайте. Пустое - подставится Outside_the_City.</small>
                    </div>

                    <div class="mb-4">
                        <label for="price_outside" class="form-label fw-bold">За городом</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               class="form-control form-control-lg"
                               id="price_outside"
                               name="price_outside"
                               value="{{ $outsideCity->price ?? '' }}"
                               placeholder="Например: 1.70">
                        <small class="text-muted">Текущая цена: {{ $outsideCity->price ?? 'не указана' }}</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        Сохранить изменения
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
