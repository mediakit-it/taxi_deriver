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
        <div class="row d-flex">
            <div class="col-md-12 form-details m-auto" style="display:flex; justify-content: center;">
                <div class="col-md-6">
                    <form method="POST" action="{{route('login_auth')}}">
                    @csrf	
                        <div class="">
                            <h2 style="text-align: center;">Вход</h2>
                            <div class="col-md-12 item-ip">
                                <input type="email" id="email" name="email" placeholder="Введите Email">
                            </div>
                            <div class="col-md-12 item-ip">
                                <input type="password" id="password" name="password" placeholder="Введите пароль">
                            </div>
                        
                        </div>
                        <div class="btn-pay">
                            <button  type="submit" class="btn-cart" style="margin-right: 15px; float: right; width: 170px;">Вход</button>
                        </div>
                    </form>
                </div>
            </div>
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
