@extends('site.master')

@section('styles')
<style>
    body {
        background: #0b0b10;
    }

    .login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
    }

    .login-card {
        display: flex;
        width: 100%;
        max-width: 920px;
        background: #ffffff;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    }

    .login-visual {
        flex: 1 1 45%;
        background: linear-gradient(160deg, #4b5cff 0%, #14142b 78%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        gap: 10px;
        padding: 40px;
        min-height: 480px;
    }

    /* Вордмарк как в админке: название компании текстом, последнее
       слово - в акцентной плашке (та же разметка - partials.brand-wordmark) */
    .login-visual__brand {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0.03em;
        line-height: 1;
        color: #ffffff;
        white-space: nowrap;
    }

    .login-visual__brand .brand-wordmark__box {
        background: #4b5cff;
        color: #ffffff;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .login-visual__title {
        font-size: 30px;
        font-weight: 700;
        line-height: 1.15;
        margin: 0;
    }

    .login-visual__text {
        color: rgba(255, 255, 255, 0.72);
        font-size: 15px;
        margin: 0;
    }

    .login-form-side {
        flex: 1 1 55%;
        display: flex;
        align-items: center;
        padding: 48px 44px;
    }

    .login-form {
        width: 100%;
    }

    .login-form__title {
        text-align: center;
        font-weight: 700;
        margin: 0 0 28px;
    }

    .login-field {
        margin-bottom: 16px;
    }

    .login-field input {
        width: 100%;
        height: 54px;
        border: none;
        border-radius: 999px;
        background: #f2f3f8;
        padding: 0 22px;
        font-size: 15px;
        outline: none;
    }

    .login-field input:focus {
        background: #e9ecff;
        box-shadow: 0 0 0 2px #4b5cff inset;
    }

    .login-btn {
        display: block;
        width: 100%;
        height: 54px;
        margin-top: 8px;
        float: none;
        border: none;
        border-radius: 999px;
        background: #0b0b10;
        color: #ffffff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }

    .login-btn:hover {
        background: #23232e;
        color: #ffffff;
    }

    @media (max-width: 767px) {
        .login-card {
            flex-direction: column;
        }

        .login-visual {
            min-height: 0;
            padding: 28px;
        }

        .login-form-side {
            padding: 32px 24px;
        }
    }
</style>
@endsection

@section('content')

<section class="login-page">
    <div class="login-card">
        <div class="login-visual">
            <div class="login-visual__brand">@include('partials.brand-wordmark')</div>
            <p class="login-visual__title">Панель управления</p>
            <p class="login-visual__text">Заказы, водители и машины службы такси в одном месте.</p>
        </div>
        <div class="login-form-side">
            <form class="login-form" method="POST" action="{{route('login_auth')}}">
            @csrf
                <h2 class="login-form__title">Вход</h2>
                <div class="login-field item-ip">
                    <input type="email" id="email" name="email" placeholder="Введите Email">
                </div>
                <div class="login-field item-ip">
                    <input type="password" id="password" name="password" placeholder="Введите пароль">
                </div>
                <button type="submit" class="btn-cart login-btn">Вход</button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    @if ($errors->has('email'))
        <script>
            toastAlert(false, '{{ $errors->first('email') }}');
        </script>
    @endif
@endpush
