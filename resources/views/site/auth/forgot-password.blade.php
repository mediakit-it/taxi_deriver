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
                        <h2 style="text-align: center;">Восстановление пароля</h2>
                        <p class="text-center">Введите свой email в поле ниже и нажмите кнопку "Восстановить". На вашу почту будет выслан новый пароль с дальнейшими инструкциями.</p>
                        <div class="col-md-12 item-ip">
                            <input type="email" id="email" name="email" placeholder="Введите Email">
                        </div>                    
                    </div>
                    <!-- <div class="checkbox-custom" style="margin-left: 15px;"><input type="checkbox"> IвЂ™ve read and accept the terms &amp; conditions *</div> -->
                    <div class="btn-pay">
                        <button type="submit" class="btn-cart" style="margin-right: 15px; float: right; width: 170px;">Восстановить</button>
                    </div>
                </div>
            </div>
            <div class="col-12 d-none show_after_tap py-5">
                <div class="d-flex justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="150" height="150" x="0" y="0" viewBox="0 0 512 511" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><linearGradient id="a" x1="256" x2="256" y1=".499" y2="399.549" gradientUnits="userSpaceOnUse"><stop stop-opacity="1" stop-color="#f5d92a" offset="0"></stop><stop stop-opacity="1" stop-color="#ee9100" offset="1"></stop></linearGradient><path fill="url(#a)" d="M372 80.5H60c-33.086 0-60 26.914-60 60v199.05c0 33.083 26.914 60 60 60h312c33.086 0 60-26.917 60-60V140.5c0-33.086-26.914-60-60-60zm-6.488 40L216 231.71 66.488 120.5zM372 359.55H60c-11.027 0-20-8.972-20-20V150.649l176 130.91 176-130.91v188.903c0 11.027-8.973 20-20 20zM512 60.5v199.05c0 26.071-16.723 48.302-40 56.56V60.5c0-11.027-8.973-20-20-20H83.441C91.695 17.223 113.926.5 140 .5h312c33.086 0 60 26.914 60 60zm0 0" opacity="1" data-original="url(#a)" class=""></path></g></svg>
                </div>
                <h2 class="text-center">Уведомление о смене пароля</h2>
                <p class="text-center">Новый пароль был отправлен вам на почту. Пожалуйста, проверьте <br> ваш почтовый ящик и следуйте инструкциям в письме.</p>
            </div>
            
        </div>
    </div>
</section>
 
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $(".btn-cart").on('click', function () {
            var email = $('#email').val();
            $.ajax({
                url: '/forgout-password-send',
                method: 'POST',
                data: {
                    email: email,
                    _token: csrfToken
                },
                success: function (response) {
                    $('.show_after_tap').removeClass('d-none');
                    $('.show_before_tap').addClass('d-none');
                }
            });
        });
    });
</script>
@endpush

