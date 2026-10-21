<div class="card mb-4">
    <div class="card-header">
        <h2>Страница «Как удалить приложение»</h2>
    </div>
    <div class="card-body">
        <p class="text-muted">Шаги показываются на публичной странице удаления аккаунта в указанном порядке: сначала по полю «Порядок», затем по добавлению.</p>

        @forelse($steps as $step)
            <div class="border rounded p-3 mb-3">
                <form action="{{ route('settings.steps.update', $step) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-3">
                            @if($step->image_path)
                                <img src="{{ asset('storage/' . $step->image_path) }}" class="img-fluid mb-2" alt="Иллюстрация шага">
                            @endif
                            <label class="form-label fw-bold">Картинка шага</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Текст шага</label>
                            <textarea class="form-control" name="text" rows="4" required>{{ $step->text }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Порядок</label>
                            <input type="number" class="form-control mb-2" name="sort_order" value="{{ $step->sort_order }}" min="0">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Сохранить шаг</button>
                        </div>
                    </div>
                </form>
                <form action="{{ route('settings.steps.destroy', $step) }}" method="POST" class="mt-2"
                      onsubmit="return confirm('Удалить этот шаг?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Удалить шаг</button>
                </form>
            </div>
        @empty
            <p class="text-muted">Шаги пока не добавлены.</p>
        @endforelse

        <h3 class="h5 mt-4">Добавить шаг</h3>
        <form action="{{ route('settings.steps.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="step_image" class="form-label fw-bold">Картинка шага</label>
                <input type="file" class="form-control" id="step_image" name="image" accept="image/*">
            </div>
            <div class="mb-3">
                <label for="step_text" class="form-label fw-bold">Текст шага</label>
                <textarea class="form-control" id="step_text" name="text" rows="3" required
                          placeholder="Например: Откройте приложение и перейдите в настройки профиля"></textarea>
            </div>
            <div class="mb-3">
                <label for="step_sort_order" class="form-label fw-bold">Порядок</label>
                <input type="number" class="form-control" id="step_sort_order" name="sort_order" value="0" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Добавить шаг</button>
        </form>
    </div>
</div>
