@extends('site.master')

@section('styles')
<style>
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
    @if (get_languages_from_cookie() == 'RUS')
        <!-- contact -->
        <section class="title-contact">
            <h3>Контакты и обратная связь</h3>
        </section>
        <!-- end/contact -->
        <!-- information -->
        <div class="textbox-information">
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-md-12 clearfix m-auto">
                        <div class="col-md-6 col-xs-12">
                           <div class="information">
     
                                
                                {!! $pageconstruct->content_ru !!}
                             
                            </div> 
                           
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div class="col-md-12 frm">
                                <div class="col-md-6 col-xs-12 form-ip">
                                    <input type="text" id="firstname" name="firstname" placeholder="Ваше имя *" style="margin-bottom:10px !important;">
                                </div>
                                <div class="col-md-6 col-xs-12 form-ip">
                                    <input type="text" id="phone" name="phone" placeholder="Ваш телефон *" style="margin-bottom:10px !important;">
                                </div>
                            </div>
                            <div class="col-md-12 frm-txt">
                                <textarea placeholder="Коментарий(необязательно)" id="message" class="form-control" name="comment[body]"></textarea>
                            </div>
                            <div class="col-md-12" style="margin-top: 28px;">
                                <div class="btn-black" style="width: 131px;" id="submitButton">
                                    <span> Оформить </span>  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <center style="margin-top: 50px; margin-bottom: 50px;"><h3>Адреса магазинов</h3></center>
                <div class="row mt-5">
                    @foreach($shops as $shop)
                        <div class="col-md-3" style="height: 170px;">
                            <center>
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42" viewBox="0 0 30 42">
                                    <defs>
                                        <style>
                                            .cls-1 {
                                                fill: #ccc;
                                                fill-rule: evenodd;
                                            }
                                        </style>
                                    </defs>
                                    <path class="cls-1" d="M250,4073a15.224,15.224,0,0,0-15,15.4c0,10.99,12.837,25.27,13.384,25.87a2.158,2.158,0,0,0,3.236,0c0.547-.6,13.384-14.88,13.384-25.87A15.225,15.225,0,0,0,250,4073Zm-5.921,14.92A5.923,5.923,0,1,1,250,4094,6,6,0,0,1,244.077,4087.92Z" transform="translate(-235 -4073)"></path>
                                </svg>
                            </center>
                            <center>
                                <div style="font-weight: 600; color: #4e4e4e; padding-top: 15px; padding-bottom: 5px;">
                                        {{$shop->name_ru}}
                                </div>
                                <div style="font-weight: 500; color: #4e4e4e;padding-bottom: 5px;">
                                        {{$shop->address_ru}}
                                </div>
                            </center>
                            <center>
                                <div style="color: #4e4e4e;padding-bottom: 15px;">
                                        {{$shop->oclock}}
                                </div>
                            </center>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top: 30px;" class="karta-magazinov">
                	<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ac00b1936ed2fe51211359581b2ef5ca4b8a67ff3855a31c40f2ea6330e75031e&amp;width=100%25&amp;height=650&amp;lang=ru_RU&amp;scroll=true"></script>
                </div>
            </div>
        </div>
    @else
        <!-- contact -->
        <section class="title-contact">
            <h3>Байланыс және кері байланыс</h3>
        </section>
        <!-- end/contact -->
        <div class="textbox-information">
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-md-12 clearfix m-auto">
                        <div class="col-md-6 col-xs-12">
                            <div class="information">
                               
                                {!! $pageconstruct->content_kz !!}
                                
                            </div>

                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div class="col-md-12 frm">
                                <div class="col-md-6 col-xs-12 form-ip">
                                    <input type="text" id="firstname" name="firstname" placeholder="Сіздің атыңыз *" style="margin-bottom:10px !important;">
                                </div>
                                <div class="col-md-6 col-xs-12 form-ip">
                                    <input type="text" id="phone" name="phone" placeholder="Сіздің телефоныңыз *" style="margin-bottom:10px !important;">
                                </div>
                            </div>
                            <div class="col-md-12 frm-txt">
                                <textarea placeholder="Пікір(міндетті емес)" id="message" class="form-control" name="comment[body]"></textarea>
                            </div>
                            <div class="col-md-12" style="margin-top: 28px;">
                                <div class="btn-black" style="width: 131px;" id="submitButton">
                                    <span> Рәсімдеу </span>  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <center style="margin-top: 50px; margin-bottom: 50px;"><h3>Дүкен мекенжайлары</h3></center>
                <div class="row mt-5">
                    @foreach($shops as $shop)
                        <div class="col-md-3" style="height: 170px;">
                            <center>
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42" viewBox="0 0 30 42">
                                    <defs>
                                        <style>
                                            .cls-1 {
                                                fill: #ccc;
                                                fill-rule: evenodd;
                                            }
                                        </style>
                                    </defs>
                                    <path class="cls-1" d="M250,4073a15.224,15.224,0,0,0-15,15.4c0,10.99,12.837,25.27,13.384,25.87a2.158,2.158,0,0,0,3.236,0c0.547-.6,13.384-14.88,13.384-25.87A15.225,15.225,0,0,0,250,4073Zm-5.921,14.92A5.923,5.923,0,1,1,250,4094,6,6,0,0,1,244.077,4087.92Z" transform="translate(-235 -4073)"></path>
                                </svg>
                            </center>
                            <center>
                                <div style="font-weight: 600; color: #4e4e4e; padding-top: 15px; padding-bottom: 5px;">
                                        {{$shop->name_kz}}
                                </div>
                                <div style="font-weight: 500; color: #4e4e4e;padding-bottom: 15px;">
                                        {{$shop->address_kz}}
                                </div>
                            </center>
                            <center>
                                <div style="color: #bbbbbb;">
                                        {{$shop->oclock}}
                                </div>
                            </center>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top: 30px;" class="karta-magazinov">
                	<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ac00b1936ed2fe51211359581b2ef5ca4b8a67ff3855a31c40f2ea6330e75031e&amp;width=100%25&amp;height=650&amp;lang=ru_RU&amp;scroll=true"></script>
                </div>
            </div>
        </div>

    @endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function showError(fieldId, fieldName) {
            $(fieldId).addClass('error-border');
            var errorMessage = '<span class="error-text">Пожалуйста, заполните поле ' + fieldName + '</span>';
            if ($(fieldId).next('.error-text').length === 0) {
                $(fieldId).after(errorMessage);
            }
        }

        function removeError(fieldId) {
            $(fieldId).removeClass('error-border');
            $(fieldId).next('.error-text').remove();
        }

        function validateFields() {
            var fields = [
                { id: '#firstname', name: 'Имя' },
                { id: '#phone', name: 'Телефон' }
            ];

            var isValid = true;

            fields.forEach(function(field) {
                var value = $(field.id).val();
                if (!value) {
                    isValid = false;
                    showError(field.id, field.name);
                } else {
                    removeError(field.id); // Reset error if field is filled
                }
            });

            return isValid;
        }

        $('#submitButton').on('click', function() {
            var isValid = validateFields();
            if (isValid) {

                $.ajax({
                    url: '/obratnayaMessage', // Путь к вашему роуту
                    type: 'POST',
                    data: {
                        'firstname': $('#firstname').val(),
                        'phone': $('#phone').val(),
                        'message': $('#message').val()
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        console.log(response);
                        if(response){
                            $('#firstname').val('');
                            $('#phone').val('');
                            $('#message').val('');
                            toastAlert(true, 'Сообщение успешно отправлено!');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastAlert(false, 'Ошибка отправки сообщения');
                    }
                });
            } else {
                toastAlert(false, 'Заполните все обязательные поля');
            }
        });

        $('input, textarea').on('input change', function() {
            if ($(this).val()) {
                removeError($(this));
            }
        });
    });
</script>
@endpush