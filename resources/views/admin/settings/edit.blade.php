@extends('admin.master')

@section('content')
    @php
        $phones = json_decode($settings['phones'] ?? '[]', true) ?: [];
        $addresses = json_decode($settings['addresses'] ?? '[]', true) ?: [];
        $timezones = ['Europe/Minsk', 'Europe/Moscow', 'Europe/Kyiv', 'Europe/Warsaw', 'Europe/Vilnius', 'Europe/Riga', 'Asia/Almaty', 'UTC'];
    @endphp

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Настройки: основное</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="company_name" class="form-label fw-bold">Название компании</label>
                        <input type="text" class="form-control" id="company_name" name="company_name"
                               value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label fw-bold">Город / зона работы</label>
                        <input type="text" class="form-control" id="city" name="city"
                               value="{{ old('city', $settings['city'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="timezone" class="form-label fw-bold">Часовой пояс</label>
                        <select class="form-control" id="timezone" name="timezone">
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $settings['timezone'] ?? 'Europe/Minsk') === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="currency_label" class="form-label fw-bold">Подпись валюты</label>
                        <input type="text" class="form-control" id="currency_label" name="currency_label"
                               value="{{ old('currency_label', $settings['currency_label'] ?? '') }}"
                               placeholder="Например: руб.">
                    </div>
                    <div class="mb-3">
                        <label for="notification_email" class="form-label fw-bold">Email для уведомлений</label>
                        <input type="email" class="form-control" id="notification_email" name="notification_email"
                               value="{{ old('notification_email', $settings['notification_email'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="driver_accept_seconds" class="form-label fw-bold">Время на принятие заказа водителем, секунд</label>
                        <input type="number" class="form-control" id="driver_accept_seconds" name="driver_accept_seconds"
                               value="{{ old('driver_accept_seconds', $settings['driver_accept_seconds'] ?? '') }}"
                               min="10" max="600">
                        <small class="text-muted">От 10 до 600 секунд. По истечении времени заказ считается непринятым.</small>
                    </div>
                    <div class="mb-3">
                        <label for="car_bind_password" class="form-label fw-bold">Пароль привязки авто</label>
                        <input type="password" class="form-control" id="car_bind_password" name="car_bind_password"
                               value="" autocomplete="new-password"
                               placeholder="{{ !empty($settings['car_bind_password']) ? 'Пароль задан' : 'Пароль не задан' }}">
                        <small class="text-muted">По этому паролю установщик водительского приложения при установке на устройство в автомобиле выбирает, к какому авто привязано устройство. Оставьте поле пустым, чтобы не менять пароль. @if(!empty($settings['car_bind_password']))Сейчас пароль задан.@elseСейчас пароль не задан - привязка недоступна.@endif</small>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Контакты</h2>
                </div>
                <div class="card-body">
                    <label class="form-label fw-bold">Телефоны</label>
                    <div id="phones-list">
                        @foreach($phones as $i => $phone)
                            <div class="row phone-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="phones[{{ $i }}][number]"
                                           value="{{ $phone['number'] ?? '' }}" placeholder="+375 29 000-00-00">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="phones[{{ $i }}][label]"
                                           value="{{ $phone['label'] ?? '' }}" placeholder="Подпись, например: диспетчерская">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-row">Удалить</button>
                                </div>
                            </div>
                        @endforeach
                        @if(empty($phones))
                            <div class="row phone-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="phones[0][number]" placeholder="+375 29 000-00-00">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="phones[0][label]" placeholder="Подпись, например: диспетчерская">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-row">Удалить</button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary mb-4" id="add-phone">Добавить телефон</button>

                    <label class="form-label fw-bold">Адреса</label>
                    <div id="addresses-list">
                        @foreach($addresses as $i => $address)
                            <div class="row address-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="addresses[{{ $i }}][address]"
                                           value="{{ $address['address'] ?? '' }}" placeholder="г. Быхов, ул. Ленина, 1">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="addresses[{{ $i }}][label]"
                                           value="{{ $address['label'] ?? '' }}" placeholder="Подпись, например: офис">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-row">Удалить</button>
                                </div>
                            </div>
                        @endforeach
                        @if(empty($addresses))
                            <div class="row address-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="addresses[0][address]" placeholder="г. Быхов, ул. Ленина, 1">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="addresses[0][label]" placeholder="Подпись, например: офис">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-row">Удалить</button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary mb-4" id="add-address">Добавить адрес</button>

                    <div class="mb-3">
                        <label for="social_telegram" class="form-label fw-bold">Telegram</label>
                        <input type="text" class="form-control" id="social_telegram" name="social_telegram"
                               value="{{ old('social_telegram', $settings['social_telegram'] ?? '') }}" placeholder="https://t.me/...">
                    </div>
                    <div class="mb-3">
                        <label for="social_viber" class="form-label fw-bold">Viber</label>
                        <input type="text" class="form-control" id="social_viber" name="social_viber"
                               value="{{ old('social_viber', $settings['social_viber'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="social_whatsapp" class="form-label fw-bold">WhatsApp</label>
                        <input type="text" class="form-control" id="social_whatsapp" name="social_whatsapp"
                               value="{{ old('social_whatsapp', $settings['social_whatsapp'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="social_instagram" class="form-label fw-bold">Instagram</label>
                        <input type="text" class="form-control" id="social_instagram" name="social_instagram"
                               value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" placeholder="https://instagram.com/...">
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Реквизиты</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="legal_name" class="form-label fw-bold">Юридическое название</label>
                        <input type="text" class="form-control" id="legal_name" name="legal_name"
                               value="{{ old('legal_name', $settings['legal_name'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="legal_unp" class="form-label fw-bold">УНП</label>
                        <input type="text" class="form-control" id="legal_unp" name="legal_unp"
                               value="{{ old('legal_unp', $settings['legal_unp'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="legal_address" class="form-label fw-bold">Юридический адрес</label>
                        <input type="text" class="form-control" id="legal_address" name="legal_address"
                               value="{{ old('legal_address', $settings['legal_address'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="legal_bank" class="form-label fw-bold">Банк</label>
                        <input type="text" class="form-control" id="legal_bank" name="legal_bank"
                               value="{{ old('legal_bank', $settings['legal_bank'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="legal_iban" class="form-label fw-bold">Расчётный счёт (IBAN)</label>
                        <input type="text" class="form-control" id="legal_iban" name="legal_iban"
                               value="{{ old('legal_iban', $settings['legal_iban'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="legal_director" class="form-label fw-bold">Руководитель</label>
                        <input type="text" class="form-control" id="legal_director" name="legal_director"
                               value="{{ old('legal_director', $settings['legal_director'] ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Оформление и SEO</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="color_primary" class="form-label fw-bold">Основной фирменный цвет</label>
                        <input type="color" class="form-control form-control-color" id="color_primary" name="color_primary"
                               value="{{ old('color_primary', !empty($settings['color_primary']) ? $settings['color_primary'] : '#000000') }}">
                    </div>
                    <div class="mb-3">
                        <label for="color_accent" class="form-label fw-bold">Акцентный цвет</label>
                        <input type="color" class="form-control form-control-color" id="color_accent" name="color_accent"
                               value="{{ old('color_accent', !empty($settings['color_accent']) ? $settings['color_accent'] : '#000000') }}">
                    </div>
                    <div class="mb-3">
                        <label for="logo" class="form-label fw-bold">Логотип</label>
                        @if(!empty($settings['logo_path']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Логотип" style="max-height: 80px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="favicon" class="form-label fw-bold">Фавикон</label>
                        @if(!empty($settings['favicon_path']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['favicon_path']) }}" alt="Фавикон" style="max-height: 32px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="favicon" name="favicon" accept=".ico,.png,image/*">
                    </div>
                    <div class="mb-3">
                        <label for="seo_title" class="form-label fw-bold">SEO: заголовок главной (title)</label>
                        <input type="text" class="form-control" id="seo_title" name="seo_title"
                               value="{{ old('seo_title', $settings['seo_title'] ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label for="seo_description" class="form-label fw-bold">SEO: описание главной (description)</label>
                        <textarea class="form-control" id="seo_description" name="seo_description" rows="3">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="seo_og_image" class="form-label fw-bold">SEO: картинка для соцсетей (OG)</label>
                        @if(!empty($settings['seo_og_image_path']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['seo_og_image_path']) }}" alt="OG-картинка" style="max-height: 120px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="seo_og_image" name="seo_og_image" accept="image/*">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg px-4 mb-4">
                Сохранить настройки
            </button>
        </form>

        @if(auth()->user()->role === 'superadmin')
            @include('admin.settings._functional')
        @endif

        @include('admin.settings._documents')

        @include('admin.settings._steps')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function setupDynamicList(listId, addButtonId, rowClass) {
                var list = document.getElementById(listId);
                var addButton = document.getElementById(addButtonId);
                if (!list || !addButton) {
                    return;
                }

                function renumber() {
                    list.querySelectorAll('.' + rowClass).forEach(function (row, index) {
                        row.querySelectorAll('input').forEach(function (input) {
                            input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
                        });
                    });
                }

                addButton.addEventListener('click', function () {
                    var rows = list.querySelectorAll('.' + rowClass);
                    var clone = rows[rows.length - 1].cloneNode(true);
                    clone.querySelectorAll('input').forEach(function (input) {
                        input.value = '';
                    });
                    list.appendChild(clone);
                    renumber();
                });

                list.addEventListener('click', function (event) {
                    if (!event.target.classList.contains('remove-row')) {
                        return;
                    }
                    var rows = list.querySelectorAll('.' + rowClass);
                    if (rows.length > 1) {
                        event.target.closest('.' + rowClass).remove();
                        renumber();
                    }
                });
            }

            setupDynamicList('phones-list', 'add-phone', 'phone-row');
            setupDynamicList('addresses-list', 'add-address', 'address-row');
        });
    </script>
@endsection
