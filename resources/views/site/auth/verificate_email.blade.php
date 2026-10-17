@extends('site.master')

@section('styles')
<style>
    
.form-details {
	max-width: 900px !important;
}
</style>
@endsection

@section('content')

<section class="form-checkout">
    <div class="container">
        <div class="row d-flex flex-column">
           
            <div class="col-md-12 form-details m-auto d-flex justify-content-center show_before_tap">
                <div class="col-8">
                    <div class="">
                        <h2 style="text-align: center;">Подтверждение email</h2>
                        <p class="text-center">Вам на почту отправлен мастер код, который необходимо ввести поле ниже</p>
                        <div class="col-md-12 item-ip">
                            <input type="text" id="code" name="code" placeholder="Введите код">
                        </div>                    
                    </div>
                    <!-- <div class="checkbox-custom" style="margin-left: 15px;"><input type="checkbox"> IвЂ™ve read and accept the terms &amp; conditions *</div> -->
                    <div class="btn-pay">
                        <button class="btn-cart" style="margin-right: 15px; float: right; width: 170px;">Отправить</button>
                        <button class="btn-buy btn-timer" id="btn-timer" style="margin-right: 15px; float: right; width: 100px;"></button>
                        <button class="btn-cart d-none" style="margin-right: 15px; float: right; width: 250px;">Отправить код еще раз</button>
                    </div>
                </div>
            </div>
            <div class="col-12 d-none show_after_success py-5">
                <div class="d-flex justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="150" height="150" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><linearGradient id="a" x1="12" x2="12" y1="1.25" y2="22.75" gradientUnits="userSpaceOnUse"><stop stop-opacity="1" stop-color="#ff8800" offset="0"></stop><stop stop-opacity="1" stop-color="#e8e100" offset="1"></stop></linearGradient><g fill="url(#a)"><path d="M12 22.75C6.07 22.75 1.25 17.93 1.25 12S6.07 1.25 12 1.25 22.75 6.07 22.75 12 17.93 22.75 12 22.75zm0-20C6.9 2.75 2.75 6.9 2.75 12S6.9 21.25 12 21.25s9.25-4.15 9.25-9.25S17.1 2.75 12 2.75z" fill="" opacity="1"></path><path d="M10.58 15.58a.75.75 0 0 1-.53-.22l-2.83-2.83c-.29-.29-.29-.77 0-1.06s.77-.29 1.06 0l2.3 2.3 5.14-5.14c.29-.29.77-.29 1.06 0s.29.77 0 1.06l-5.67 5.67a.75.75 0 0 1-.53.22z" fill="" opacity="1"></path></g></g></svg>
                </div>
                <h2 class="text-center">Уведомление о успешной верификации</h2>
                <p class="text-center">Верификация пройдена успешно.</p>
            </div>
            <div class="col-12 d-none show_after_unsuccess py-5">
                <div class="d-flex justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="150" height="150" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><linearGradient id="a" x1="0" x2="512" y1="258" y2="258" gradientTransform="matrix(1 0 0 -1 0 514)" gradientUnits="userSpaceOnUse"><stop stop-opacity="1" stop-color="#ff9200" offset="0"></stop><stop stop-opacity="1" stop-color="#ff8f0f" offset="0.16"></stop><stop stop-opacity="1" stop-color="#ffad00" offset="0.413"></stop><stop stop-opacity="1" stop-color="#fff019" offset="0.725"></stop><stop stop-opacity="1" stop-color="#ebff00" offset="1"></stop></linearGradient><path d="M256 0C114.508 0 0 114.497 0 256c0 141.493 114.497 256 256 256 141.492 0 256-114.497 256-256C512 114.507 397.503 0 256 0zm0 472c-119.384 0-216-96.607-216-216 0-119.385 96.607-216 216-216 119.384 0 216 96.607 216 216 0 119.385-96.607 216-216 216zm87.586-275.302L284.284 256l59.302 59.302c7.811 7.811 7.812 20.474.001 28.284-7.811 7.811-20.476 7.81-28.284 0L256 284.284l-59.303 59.302c-7.808 7.809-20.471 7.812-28.284 0-7.811-7.811-7.81-20.474.001-28.284L227.716 256l-59.302-59.302c-7.811-7.811-7.812-20.474-.001-28.284s20.476-7.811 28.284 0L256 227.716l59.303-59.302c7.81-7.81 20.473-7.811 28.284 0 7.81 7.811 7.809 20.474-.001 28.284z" style="fill:url(#a);" fill=""></path></g></svg>
                </div>
                <h2 class="text-center">Уведомление о неудачной верификации</h2>
                <p class="text-center">Верификация не закончилась успехом</p>
            </div>

            
        </div>
    </div>
</section>
 
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $(".btn-cart").on('click', function () {
            $('.show_before_tap').addClass('d-none');

            if (false) $('.show_after_success').removeClass('d-none');
            else $('.show_after_unsuccess').removeClass('d-none');
        });


        document.querySelector('#btn-timer').onclick = function(){
            var button = this;
            var buttonText = this.innerText;
            
            button.setAttribute("disabled", true);
            
            var secondsUntilNextClick = 60;
            var interval = setInterval(function(){
                secondsUntilNextClick--;
                button.innerText = secondsUntilNextClick;
            
                if (secondsUntilNextClick === 0){
                clearInterval(interval);
                    button.innerText = buttonText;
                    button.removeAttribute("disabled");
                }
                
            }, 1000)
        }

        $('#btn-timer').click();
    });
</script>
@endpush

