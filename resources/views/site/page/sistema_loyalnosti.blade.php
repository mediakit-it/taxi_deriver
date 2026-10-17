@extends('site.master')

@section('content')
<section class="banner-top-v1 banner-blog banner-checkout banner-about">  </section>
<section class="container">
@if (get_languages_from_cookie() == 'RUS')

    {!! $pageconstruct->content_ru !!}

@else

    
   {!! $pageconstruct->content_kz !!}

@endif
</section>  


@endsection