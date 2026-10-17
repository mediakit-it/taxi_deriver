@extends('site.master')

@section('styles')
<style>
    .circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px auto;

        box-shadow: 0 0 0 rgba(159, 159, 159, 0.4);
        animation: pulse 1.5s infinite;
    }
    .circle span {
        color: #000;
        font-size: 20px;
        font-weight: 600;
    }

    @keyframes pulse {
        0% {
            -moz-box-shadow: 0 0 0 0 rgba(159, 159, 159, 0.4);
            box-shadow: 0 0 0 0 rgba(159, 159, 159, 0.4);
        }
        70% {
            -moz-box-shadow: 0 0 0 10px rgba(159, 159, 159, 0);
            box-shadow: 0 0 0 10px rgba(159, 159, 159, 0);
        }
        100% {
            -moz-box-shadow: 0 0 0 0 rgba(159, 159, 159, 0);
            box-shadow: 0 0 0 0 rgba(159, 159, 159, 0);
        }
    }
    .error-border {
        border: 1px solid red;
    }

    .error-text {
        color: red;
        font-size: 10px;
        margin-top: 5px;
        display: block;
    }

    .form-checkout .form-details .item-ip label {
        font-weight: 400;
        letter-spacing: 0px;
    }


    .form-checkout .form-details .item-ip input {
        font-size: 13px;
        font-weight: 300;
    }

    .form-checkout .form-details .item-ip input {
        font-size: 13px;
        font-weight: 300;
    }


    .form-details {
        max-width: 900px !important;
    }

    .form-checkout .form-details .item-ip select {
        outline: none;
        box-shadow: unset;
        border: 1px solid #e5e5e5;
        border-radius: 0;
        width: 100%;
        height: 40px;
        padding-left: 20px;

        font-size: 13px;
        font-weight: 300;
    }

    .form-checkout .form-details .item-ip input:focus,
    .form-checkout .form-details .item-ip select:focus {
        border: 1px solid #000;
        transition: .3s;
    }
</style>
@endsection

@section('content')

<section class="form-checkout">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-12 form-details m-auto flex-column" style="display:flex; justify-content: center;">
                <div class="col-12">
                    <h2 style="text-align: center;">Ваша персональная скидка</h2>
                    <div class="circle">
                        <span>{{Auth::guard('web')->user()->country == 'Беларусь' ? Auth::guard('web')->user()->discountUser . '%' : Auth::guard('web')->user()->discountMoney . " " . get_valuta()}}</span>
                    </div>

                    <p class="black-clr" style="text-align: center;">
                        Номер карты: {{Auth::guard('web')->user()->discountNumber ? Auth::guard('web')->user()->discountNumber : '-'}}
                    </p>
                </div>
                <div class="col-md-12 form_profile_data">
                    <h2 style="text-align: center;">Профиль</h2>
                    <div class="col-md-6 col-sm-12 item-ip">
                        <label for="name">Ваше имя</label>
                        <input type="text" id="name" name="name" placeholder="Введите имя" value="{{Auth::guard('web')->user()->name}}">
                    </div>
                    <div class="col-md-6 col-sm-12 item-ip">
                        <label for="first_name">Ваша фамилия</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Введите фамилию" value="{{Auth::guard('web')->user()->first_name}}">
                    </div>
                    <div class="col-md-12 col-sm-12 item-ip">
                        <label for="name">Ваше отчество</label>
                        <input type="text" id="otchestvo" name="otchestvo" placeholder="Введите отчество" value="{{Auth::guard('web')->user()->otchestvo}}">
                    </div>
                    <div class="col-md-12 col-sm-12 item-ip">
                        <label for="birth_date">Дата рождения</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ Auth::guard('web')->user()->birthdate ? Auth::guard('web')->user()->birthdate->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-12 col-sm-12 item-ip">
                        <label for="select_country">Выберите страну</label>
                        <select class="selectpicker" name="select_country" id="select_country">
                            <option value="Беларусь" {{Auth::guard('web')->user()->country == 'Беларусь' ? 'selected' : ''}}>Беларусь</option>
                            <option value="Россия" {{Auth::guard('web')->user()->country == 'Россия' ? 'selected' : ''}}>Россия</option>
                            <option value="Казахстан" {{Auth::guard('web')->user()->country == 'Казахстан' ? 'selected' : ''}}>Казахстан</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-12 item-ip">
                        <label for="email">Ваш email</label>
                        <input type="email" id="email" name="email" placeholder="Нельзя изменить" disabled  value="{{Auth::guard('web')->user()->email}}">
                    </div>
                    <div class="col-md-6 col-sm-12 item-ip">
                        <label for="phone">Ваш номер телефона</label>
                        <input type="text" id="phone" name="phone" placeholder="Введите номер телефона" value="{{Auth::guard('web')->user()->phone}}">
                    </div>

                    <div class="btn-pay">
                        <button class="btn-cart btn-change-data" style="margin-right: 15px; float: right; width: auto;">Изменить данные профиля</button>
                    </div>
                </div>
                <div class="form_profile_passwords">
                    <div class="col-12 item-ip">
                        <label for="password_old">Введите старый пароль</label>
                        <input type="password" id="password_old" name="password_old" placeholder="Введите старый пароль">
                    </div>
                    <div class="col-md-6 col-sm-12 item-ip">
                        <label for="password">Введите новый пароль</label>
                        <input type="password" id="password" name="password" placeholder="Введите новый пароль">
                    </div>
                    <div class="col-md-6 col-sm-12 item-ip">
                        <label for="password_confirm">Подтвердите новый пароль</label>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Подтвердите пароль">
                    </div>

                    <div class="btn-pay">
                        <button class="btn-cart btn-change-password" style="margin-right: 15px; float: right; width: auto;">Изменить пароль</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{asset('assetsSite/js/inputmask/jquery.inputmask.min.js')}}"></script>
<script>
   $(document).ready(function() {

        if (sessionStorage.getItem('toastMessage')) {
            var message = sessionStorage.getItem('toastMessage');
            var success = sessionStorage.getItem('toastSuccess') === 'true';
            toastAlert(success, message);
            sessionStorage.removeItem('toastMessage');
            sessionStorage.removeItem('toastSuccess');
        }

        function showError(fieldId, errorMessage) {
            $(fieldId).addClass('error-border');
            var errorText = '<span class="error-text">' + errorMessage + '</span>';
            $(fieldId).after(errorText);
        }

        function removeError(fieldId) {
            $(fieldId).removeClass('error-border');
            $(fieldId).next('.error-text').remove();
        }

        function validateEmail(email) {
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailPattern.test(email);
        }

        function validatePhone() {
            return $('#phone').inputmask("isComplete");
        }

        function validatePasswordFields() {
            var password_old = $('#password_old').val();
            var password = $('#password').val();
            var password_confirm = $('#password_confirm').val();

            $('.error-text').remove();
            $('.error-border').removeClass('error-border');

            var isValid = true;

            if (password_old === '') {
                showError('#password_old', 'Введите старый пароль');
                isValid = false;
            }

            if (password === '') {
                showError('#password', 'Введите новый пароль');
                isValid = false;
            } else if (password.length < 6) {
                showError('#password', 'Новый пароль должен содержать минимум 6 символов');
                isValid = false;
            }

            if (password_confirm === '') {
                showError('#password_confirm', 'Подтвердите новый пароль');
                isValid = false;
            } else if (password !== password_confirm) {
                showError('#password_confirm', 'Новый пароль и его подтверждение не совпадают');
                isValid = false;
            }

            return isValid;
        }

        function validateProfileFields() {
            var name = $('#name').val();
            var otchestvo = $('#otchestvo').val();
            var first_name = $('#first_name').val();
            var phone = $('#phone').val();
            var country = $('#select_country').val();
            var birthdate = $('#birth_date').val();

            $('.error-text').remove();
            $('.error-border').removeClass('error-border');

            var isValid = true;

            if (name === '') {
                showError('#name', 'Введите имя');
                isValid = false;
            }

            if (birthdate === '') {
                showError('#birth_date', 'Введите Дату рождения');
                isValid = false;
            }

            if (otchestvo === '') {
                showError('#otchestvo', 'Введите отчесвто');
                isValid = false;
            }

            if (first_name === '') {
                showError('#first_name', 'Введите фамилию');
                isValid = false;
            }

            if (phone === '') {
                showError('#phone', 'Введите номер телефона');
                isValid = false;
            } else {
                switch (country) {
                    case 'Беларусь':
                        if (!validatePhone()) {
                            showError('#phone', 'Введите корректный номер телефона для Беларуси');
                            isValid = false;
                        }
                        break;
                    case 'Россия':
                        if (!validatePhone()) {
                            showError('#phone', 'Введите корректный номер телефона для России');
                            isValid = false;
                        }
                        break;
                    case 'Казахстан':
                        if (!validatePhone()) {
                            showError('#phone', 'Введите корректный номер телефона дляКазахстана');
                            isValid = false;
                        }
                        break;
                }
            }

            return isValid;
        }

        $('.btn-change-password').on('click', function() {
            if (!validatePasswordFields()) {
                return;
            }

            var formData = {
                password_old: $('#password_old').val(),
                password: $('#password').val(),
                password_confirmation: $('#password_confirm').val()
            };

            $.ajax({
                url: '{{ route("changePassword") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastAlert(true, response.message);
                        $('#password_old').val('');
                        $('#password').val('');
                        $('#password_confirm').val('');
                    } else {
                        toastAlert(false, response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastAlert(false, 'Произошла ошибка при изменении пароля. Пожалуйста, попробуйте снова.');
                }
            });
        });

        $('.btn-change-data').on('click', function() {
            if (!validateProfileFields()) {
                return;
            }

            var formData = {
                name: $('#name').val(),
                otchestvo: $('#otchestvo').val(),
                first_name: $('#first_name').val(),
                phone: $('#phone').val(),
                select_country: $('#select_country').val(),
                birth_date: $('#birth_date').val()
            };

            $.ajax({
                url: '{{ route("updateProfile") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // toastAlert(true, response.message);
                        sessionStorage.setItem('toastMessage', response.message);
                        sessionStorage.setItem('toastSuccess', 'true');
                        location.reload();
                    } else {
                        toastAlert(false, response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastAlert(false, 'Произошла ошибка при обновлении профиля. Пожалуйста, попробуйте снова.');
                }
            });
        });

        function setPhoneMask() {
            var country = $('#select_country').val();
            var mask = '';
            switch(country) {
                case 'Беларусь':
                    mask = '+375999999999';
                    // mask = '+375 (99) 999-9999';
                    break;
                case 'Россия':
                    mask = '+79999999999';
                    // mask = '+7 (999) 999-99-99';
                    break;
                case 'Казахстан':
                    mask = '+79999999999';
                    // mask = '+7 (999) 999-99-99';
                    break;
            }
            $('#phone').inputmask(mask);
        }

        $('#password_old, #password, #password_confirm').on('input', function() {
            removeError('#' + $(this).attr('id'));
        });

        $('#select_country').on('change', function() {
            $('#phone').val('');
            setPhoneMask();
        });

        setPhoneMask();
});

</script>
@endpush

