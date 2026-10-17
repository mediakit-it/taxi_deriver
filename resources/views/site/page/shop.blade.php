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

    .image-hover {
    width: 60px; 
    height: 55px; 
    transition: transform 0.3s ease;
    }

    .image-hover:hover {
        transform: scale(5);
        z-index: 10; 
        position: relative; 
    }
</style>
@endsection

@section('content')
@php
        $country = get_country_from_cookie(); // Получаем страну из куки
    @endphp
    @if ($country == 'Беларусь')
   
        <section class="title-contact">
                <center style="margin-top: 50px; margin-bottom: 50px;"><h3>Адреса магазинов</h3></center>
                <div class="row mt-5 cont-magazini">
                    @foreach($shops as $shop)
                        <div class="col-md-3 magazini">
                            <a href="{{ route('shop.products', ['shopId' => $shop->id]) }}">
                                <div class="imageblok-magazini">
                                @if($shop->image)
                                    
                                            <img src="{{ asset('images/' . $shop->image) }}" alt="{{ $shop->name_ru }}" class="image-magazini">
                                        
                                    @else
                                        <span style="margin: 0 auto;">Нет изображения</span>
                                    @endif
                                </div>
                                <div style="font-weight: 600; color: #4e4e4e; padding-top: 15px; padding-bottom: 5px;">
                                        {{$shop->name_ru}}
                                </div>
                                <div style="font-weight: 500; color: #4e4e4e;padding-bottom: 5px;">
                                        {{$shop->address_ru}}
                                </div>
                                <div style="color: #4e4e4e;padding-bottom: 15px;">
                                        {{$shop->oclock}}
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top: 30px;" class="karta-magazinov">
                	<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ac00b1936ed2fe51211359581b2ef5ca4b8a67ff3855a31c40f2ea6330e75031e&amp;width=100%25&amp;height=650&amp;lang=ru_RU&amp;scroll=true"></script>
                </div>
            </div>
        </div>
  
</section>
        @elseif ($country == 'Россия')

 <section class="title-contact">
   
                <center style="margin-top: 50px; margin-bottom: 50px;"><h3>Адреса магазинов</h3></center>
                <div class="row mt-5">
                    @foreach($shops as $shop)
                        <div class="col-md-3" style="height: 170px;">
                        <center>
                            @if($shop->image)
                                <img src="{{ asset('images/' . $shop->image) }}" alt="{{ $shop->name_ru }}" class="image-hover">
                            @else
                                <span>Нет изображения</span>
                            @endif
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
        </section>






        @elseif ($country == 'Казахстан')

      
 <section class="title-contact">
                <center style="margin-top: 50px; margin-bottom: 50px;"><h3>Адреса магазинов</h3></center>
                <div class="row mt-5">
                    @foreach($shops as $shop)
                        <div class="col-md-3" style="height: 170px;">
                        <center>
                            @if($shop->image)
                                <img src="{{ asset('images/' . $shop->image) }}" alt="{{ $shop->name_ru }}" class="image-hover">
                            @else
                                <span>Нет изображения</span>
                            @endif
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
        </section>

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






