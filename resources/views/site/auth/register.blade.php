@extends('site.master')

@section('styles')
<style>
    .form-details {
        max-width: 900px !important;
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
</style>
@endsection

@section('content')

<section class="form-checkout">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-12 form-details m-auto" id="registrationElement" style="display:flex; justify-content: center;">
                <form id="registrationForm">
                @csrf
                    <div class="">
                        <h2 style="text-align: center;">Регистрация</h2>
                        <div class="col-md-6 col-sm-12 item-ip">
                            <label for="name">Ваше имя</label>
                            <input type="text" id="name" name="name" placeholder="Введите имя">
                        </div>
                        <div class="col-md-6 col-sm-12 item-ip">
                            <label for="first_name">Ваше отчество</label>
                            <input type="text" id="otchestvo" name="otchestvo" placeholder="Ваше отчество">
                        </div>
                        <div class="col-md-12 col-sm-12 item-ip">
                            <label for="first_name">Ваша фамилия</label>
                            <input type="text" id="first_name" name="first_name" placeholder="Введите фамилию">
                        </div>
                        <div class="col-md-12 col-sm-12 item-ip">
                            <label for="birth_date">Дата рождения</label>
                            <input type="date" id="birth_date" name="birth_date">
                        </div>
                        <div class="col-md-12 col-sm-12 item-ip">
                            <label for="select_country">Выберите страну</label>
                            <select class="selectpicker" name="select_country" id="select_country">
                                <option value="Беларусь">Беларусь</option>
                                <option value="Россия">Россия</option>
                                <option value="Казахстан">Казахстан</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-sm-12 item-ip">
                            <label for="email">Ваш email</label>
                            <input type="email" id="email" name="email" placeholder="Введите Email">
                        </div>
                        <div class="col-md-6 col-sm-12 item-ip">
                            <label for="phone">Ваш номер телефона</label>
                            <input type="text" id="phone" name="phone" placeholder="Введите номер телефона">
                        </div>

                                            <div class="product">
                        <p style="color: black; font-size: 18px; text-align: center;">Дорогие клиенты,<br> ознакомьтесь, пожалуйста, до предоставления согласия<br> ООО "Перфекс"<br> со следующей информацией:</p>
                        <p style="color: black; font-size: 16px; text-align: center;"><a style="color: #181818; font-weight: 600;"  href="public/doc/Условия_обработки_персональных_данных.pdf">Условия обработки</a> Ваших персональных данных</p>
                        <p style="color: black; font-size: 16px; text-align: center;"><a style="color: #181818; font-weight: 600;" href="public/doc/Права,_связанные_с_обработкой_ПД (1).pdf" target="_blank" class="styled-link">Ваши прова</a>, связанные с обработкой персональных данных, и механизм их реализации, в том числе последствия дачи согласия или отказа в даче согласия</p>

                        <div class="col-12 item-ip d-flex align-items-center">
                            <input type="checkbox" name="consent_1" id="consent_1" style="height: 15px;width: 15px;margin: 0;">
                            <label for="consent_1" style="margin: 0;margin-left: 10px;">
                                Даю свое согласие на обработку персональных данных в целях создания и использования личного кабинета на сайте <a style="color: black; font-weight: 600;" href="https://azmirli-perfume.by/">https://azmirli-perfume.by/</a>.
                            </label>
                        </div>

                        <div class="col-12 item-ip d-flex align-items-center">
                            <input type="checkbox" name="consent_2" id="consent_2" style="height: 15px;width: 15px;margin: 0;">
                            <label for="consent_2" style="margin: 0;margin-left: 10px;">
                                Даю свое согласие на обработку персональных данных в целях получения рекламно-информационной рассылки ООО «Перфекс» о товарах (работах, услугах) и проводимых рекламных кампаниях/акциях посредством отправки Viber/SMS-сообщений (если Viber-сообщение не доставлено).
                            </label>
                        </div>

                        <div class="col-12 item-ip d-flex align-items-center">
                            <input type="checkbox" name="consent_3" id="consent_3" style="height: 15px;width: 15px;margin: 0;">
                            <label for="consent_3" style="margin: 0;margin-left: 10px;">
                                Даю свое согласие на обработку персональных данных в целях участия в <a style="color: #181818; font-weight: 600;" href="public/doc/Программа лояльности.pdf" target="_blank" class="styled-link">программе лояльности «Azmirli».</a> С Положением о программе лояльности ознакомлен.
                            </label>
                        </div>

                        <!-- <div class="col-12 item-ip d-flex align-items-center">
                            <input type="checkbox" name="polit_conf" id="polit_conf" style="height: 15px;width: 15px;margin: 0;">
                            <label for="polit_conf" style="margin: 0;margin-left: 5px;">
                                Нажимая кнопку, вы соглашаетесь с
                                <a href="public/doc/Условия_обработки_персональных_данных.pdf" target="_blank" class="styled-link">политикой конфиденциальности</a>
                            </label>
                        </div>-->
                    </div>

                    </div>
                    <div class="btn-pay">
                        <button  type="button" id="registerButton" class="btn-cart" style="margin-right: 15px; float: right; width: 240px;">Зарегистрироваться</button>
                    </div>
                </form>
            </div>
            <div class="col-12 d-none show_after_success py-5">
                <div class="d-flex justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="150" height="150" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><linearGradient id="a" x1="12" x2="12" y1="1.25" y2="22.75" gradientUnits="userSpaceOnUse"><stop stop-opacity="1" stop-color="#ff8800" offset="0"></stop><stop stop-opacity="1" stop-color="#e8e100" offset="1"></stop></linearGradient><g fill="url(#a)"><path d="M12 22.75C6.07 22.75 1.25 17.93 1.25 12S6.07 1.25 12 1.25 22.75 6.07 22.75 12 17.93 22.75 12 22.75zm0-20C6.9 2.75 2.75 6.9 2.75 12S6.9 21.25 12 21.25s9.25-4.15 9.25-9.25S17.1 2.75 12 2.75z" fill="" opacity="1"></path><path d="M10.58 15.58a.75.75 0 0 1-.53-.22l-2.83-2.83c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l2.3 2.3 5.14-5.14c.29-.29.77-.29 1.06 0s.29.77 0 1.06l-5.67 5.67a.75.75 0 0 1-.53.22z" fill="" opacity="1"></path></g></g></svg>
                </div>
                <h2 class="text-center">Вы успешно зарегистрировались на сайте.</h2>
                <p class="text-center">На почту был выслан пароль к профилю, который в дальнейшем можно изменить в профиле.</p>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{asset('assetsSite/js/inputmask/jquery.inputmask.min.js')}}"></script>
<script>
    $(document).ready(function() {
        function showError(fieldId, errorMessage) {
            $(fieldId).addClass('error-border');
            var errorText = '<span class="error-text">' + errorMessage + '</span>';
            if ($(fieldId).next('.error-text').length === 0) {
                $(fieldId).after(errorText);
            }
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

        function validateFields() {
    var fields = [
        { id: '#name', name: 'Имя' },
        { id: '#first_name', name: 'Фамилия' },
        // { id: '#otchestvo', name: 'Отчество' },
        { id: '#email', name: 'E-mail' },
        { id: '#birth_date', name: 'Дата рождения' },
        { id: '#phone', name: 'Телефон' }
    ];

    var isValid = true;

    fields.forEach(function(field) {
        var value = $(field.id).val();
        if (!value) {
            isValid = false;
            showError(field.id, 'Пожалуйста, заполните поле ' + field.name);
        } else {
            removeError(field.id);

            if (field.id === '#email' && !validateEmail(value)) {
                isValid = false;
                showError(field.id, 'Пожалуйста, введите корректный E-mail');
            }

            if (field.id === '#phone' && !validatePhone()) {
                isValid = false;
                showError(field.id, 'Пожалуйста, введите корректный номер телефона');
            }
        }
    });

    // Уберите проверку на polit_conf
    // if (!$('#polit_conf').is(':checked')) {
    //     isValid = false;
    //     toastAlert(false, 'Пожалуйста, подтвердите согласие с политикой конфиденциальности.');
    // }

    return isValid;
}

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
            $('#phone').val('');
        }

        $('#registerButton').on('click', function() {
    var isValid = validateFields(); // Ваша функция валидации полей
    var consent1 = $('#consent_1').is(':checked');
    var consent2 = $('#consent_2').is(':checked');
    var consent3 = $('#consent_3').is(':checked');

    // Проверка, установлены ли все чекбоксы (без politConf)
    if (!consent1 || !consent2 || !consent3) {
        toastAlert(false, 'Пожалуйста, подтвердите все согласия перед продолжением.');
        return; // Прерываем выполнение, если не все согласия даны
    }

    if (isValid) {
        var formData = {
            name: $('#name').val(),
            first_name: $('#first_name').val(),
            otchestvo: $('#otchestvo').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            birth_date: $('#birth_date').val(),
            select_country: $('#select_country').val(),
            // polit_conf: politConf // Убрано, так как politConf больше не используется
        };

        $('.show_after_success').removeClass('d-none');
        $('#registrationElement').hide();

        $.ajax({
            url: '{{ route("registrationUser") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    toastAlert(true, 'Регистрация прошла успешно');
                    $('.show_after_success').removeClass('d-none');
                    $('#registrationElement').hide();
                } else {
                    toastAlert(false, response.message);
                }
            },
            error: function(xhr, status, error) {
                toastAlert(false, 'Произошла ошибка при регистрации. Пожалуйста, попробуйте снова.');
            }
        });
    } else {
        toastAlert(false, 'Пожалуйста, заполните все поля.');
    }

});

$('input').on('input change', function() {
    if ($(this).val()) {
        removeError($(this));
    }
});

$('#select_country').on('change', function() {
    setPhoneMask();
});

setPhoneMask();
    });
</script>

@endpush
