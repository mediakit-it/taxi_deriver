{{-- Раздел «Функционал»: рендерится и сохраняется только для суперадмина --}}
<div class="card mb-4">
    <div class="card-header">
        <h2>Функционал</h2>
    </div>
    <div class="card-body">
        <p class="text-muted">Переключатели поведения коробки. Видит и меняет только суперадмин нашей компании.
            В текущей версии переключатели сохраняются и передаются приложениям; ручные режимы - это текущее
            поведение системы, автоматика (авторасчёт цены, автоназначение водителя) появится в следующей фазе.</p>

        <form action="{{ route('settings.functional.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="functional_price_mode" class="form-label fw-bold">Расчёт цены</label>
                <select class="form-control" id="functional_price_mode" name="functional_price_mode">
                    <option value="manual" @selected(old('functional_price_mode', $functional['price_mode']) === 'manual')>Вручную диспетчером</option>
                    <option value="auto" @selected(old('functional_price_mode', $functional['price_mode']) === 'auto')>Автоматически по тарифам</option>
                    <option value="combined" @selected(old('functional_price_mode', $functional['price_mode']) === 'combined')>Комбинированно (авто с ручной правкой)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="functional_assignment_mode" class="form-label fw-bold">Назначение водителя</label>
                <select class="form-control" id="functional_assignment_mode" name="functional_assignment_mode">
                    <option value="manual" @selected(old('functional_assignment_mode', $functional['assignment_mode']) === 'manual')>Вручную диспетчером</option>
                    <option value="auto" @selected(old('functional_assignment_mode', $functional['assignment_mode']) === 'auto')>Автоматически ближайшему</option>
                    <option value="combined" @selected(old('functional_assignment_mode', $functional['assignment_mode']) === 'combined')>Комбинированно (авто, при отказе или молчании - диспетчеру)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="functional_order_sources" class="form-label fw-bold">Разрешённые источники заказов</label>
                <select class="form-control" id="functional_order_sources" name="functional_order_sources">
                    <option value="phone" @selected(old('functional_order_sources', $functional['order_sources']) === 'phone')>Только телефон</option>
                    <option value="app" @selected(old('functional_order_sources', $functional['order_sources']) === 'app')>Только приложение</option>
                    <option value="both" @selected(old('functional_order_sources', $functional['order_sources']) === 'both')>Телефон и приложение</option>
                </select>
                <small class="text-muted">Ограничивает выбор источника при создании заказа в админке.</small>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="functional_preorders_enabled"
                       name="functional_preorders_enabled" value="1"
                       @checked(old('functional_preorders_enabled', $functional['preorders_enabled']))>
                <label class="form-check-label fw-bold" for="functional_preorders_enabled">Предзаказы</label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="functional_ratings_enabled"
                       name="functional_ratings_enabled" value="1"
                       @checked(old('functional_ratings_enabled', $functional['ratings_enabled']))>
                <label class="form-check-label fw-bold" for="functional_ratings_enabled">Оценки и жалобы клиентов</label>
            </div>

            <button type="submit" class="btn btn-primary">Сохранить функционал</button>
        </form>
    </div>
</div>
