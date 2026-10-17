@extends('site.master')

@section('content')
    <!--  banner -->
    <section class="banner-top-v1 banner-blog  banner-checkout banner-about">
        <h3>Акции</h3>
        <!-- <p>Lorem Ipsum is not simply random text.</p> -->
    </section>
    <!-- end/ banner -->
    <!--out -->
    <section class="out-mission">
        <div class="container">
            <div class="row">
                <h3>{{get_languages_from_cookie() == 'RUS' ? $action->name_ru : $action->name_kz}}</h3>
                <div class="col-md-6">
                    <div class="prd-about">
                        <img class="img-responsive" src="{{asset($action->image)}}" alt="">
                    </div>
                </div>
                <div class="col-md-6">
                @if(get_languages_from_cookie() == 'RUS' )
                    @foreach(explode("\n", $action->description_ru) as $actxyi)
                        <div class="item-text"><span>{!! $actxyi !!}</span></div>
                    @endforeach
                @else
                    @foreach(explode("\n", $action->description_kz) as $actxyi)
                        <div class="item-text"><span>{!! $actxyi !!}</span></div>
                    @endforeach
                @endif
                </div>
            </div>
        </div>
    </section>
    <!--end/out -->
@endsection