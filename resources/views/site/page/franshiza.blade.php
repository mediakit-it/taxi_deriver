@extends('site.master')

@section('styles')
<style>
    .main_title {
        font-size: 32px;
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
    
    .open_store_form {
        margin: auto;
        padding: 50px 25px;
        border: 1px solid #ddd;
        max-width: 600px;
    }

    .form_header p{
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        color: #000;
    }

    .open_store_form label {
        font-weight: 400;
        margin-bottom: 0;
    }

    .open_store_form input {
        height: 40px;
        border-radius: 0;
    }

    .open_store_form input:focus {
        border: 1px solid var(--secondary-color-hover);
        box-shadow: 0 0 0;
    }

    #agreement {
        height: 15px;
        width: auto;
        margin: 0;
    }

    .new_row_image {
        margin-right: 55px;
        margin-bottom: 30px;
        max-width: 45%;
        float: left;
    }

    .reasons_wrap {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        justify-content: center;
    }
    .reason_item {
        font-size: 14px;
        flex: 0 1 25%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding-left: 15px;
        padding-right: 15px;
        padding-top: 40px;
        min-width: 205px;
    }

    .reason_item .reason_item_hdr {
        text-transform: uppercase;
        font-weight: 600;
        padding-top: 30px;
        padding-bottom: 25px;
        height: 42px;
        box-sizing: content-box;
    }

    .news_item_content_wrap ul li::before {
        content: "";
        display: inline-block;
        width: 12px;
        min-width: 12px;
        height: 17px;
        background-image: url("{{asset('assetsSite/images/About_us/list_image.svg')}}");
        background-size: contain;
        background-repeat: no-repeat;
        margin-right: 20px;
    }

    .news_item_content_wrap ul li {
        display: flex;
        align-items: center;
        font-size: 16px;
    }

    .btn-submit {
        color: #fff;
        background: #000;
        border: 1px solid #000;

        font-size: 18px;
        padding: 10px 20px;
    }
    .btn-submit:hover {
        border: 1px solid #898989;
        background: transparent;
        color: #333333 !important;
    }

    @media (max-width: 990px) {
        .reason_item {
            flex: 0 1 50%;
        }
    }

    @media (max-width: 786px) {
        .main_title {
            font-size: 22px;
            font-weight: 600;
        }
        .reason_item {
            flex: 0 1 50%;
        }
    }

    @media (max-width: 486px) {
        .new_row_image {
            width: 100%;
            max-width: 100%;
        }
        .reason_item {
            flex: 0 1 100%;
        }
    }
</style>
@endsection

@section('content')
  <!--  banner -->
  @if (get_languages_from_cookie() == 'RUS')
    <section class="banner-top-v1 banner-blog  banner-checkout banner-about">
        <h3>Франшиза</h3>
        <!-- <p>Lorem Ipsum is not simply random text.</p> -->
    </section>
    <!-- end/ banner -->
    <!--out -->
    <section class="out-mission container">
        <div class="container">
            <div class="row">
                <h3 class="main_title">Хотите открыть свой ПАРФЮМЕРНЫЙ магазин?</h3>
                <form class="open_store_form mb-5"  id="openStoreForm">
                    <div class="col-12 form_header mb-5">
                        <p>Хочешь открыть свой ПАРФЮМЕРНЫЙ магазин?</p>
                        <p>Напиши нам!</p>
                    </div>
                    <div class="col-12 mb-4">
                        <label for="first_name">Ваше имя *</label>
                        <input class="form-control" type="text" name="first_name" id="first_name">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="city">В каком городе вы хотите открыть магазин *</label>
                        <input class="form-control" type="text" name="city" id="city">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="email">Ваш email *</label>
                        <input class="form-control" type="text" name="email" id="email">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="telephone">Ваш номер телефона *</label>
                        <input class="form-control" type="text" name="telephone" id="telephone">
                    </div>
                    <div class="col-12 d-flex align-items-center gap-2 mb-4">
                        <input class="form-control" type="checkbox" name="agreement" id="agreement">
                        <label for="agreement">Я согласен на обработку персональных данных</label>
                    </div>
                    <div class="col-12 d-flex">
                        <button class="btn btn-submit m-auto" type="submit">Отправить</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <section class="container blog">
        {!! $pageconstruct->content_ru !!}
    </section>
                
    <!--end/out -->
    @else 
    <section class="banner-top-v1 banner-blog  banner-checkout banner-about">
        <h3>Франшиза</h3>
        <!-- <p>Lorem Ipsum is not simply random text.</p> -->
    </section>
    <!-- end/ banner -->
    <!--out -->
    <section class="out-mission container">
        <div class="container">
            <div class="row">
                <h3 class="main_title">Өз парфюмерия мәгілтешіңізді ашу керек пе?</h3>
                <form class="open_store_form mb-5" id="openStoreForm">
                    <div class="col-12 form_header mb-5">
                        <p>Өз парфюмерия мәгілтешіңізді ашу керек пе?</p>
                        <p>Бізге жазыңыз!</p>
                    </div>
                    <div class="col-12 mb-4">
                        <label for="first_name">Сіздің атыңыз *</label>
                        <input class="form-control" type="text" name="first_name" id="first_name">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="city">Мәгілтешін ашқан көшірмесіңіз келетін қалада *</label>
                        <input class="form-control" type="text" name="city" id="city">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="email">Сіздің email адресіңіз *</label>
                        <input class="form-control" type="text" name="email" id="email">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="telephone">Сіздің телефон нөміріңіз *</label>
                        <input class="form-control" type="text" name="telephone" id="telephone">
                    </div>
                    <div class="col-12 d-flex align-items-center gap-2 mb-4">
                        <input class="form-control" type="checkbox" name="agreement" id="agreement">
                        <label for="agreement">Мен жеке деректерді өңдеумен келісемін</label>
                    </div>
                    <div class="col-12 d-flex">
                        <button class="btn btn-submit m-auto" type="submit">Жіберу</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <section class="container blog">
        {!! $pageconstruct->content_kz !!}
    </section>
                
    @endif
    


@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        $('#openStoreForm').on('submit', function(e){
            e.preventDefault();

            // Валидация формы
            let isValid = true;

            // Очистить предыдущие ошибки
            $('#openStoreForm .error-text').remove();
            $('#openStoreForm input').removeClass('error-border');
            $('#openStoreForm label').removeClass('error-text');

            // Проверка обязательных полей
            $('#openStoreForm input[type="text"]').each(function(){
                if ($(this).val().trim() === '') {
                    isValid = false;
                    $(this).addClass('error-border');
                    $(this).after('<span class="error-text">Это поле обязательно для заполнения</span>');
                }
            });

            // Проверка email
            let email = $('#email').val().trim();
            if (email !== '' && !validateEmail(email)) {
                isValid = false;
                $('#email').addClass('error-border');
                $('#email').after('<span class="error-text">Введите корректный email</span>');
            }

            // Проверка номера телефона (допустим, простой проверки на длину 10 символов)
            let telephone = $('#telephone').val().trim();
            if (telephone !== '' && !validateTelephone(telephone)) {
                isValid = false;
                $('#telephone').addClass('error-border');
                $('#telephone').after('<span class="error-text">Введите корректный номер телефона</span>');
            }

            // Проверка соглашения
            if (!$('#agreement').is(':checked')) {
                isValid = false;
                // $('#agreement').closest('div').find('label').addClass('error-text');
                $('#agreement').addClass('error-border');
            }

            // Если все поля валидны, отправить форму (здесь может быть ajax-запрос)
            if (isValid) {
                let form = $(this); // сохраняем форму в переменной

                $.ajax({
                    url: '/franshizaMessage', // замените на реальный URL маршрута
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        
                        // Очистка формы
                        form[0].reset();
                        toastAlert(true, 'Ваша заявка успешно отправлена');
                    },
                    error: function(response) {
                        if (response.responseJSON && response.responseJSON.errors) {
                            let errors = response.responseJSON.errors;
                            for (let field in errors) {
                                let input = form.find(`[name="${field}"]`);
                                input.addClass('error-border');
                                input.after(`<span class="error-text">${errors[field][0]}</span>`);
                            }
                        } else {
                            toastAlert(false, 'Произошла ошибка при отправке формы. Пожалуйста, попробуйте еще раз.');
                        }
                    }
                });
            }
        });

        // Функция для проверки email
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@(([^<>()[\]\.,;:\s@"]+\.[^<>()[\]\.,;:\s@"]{2,}))$/i;
            return re.test(email.toLowerCase());
        }

        // Функция для проверки номера телефона
        function validateTelephone(telephone) {
            return telephone.length >= 10;  // Простая проверка на длину
        }
    });
</script>
@endpush

