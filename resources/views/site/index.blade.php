@extends('site.master')

@section('styles')
<link rel="stylesheet" href="{{asset('assetsSite/css/splide.min.css')}}">

<style>
    .splide__pagination {
        right: 150px;
        bottom: 25px;
        left: auto;
    }
    .splide__arrow {
        border-radius: 0;
        width: 40px;
        height: 40px;
        background: #fff0;
        opacity: 1;
    }
    .splide__arrow--prev {
        left: 0;
    }
    .splide__arrow--next {
        right: 0;
    }
    .splide__arrow:hover:not(:disabled) {
        background-color: #000;
    }
    .splide__arrow svg {
        width: 12px;
        height: 12px;
    }
    .splide__arrow:hover:not(:disabled) svg {
        fill: #fff;
    }

    .splide__pagination.splide__pagination--ltr {
        display: none;
    }
</style>
@endsection

@section('content')
<!-- slideshow -->
@if($banners)
    <!-- <section class="splide container-fluid" aria-label="Splide Basic HTML Example" data-splide='{"type":"loop", "autoplay": "true", "interval": "5000"}'>
        <div class="splide__track">
            <ul class="splide__list">
                @foreach($banners as $banner)
                        <li class="splide__slide">
                            @if($banner->type_url=="action")
                                <a href="{{route('actionPage',['slug'=>$banner->slug])}}"><img class="img-responsive" src="{{$banner->image}}" alt=""></a>    
                            @elseif($banner->type_url=="metka")
                                <a href="{{route('catalogAll')}}?metkaIds={{$banner->slug}}"><img class="img-responsive" src="{{$banner->image}}" alt=""></a>  
                            @elseif($banner->type_url=="product")
                                <a href="{{route('site.product',['slug'=>$banner->slug])}}"><img class="img-responsive" src="{{$banner->image}}" alt=""></a>  
                            @elseif($banner->type_url=="Brand")
                                <a href="{{route('catalogAll')}}?brandIds={{$banner->slug}}"><img class="img-responsive" src="{{$banner->image}}" alt=""></a>  
                            @elseif($banner->type_url=="noUrl") 
                                <img class="img-responsive" src="{{$banner->image}}" alt="">
                            @else
                                <img class="img-responsive" src="{{$banner->image}}" alt="">  
                            @endif
                        </li>
                @endforeach
            </ul>
        </div>
    </section> -->
@endif
    <!-- end/slideshow -->
<section>
    <p style="text-align: center;padding: 200px;font-size: 32px;font-weight: 700;color: black;">САЙТ СКОРО ПОЯВИТСЯ</p>
</section>


@endsection

@push('scripts')
<script src="{{asset('assetsSite/js/splide.js')}}"></script>

<script>
    $(document).ready(function () {
        var splide = new Splide( '.splide' );
        splide.mount();
    });
</script>
@endpush