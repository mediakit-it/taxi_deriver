<div class="card mb-4">
    <div class="card-header">
        <h2>Документы и политики</h2>
    </div>
    <div class="card-body">
        @if($documents->isEmpty())
            <p class="text-muted">Документы пока не добавлены.</p>
        @else
            <ul class="list-group mb-4">
                @foreach($documents as $document)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank">{{ $document->title }}</a>
                        <form action="{{ route('settings.documents.destroy', $document) }}" method="POST"
                              onsubmit="return confirm('Удалить документ «{{ $document->title }}»?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        <form action="{{ route('settings.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="document_title" class="form-label fw-bold">Название документа</label>
                <input type="text" class="form-control" id="document_title" name="title" required
                       placeholder="Например: Политика конфиденциальности">
            </div>
            <div class="mb-3">
                <label for="document_file" class="form-label fw-bold">Файл PDF</label>
                <input type="file" class="form-control" id="document_file" name="file" accept="application/pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">Добавить документ</button>
        </form>
    </div>
</div>
