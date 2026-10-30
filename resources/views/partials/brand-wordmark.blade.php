@php
    // Вордмарк текстом, как на mediakit.by: название из «Настроек» (company_name),
    // последнее слово - в акцентной плашке; у одного слова в плашке последние 3 буквы.
    // Один источник для сайдбара админки и экрана входа - правки только здесь.
    $brandName = trim((string) (\App\Models\Setting::get('company_name') ?: config('app.name', 'Taxi')));
    $brandName = mb_strtoupper($brandName, 'UTF-8');
    $brandWords = preg_split('/\s+/u', $brandName) ?: [];
    if (count($brandWords) > 1) {
        $brandTail = array_pop($brandWords);
        $brandHead = implode(' ', $brandWords);
    } elseif (mb_strlen($brandName, 'UTF-8') > 3) {
        $brandHead = mb_substr($brandName, 0, -3, 'UTF-8');
        $brandTail = mb_substr($brandName, -3, null, 'UTF-8');
    } else {
        $brandHead = '';
        $brandTail = $brandName;
    }
@endphp
@if($brandHead !== '')<span class="brand-wordmark__head">{{ $brandHead }}</span>@endif<span class="brand-wordmark__box">{{ $brandTail }}</span>
