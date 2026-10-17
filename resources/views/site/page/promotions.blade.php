@extends('site.master')

@section('styles')
<style>
    .promotion_link {
        color: #000;
    }

    .promotion_link:hover {
        color: #000;
    }

    .promotion__div:hover {
        box-shadow: 0 0 5px #bbb;
    }

    .promotion_card {
        padding: 10px 15px;
        border: 1px solid #dddddd79;
    }

    .promotion_image {
        display: flex;
        justify-items: center;
        height: auto;
    }

    .promotion_image img { 
        margin: auto;
        max-height: 100%;
        width: 100%;
    }

    .promotion_text_block {
        height: 60px;
    }
    .promotion_text {
        color: #000;
        font-weight: 500;

        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        -o-text-overflow: ellipsis;
        text-overflow: ellipsis;
        -webkit-line-clamp: 3;
    }
</style>
@endsection

@section('content')
<section class="promotions__section container py-5">
    <div class="row d-flex flex-wrap">
        @forelse($actions as $action)
            <div class="col-4">
                <div class="promotion__div">
                    <a class="promotion_link" href="{{route('actionPage',['slug'=>$action->slug])}}">
                        <div class="promotion_card">
                            <div class="promotion_image mb-3">
                                <img src="{{asset($action->image)}}" alt="">
                            </div>
                            <div class="promotion_text_block text-center">
                                <p class="promotion_text">
                                    {{get_languages_from_cookie() == "RUS" ? $action->name_ru : $action->name_kz}}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <h2 class="text-center pt-5">Мы пока не добавили акции, но они обязательно скоро появятся</h2>
                <p class="text-center">А пока вы можете просмотреть наш каталог и выбрать подходящие себе духи. У нас огромный выбор!</p>
                <div class="d-flex">
                    <img src="{{asset('assetsSite/images/About_us/go_buy_perfume.jpg')}}" alt="" style="max-width: 600px; margin: auto;">
                </div>
            </div>
        @endforelse
        
        <!-- <div class="col-6">
            <div class="promotion__div">
                <a class="promotion_link" href="#">
                    <div class="promotion_card">
                        <div class="promotion_image mb-3">
                            <img src="{{asset('assetsSite/images/About_us/avt3.png')}}" alt="">
                        </div>
                        <div class="promotion_text text-center">
                            Lorem Ipsum is simply dummy text of the printing 
                            and typesetting industry. Lorem Ipsum has been the 
                            industry's standard dummy text ever since the 1500s
                        </div>
                    </div>
                </a>
            </div>
        </div> -->
    </div>
</section>
@endsection