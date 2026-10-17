<!DOCTYPE html>
<html lang="en">

<head>
    <title>Такси Быхов</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- CSS LIBRARY -->
    <link rel="stylesheet" type="text/css" href="{{asset('assetsSite/vendor/slick.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assetsSite/vendor/slick-theme.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assetsSite/vendor/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/fontello-codes.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/bootstrap.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/fontello-embedded.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/fontello-ie7-codes.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/fontello-ie7.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/fontello.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/ionicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/Pe-icon-7-stroke.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/styles_Ecommerce.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/jquery.fancybox.min.css')}}">
    <link rel="stylesheet" href="{{asset('assetsSite/css/jquery.toast.css')}}">
    <!-- MAIN STYLE -->
    <link rel="stylesheet" type="text/css" href="{{asset('assetsSite/css/style.css')}}">

    <style>
    /* Центрирование модального окна */
    .modal-dialog {
        min-height: calc(100% - 3.5rem);
        display: flex;
        align-items: center;
        position: absolute;
        top: 0px;
        left: 0px !important;
        right: 0px !important;
        /* left: 50%; */
        /* transform: translate(-50%, -50%); */
        margin: auto;
    }
    .modal-content {width: 100%;}
    /* .modal-backdrop {
      background-color: rgba(0, 0, 0, 0.5);
    } */

    .position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    padding: 10px;
    box-sizing: border-box;
}

.metka-product ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.metka-product li {
    background-color: rgba(0, 0, 0, 0.5); /* Полупрозрачный фон */
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
}


.select-custom .bootstrap-select {
    width: 100%;
    height: 40px;
    border: 1px solid #e5e5e5;
    padding-left: 20px;
}

.select-custom  .bootstrap-select button {
    width: 100%;
    padding-top: 10px;
    font-size: 13px;
    font-weight: 300;
    color: #888888;
    position: relative;
}

.select-custom .dropdown-menu {
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    top: 97% !important;
}

.item-ip .bootstrap-select button i {
    color: #888888;
    float: right;
    font-size: 14px;
    position: absolute;
    right: 10px;
}

  </style>

  @yield('styles')
</head>

<body>
    <!-- header -->
    
    <!-- end/header -->

    @yield('content')
  

 
   
    <!-- LOAD JQUERY -->
    <script src="{{asset('assetsSite/js/jquery-1.12.4.min.js')}}"></script>
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDyCxHyc8z9gMA5IlipXpt0c33Ajzqix4"></script> -->
    <script src="{{asset('assetsSite/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('assetsSite/js/slick.min.js')}}"></script>
    <script src="{{asset('assetsSite/js/select.js')}}"></script>
    <script src="{{asset('assetsSite/js/jquery.fancybox.min.js')}}"></script>
    <script src="{{asset('assetsSite/js/jquery.toast.js')}}"></script>
    <script src="{{asset('assetsSite/js/bootstrap-slider.min.js')}}"></script>
    <!-- Custom jQuery -->
    <script src="{{asset('assetsSite/js/main.js')}}"></script>
    <script src="{{asset('assetsSite/js/cart.js')}}"></script>

    @stack('scripts')

  
</body>

</html>