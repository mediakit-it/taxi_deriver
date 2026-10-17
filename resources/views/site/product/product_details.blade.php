@extends('site.master')

@section('styles')
<style>
    .review_block,
    .empty_reviews {
        padding: 15px 20px;
        border: 1px solid #ededed;
        margin-bottom: 10px;
    }

    .review_block .row {
        padding: 0 15px;
    }

    .total_reviews {
        width: 90%;
        box-shadow: 0 2px 22px rgba(0, 0, 0, 0.1);
        display: flex;
        padding: 20px;
    }

    .empty_reviews_block .newReview,
    .comment-form__full .newReviewSubmit {
        background-color: var(--secondary-color);
        font-size: 14px;
        font-weight: 600;
        line-height: 24px;
        color: #000;
        padding: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        transition: all 0.4s ease 0s;
    }

    .empty_reviews_block .newReview:hover {
        background-color: var(--secondary-color-hover);
        color: #fff;
    }

    .empty_reviews_block .newReview[disabled] {
        background-color: #f8f8f8;
        color: #b7b7b7;
    }

    .tab-content .review_body p,
    .tab-content .review_header p {
        margin-top: 0;
        margin-bottom: 0;
    }

    .review_form {
        padding: 15px 32px 26px;
        border: 1px solid #ededed;
    }

    .label-required {
        color: red;
    }

    .form-group {
        margin-top: 20px;
    }

    .form-control-label {
        color: #555;
        color: var(--basic_text_black);
        margin-bottom: 5px;
        display: inline-block;
    }

    .form-control {
        color: #000;
        box-shadow: none;
        border: 1px solid #dadada;
        height: 47px;
        padding: 6px 14px;
        background-color: #fff;
        font-size: 16px;
        line-height: calc(1em + 4px);
        position: relative;
        width: 100%;
    }

    .form-control:disabled, .form-control[readonly] {
        background-color: #f7f7f7;
        opacity: 1;
    }

    textarea.form-control {
        min-height: 117px;
        resize: none;
    }

    .rating-area:not(:checked) > input {
        display: none;
    }
    .rating-area:not(:checked) > label {
        float: right;
        padding: 0;
        cursor: pointer;
        font-size: 24px;
        color: lightgrey;
        text-shadow: 1px 1px #bbb;
    }
    .rating-area:not(:checked) > label:before {
        content: '\2605';
    }
    .rating-area > input:checked ~ label {
        color: #ffa700;
    }
    .rating-area:not(:checked) > label:hover,
    .rating-area:not(:checked) > label:hover ~ label {
        color: #ffa700;
    }
    .rating-area > input:checked + label:hover,
    .rating-area > input:checked + label:hover ~ label,
    .rating-area > input:checked ~ label:hover,
    .rating-area > input:checked ~ label:hover ~ label,
    .rating-area > label:hover ~ input:checked ~ label {
        color: #ffa700;
    }
    .rate-area > label:active {
        position: relative;
    }
    
    #not-available {
        position: absolute;
        right: 0;
        z-index: 1;
        padding: 10px;
        background: red;
        color: #fff;
        display: none;
    }

    .quick-shop .prdHsId {
        position: relative;
    }

    
</style>
@endsection

@section('content')

<script>
    var product = @json($product);
    let shop = @json($product->shopProductsWeb[0]);
    var valutaJs = "{{ get_valuta() }}";
    var languagesJs = "{{ get_languages_from_cookie() }}";
</script>
<!-- banner -->
<div class="banner-top">
    <div class="container">
        <ul>
            <li><a href="{{route('home')}}">{{get_languages_from_cookie()=="RUS" ? 'Главная' : 'Басты бет'}} /</a></li>
            <li><a href="{{route('catalog',['slug'=>$product->category->slug])}}">{{get_languages_from_cookie()=="RUS" ? $product->category->name_ru : $product->category->name_kz}} /</a></li>
            <li><a>{{get_languages_from_cookie()=="RUS" ? $product->name_ru : $product->name_kz}} </a></li>
        </ul>
    </div>
</div>
<!-- end/banner -->
<!-- content-details-product -->
<section class="content-details-product" data-product-slug="{{$product->slug}}">
    <div class="container">
        <div class="row">
            <div class="col-md-1 scr-1"></div>
            <div class="col-md-12">
                <div class="right-content">
                    <div class="quick-shop clearfix mx-0">
                        <div class="col-md-6 prdHsId" data-product-id="{{$product->id}}" style="padding-right: 0; padding-left: 0">
                           @if($product->shopProductsWeb[0]->variable == "false")
                                <div id="not-available"style="display: {{ $product->shopProductsWeb[0]->quantity > 0 ? 'none' : 'block' }};">
                                    Нет в наличии
                                </div>
                           @else
                                <div id="not-available"style="display: {{ $product->shopProductsWeb[0]->variations[0]['quantity'] > 0 ? 'none' : 'block' }};">
                                    Нет в наличии
                                </div>
                           @endif
                       
                            <div class="img-product slider-for overflow-hidden">
                                @foreach($product->gallery as $img)
                                    <div class="item">
                                        <img class="img-responsive" src="{{$img}}" alt="">
                                    </div>
                                @endforeach
                            </div>
                            @auth('web')
                                <div class="favourite-btn {{$product->isFavoritedByUser() ? 'active' : ''}}">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 412.735 412.735" style="enable-background:new 0 0 512 512" xml:space="preserve" class="heart_favourite"><g>
                                        <path class="icon__fill {{$product->isFavoritedByUser() ? 'active' : ''}}" d="M295.706 46.058c58.744.286 106.294 47.836 106.58 106.58 0 107.624-195.918 214.204-195.918 214.204S10.449 258.695 10.449 152.638c0-58.862 47.717-106.58 106.58-106.58a105.534 105.534 0 0 1 89.339 48.065 106.578 106.578 0 0 1 89.338-48.065z" style="" fill="" data-original=""></path>
                                        <path class="icon__fill_stroke {{$product->isFavoritedByUser() ? 'active' : ''}}" d="M206.367 377.291a9.918 9.918 0 0 1-5.224-1.567C193.306 371.544 0 263.397 0 152.638 0 88.005 52.395 35.609 117.029 35.609a114.42 114.42 0 0 1 89.339 41.273C248.117 27.541 321.959 21.387 371.3 63.136a117.028 117.028 0 0 1 41.436 89.501c0 112.327-193.306 218.906-201.143 223.086a9.94 9.94 0 0 1-5.226 1.568zM117.029 56.507c-53.091 0-96.131 43.039-96.131 96.131 0 89.861 155.167 184.424 185.469 202.188 30.302-17.241 185.469-111.282 185.469-202.188.087-53.091-42.881-96.201-95.972-96.289a96.13 96.13 0 0 0-80.615 43.521c-3.557 4.905-10.418 5.998-15.323 2.44a10.968 10.968 0 0 1-2.44-2.44 94.041 94.041 0 0 0-80.457-43.363z" style="" fill="#000000" data-original="#083863" class="" opacity="1"></path></g>
                                    </svg>
                                </div>
                            @endauth
                            <div class="metka-product position-absolute">
                                <ul>
                                    @foreach($product->metkas as $metka)
                                        <li>{{get_languages_from_cookie()=="RUS" ? $metka->name_ru : $metka->name_kz}}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="js-slideshow-product-details slider-nav">
                                @foreach($product->gallery as $img)
                                    <div class="item"><img class="img-responsive" src="{{$img}}" alt=""></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6 details-prd-right">
                            <div class="quick-left">
                                <h3>{{get_languages_from_cookie()=="RUS" ? $product->name_ru : $product->name_kz}}</h3>
                              
                  
                                @if($product->shopProductsWeb[0]->variable == 'false')
                                <ul class="price pb-3" id="priceProduct">
                                    @if($product->shopProductsWeb[0]->discounted_price>0)
                                        <li>
                                            <div><del class="f12l16">{{$product->shopProductsWeb[0]->price.' '.get_valuta()}}</del></div>
                                            <div style="font-size: 16px;font-weight: 600;">{{$product->shopProductsWeb[0]->discounted_price.' '.get_valuta()}}</div>
                                        </li>
                                    @else
                                        <li style="font-size: 16px;font-weight: 600;">{{$product->shopProductsWeb[0]->price.' '.get_valuta()}}</li>
                                    @endif
                                    <li>{{get_languages_from_cookie()=="RUS" ? 'Артикул' : 'Артикул'}}: {{$product->article}} </li>
                                    
                                </ul>
                                @else
                                <ul class="price" id="priceProduct">
                                    @if($product->shopProductsWeb[0]->variations[0]['priceDiscount']>0)
                                        <li>
                                            <div><del class="f12l16">{{$product->shopProductsWeb[0]->variations[0]['price'].' '.get_valuta()}}</del></div>
                                            <div style="font-size: 16px;font-weight: 600;">{{$product->shopProductsWeb[0]->variations[0]['priceDiscount'].' '.get_valuta()}}</div>
                                        </li>
                                        <li>
                                        </li>
                                    @else
                                        <li style="font-size: 16px;font-weight: 600;">{{$product->shopProductsWeb[0]->variations[0]['price'].' '.get_valuta()}}</li>
                                    @endif
                                    <li>{{get_languages_from_cookie()=="RUS" ? 'Артикул' : 'Артикул'}}: {{$product->article}} </li>
                                    
                                </ul>
                                @endif
                                <div class="side-product mb-2 mt-3">
                                    <p class="my-0" style="text-transform: uppercase">{{get_languages_from_cookie()=="RUS" ? 'Бренд' : 'Бренд'}}: </p>
                                    <div class="brend">
                                        <ul style="display:block; margin-left: 10px; margin-top: 0;">
                                            <li class="mb-1"><a style="cursor: default;">{{get_languages_from_cookie()=="RUS" ? $product->brand->name_ru : $product->brand->name_kz}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                @foreach($product->attributes as $attribute)
                                    <div class="side-product mb-2">
                                        <p class="my-0" style="text-transform: uppercase">{{get_languages_from_cookie()=="RUS" ? $attribute['name_ru'] : $attribute['name_kz']}}: </p>
                                            @php
                                                $variant_result = '';
                                            @endphp
                                            <ul style="display:block; margin-left: 10px; margin-top: 0;">
                                                @foreach($attribute['variants'] as $variant)
                                                    @if(!$loop->last)
                                                        <li class="mb-1"><a style="cursor: default;">{{get_languages_from_cookie()=="RUS" ? $variant['name_ru'] : $variant['name_kz']}},</a></li>
                                                    @else
                                                        <li class="mb-1"><a style="cursor: default;">{{get_languages_from_cookie()=="RUS" ? $variant['name_ru'] : $variant['name_kz']}}</a></li>
                                                    @endif
                                                @endforeach
                                        
                                        </ul>
                                    </div>
                                @endforeach
                                
                              
                   
                                @if($product->shopProductsWeb)
                                <div class="item-ip select-custom mb-4">
                                    <select title="Pick a number" class="selectpicker">
                                        @foreach($product->shopProductsWeb as $shopProduct)
                                            @if($loop->first)
                                                <option value="{{$shopProduct->id}}" selected>{{get_languages_from_cookie()=="RUS" ? $shopProduct->shop->name_ru : $shopProduct->shop->name_kz}}</option>
                                            @else
                                                <option value="{{$shopProduct->id}}">{{get_languages_from_cookie()=="RUS" ? $shopProduct->shop->name_ru : $shopProduct->shop->name_kz}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                @if($product->shopProductsWeb[0]->variable == 'true')
                                    <div class="variants-product">
                                        <p>{{get_languages_from_cookie()=="RUS" ? $product->shopProductsWeb[0]->variations[0]['nameAttribute_ru'] : $product->shopProductsWeb[0]->variations[0]['nameAttribute_kz']}}</p>
                                        <ul>
                                            @foreach($product->shopProductsWeb[0]->variations as $key=>$variation)
                                                @if($loop->first)
                                                    <li class="active" data-key="{{$key}}">{{get_languages_from_cookie()=="RUS" ? $variation['name_ru'] : $variation['name_kz']}}</li>
                                                @else
                                                    <li data-key="{{$key}}">{{get_languages_from_cookie()=="RUS" ? $variation['name_ru'] : $variation['name_kz']}}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if($product->shopProductsWeb[0]->variable == 'true')
                                    @if($product->shopProductsWeb[0]->variations[0]['quantity']>0)
                                        <div class="side-product d-flex align-items-center" id="quantityProduct">
                                            <h4>Количество: </h4>
                                            <div class="quantity">
                                                <button type="button" class="quantity-right-plus btn btn-number js-plus" data-type="plus" data-field="">
                                                    <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                                </button>
                                                <input type="text" name="number" value="1" class="product_quantity_number js-number" disabled>
                                                <button type="button" class="quantity-left-minus btn btn-number js-minus" data-type="minus" data-field="" data-count="{{$product->shopProductsWeb[0]->variations[0]['quantity']}}">
                                                    <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="side-product" id="quantityProduct">
                                            <h4>Данный вариант товара отсутствует в данном магазине </h4>
                                        </div>
                                    @endif
                                @else
                                    @if($product->shopProductsWeb[0]['quantity']>0)
                                        <div class="side-product d-flex align-items-center" id="quantityProduct">
                                            <h4>Количество: </h4>
                                            <div class="quantity">
                                                <button type="button" class="quantity-right-plus btn btn-number js-plus" data-type="plus" data-field="">
                                                    <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                                </button>
                                                <input type="text" name="number" value="1" class="product_quantity_number js-number" disabled>
                                                <button type="button" class="quantity-left-minus btn btn-number js-minus" data-type="minus" data-field="" data-count="{{$product->shopProductsWeb[0]['quantity']}}">
                                                    <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="side-product" id="quantityProduct">
                                            <h4>Данный товар отсутствует в данном магазине. </h4>
                                        </div>
                                    @endif
                                @endif

                                @if($product->shopProductsWeb[0]->variable == 'true')
                                    @if($product->shopProductsWeb[0]->variations[0]['quantity']>0)
                                        <div class="add-to-bag">
                                            <button type="button" class="add">
                                                <span class="icon icon-ecommerce-basket"></span>
                                                <small class="line1"> </small>
                                                <span style="right: -15%; width: 100%;">{{get_languages_from_cookie()=="RUS" ? 'В корзину' : 'Себетке'}}</span>
                                            </button>
                                        </div>
                                    @else
                                        <div class="add-to-bag">
                                        </div>
                                    @endif
                                @else
                                    @if($product->shopProductsWeb[0]['quantity']>0)
                                        <div class="add-to-bag">
                                            <button type="button" class="add">
                                                <span class="icon icon-ecommerce-basket"></span>
                                                <small class="line1"> </small>
                                                <span style="right: -15%; width: 100%;">{{get_languages_from_cookie()=="RUS" ? 'В корзину' : 'Себетке'}}</span>
                                            </button>
                                        </div>
                                    @else
                                        <div class="add-to-bag">
                                        </div>
                                    @endif
                                @endif

                                <!-- <button type="button"><span class="pe-7s-like"></span></button> -->      
                            </div>
                        </div>
                    </div>
                    <div class="tab-details">
                        <div class="tab-ul">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#details" aria-controls="details" role="tab" data-toggle="tab" class="font-tab">{{get_languages_from_cookie()=="RUS" ? 'Описание' : 'Сипаттама'}}</a>
                                </li>
                                <li role="presentation">
                                    <a href="#reviews" aria-controls="reviews" role="tab" data-toggle="tab" class="font-tab">{{get_languages_from_cookie()=="RUS" ? 'Отзывы' : 'Пікірлер'}} </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tags" aria-controls="tags" role="tab" data-toggle="tab" class="font-tab">{{get_languages_from_cookie()=="RUS" ? 'Состав' : 'Құрамы'}} </a>
                                </li>
                                <li role="presentation">
                                    <a href="#faqs" aria-controls="faqs" role="tab" data-toggle="tab" class="font-tab">{{get_languages_from_cookie()=="RUS" ? 'Доп. информация' : 'Қосымша ақпарат'}} </a>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content ">
                            <div role="tabpanel" class="tab-pane fade in active" id="details">
                                <span>{{get_languages_from_cookie()=="RUS" ? $product->description_ru : $product->description_kz}}</span>
                                <!-- <span>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. 
                                    </span> -->
                                <!-- <p>Why do we use it?</p>
                                <span>Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</span> -->
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="reviews">
                                <div class="row row-md-reverse d-flex">
                                    <div class="col-md-9 col-12">
                                        @if ($product->reviews->count() == 0)
                                            <div class="empty_reviews">
                                                <span>Никто пока не оценил этот товар, но вы можете стать первым!</span>
                                            </div>
                                        @endif

                                        @auth('web')
                                        <div class="review_form my-4 d-none">
                                            <div class="row">
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label class="form-control-label" for="first_name">Ваше имя<span class="label-required">*</span> </label>
                                                    <input class="form-control" type="text" id="first_name" name="first_name" placeholder="" required="required" value="{{auth('web')->user()->first_name}}">
                                                </div>
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label class="form-control-label" for="user_email">E-mail<span class="label-required">*</span>  </label>
                                                    <input class="form-control" type="text" id="user_email" name="user_email" placeholder="" required="required" value="{{auth('web')->user()->email}}" disabled>
                                                </div>
                                                <div class="form-group col-sm-12">
                                                    <label class="form-control-label">Ваша оценка<span class="label-required">*</span></label>
                                                    <div class="rate-group-wrap d-flex gap-3">
                                                        <div class="rating-area">
                                                            <input type="radio" class="radio-rate" id="star-5" name="rating" value="5">
                                                            <label for="star-5" title="Отлично"></label>	
                                                            <input type="radio" class="radio-rate" id="star-4" name="rating" value="4">
                                                            <label for="star-4" title="Хорошо"></label>    
                                                            <input type="radio" class="radio-rate" id="star-3" name="rating" value="3">
                                                            <label for="star-3" title="Нормально"></label>  
                                                            <input type="radio" class="radio-rate" id="star-2" name="rating" value="2">
                                                            <label for="star-2" title="Плохо"></label>    
                                                            <input type="radio" class="radio-rate" id="star-1" name="rating" value="1">
                                                            <label for="star-1" title="Очень плохо"></label>
                                                        </div>
                                                        <div class="rate-group-wrap__text" id="rating-text"></div>
                                                    </div>
                                                </div>

                                                <div class="form-group comment-form__full col-sm-12">
                                                    <label class="form-control-label" for="comment">Комментарий <span class="label-required">*</span> </label>
                                                    <textarea class="form-control" rows="3" id="comment" name="comment" placeholder="" required=""></textarea>
                                                </div>

                                                <div class="comment-form__full col-sm-12">
                                                    <div class="text-danger form-errors"></div>
                                                </div>

                                                <div class="comment-form__full mt-4 col-sm-12">
                                                    <button class="btn newReviewSubmit">Оставить отзыв</button>
                                                </div>
                                            </div>
                                        </div>
                                        @endauth
                                        
                                        <div class="user_reviews">
                                        @foreach($product->reviews->sortByDesc('created_at') as $review)
                                            <div class="review_block">
                                                <div class="row">
                                                    <div class="review_header">
                                                        <p class="mb-0">{{\App\Models\User::find($review->user_id)->first_name}} <span class="f12l16 ms-2">{{$review->created_at->format('d-m-Y H:i')}}</span></p>
                                                        <p class="d-flex align-items-center">{{$review->rating}} <span class="star_rating"></span></p>
                                                    </div>

                                                    <div class="review_body mt-3">
                                                        <div class="comment">
                                                            <p style="color: #626262;"><strong style="color: #000">Комментарий: </strong> {{$review->text}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-12 mb-5">
                                        <div class="total_reviews m-auto">
                                            <div class="empty_reviews_block w-100">
                                                <div class="total_ratings mb-4">
                                                    @if ($product->reviews->count() > 0)
                                                        <span class="product_rating f24l28 fw500">{{number_format($product->reviews()->avg('rating'), 1)}}</span> 
                                                        <span class="star_rating f28l28"></span>
                                                    @else
                                                        <span class="product_rating f24l28 fw500">Нет отзывов</span>
                                                    @endif
                                                </div>

                                                @auth('web')
                                                    <button class="btn newReview w-100">Добавить отзыв</button>
                                                @endauth
                                                @guest('web')
                                                    <button class="btn newReview w-100 mb-3" disabled>Добавить отзыв</button>
                                                    <span class="f12l16">Чтобы добавить отзыв нужно быть авторизованным</span>
                                                @endguest
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tags">
                                <span>{{get_languages_from_cookie()=="RUS" ? $product->sostav_ru : $product->sostav_kz}}</span>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="faqs">
                                <span>{{get_languages_from_cookie()=="RUS" ? $product->dop_info_ru : $product->dop_info_kz}}</span>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="special related-product-v1">
    <div class="container">
        <h3>{{get_languages_from_cookie()=="RUS" ? 'С этим товаром часто покупают' :'Бұл тауармен бірге жиі сатып алады'}}</h3>
        <div class="home-2">
            @foreach($productsUnical as $product)
                @include('site.product.cart_product_often_buy',['product'=>$product])
            @endforeach
        </div>
    </div>
</section>
<!-- end/content-details-product -->

@endsection

@push('scripts')

<!-- отзывы -->
<script>
    $(document).ready(function() {
        const ratings = [
            'Отлично',
            'Хорошо',
            'Нормально',
            'Плохо',
            'Очень плохо'
        ];

        const radioRates = document.querySelectorAll('.radio-rate');
        const ratingText = document.getElementById('rating-text');

        radioRates.forEach((radio, index) => {
            radio.addEventListener('click', () => {
                ratingText.textContent = ratings[index];
            });
        });

        $('.newReview').on('click', function (e) {
            $('.review_form').removeClass('d-none');
        });

        $('.newReviewSubmit').on('click', function (e) {
            e.preventDefault();

            // first_name = $('.review_form #first_name').val();
            // email = $('.review_form #user_email').val();
            rating = $('.radio-rate:checked').val();
            comment = $('.review_form #comment').val();

            product_slug = $('.content-details-product').attr('data-product-slug');

            if (rating && comment) {
                $.ajax({
                    url: '/new_reviews_user',
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
                    },
                    data: {
                        'rating': rating,
                        'message': comment,
                        'product_slug': product_slug
                    },
                    success: function(data) {
                        if (data != 'unsuccess') {
                            console.log(data);
                            $('.comment-form__full .form-errors').text('')
                            $('.empty_reviews').remove();
                            $('.review_form').addClass('d-none');
                            $('.radio-rate:checked').prop('checked', false);
                            $('.review_form #comment').val('');

                            $('.user_reviews').prepend(`
                                <div class="review_block">
                                    <div class="row">
                                        <div class="review_header">
                                            <p class="mb-0">${data.review.user.first_name} <span class="f12l16 ms-2">Только что</span></p>
                                            <p class="d-flex align-items-center">${rating} <span class="star_rating"></span></p>
                                        </div>

                                        <div class="review_body mt-3">
                                            <div class="comment">
                                                <p style="color: #626262;"><strong style="color: #000">Комментарий: </strong> ${comment}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);

                            $('.total_ratings').empty();
                            $('.total_ratings').append(`
                                <span class="product_rating f24l28 fw500">${data.product_rating}</span> 
                                <span class="star_rating f28l28"></span>
                            `);

                            num_reviews = parseInt($('.count_reviews').text());
                            $('.count_reviews').text(num_reviews + 1);
                        }
                        else {
                        }
                    }
                }); 
            }
            else {
                $('.comment-form__full .form-errors').text('Пожалуйста, укажите обязательные поля!');
            }
        }); 
    });
</script>

<script>
    $(document).ready(function() {

        //Выбор магазина
        $(document).on('change', '.selectpicker', function(){
            var selectedId = parseInt($(this).val(), 10); 
            shop = product.shop_products_web.find(function(item) {
                return item.id === selectedId;
            });

           
            htmlVariant = ``;
            if(shop.variable=="true"){         
                htmlVariant+=`<p>${languagesJs=="RUS" ? shop.variations[0].nameAttribute_ru : shop.variations[0].nameAttribute_kz}</p>`;
                htmlVariant+=`<ul>`;
                $.each(shop.variations, function( index, value ) {
                    if(index==0){
                        htmlVariant+=`<li class="active" data-key="${index}">${languagesJs=="RUS" ? value.name_ru : value.name_kz}</li>`;
                    }else{
                        htmlVariant+=`<li data-key="${index}">${languagesJs=="RUS" ? value.name_ru : value.name_kz}</li>`;
                    }
                });
                htmlVariant+=`</ul>`;
                $('.variants-product').html(htmlVariant);
                priceUpdateVariant(shop.variations[0]);
            }else{
                $('.variants-product').html('');
                priceUpdate(shop);
            }
            quantityProduct(shop);

        });
       
         //Выбор варианта
        $(document).on('click','.variants-product ul li',function(){
            $(this).closest('ul').find('li').each(function(index) {
                $(this).removeClass('active');
            });
            $(this).addClass('active');

            var keyVariant = $(this).data('key');
            var arrayData = shop.variations[keyVariant];

            priceUpdateVariant(arrayData);
            quantityProduct(shop,keyVariant);

        });

        function priceUpdateVariant(shopVariantData){
            var htmlPrice = ``;
            if(shopVariantData.priceDiscount>0){
                htmlPrice+=`
                    <li>
                        <div><del class="f12l16">${shopVariantData.price + ' ' + valutaJs}</del></div>
                        <div style="font-size: 16px;font-weight: 600;">${shopVariantData.priceDiscount + ' ' + valutaJs}</div>
                    </li>`;
                htmlPrice+=`<li>${languagesJs=="RUS" ? 'Артикул' : 'Артикул'}: ${product.article} </li>`;
            }else{
                htmlPrice+=`<li style="font-size: 16px;font-weight: 600;">${shopVariantData.price + ' ' + valutaJs}</li>`;
                htmlPrice+=`<li>${languagesJs=="RUS" ? 'Артикул' : 'Артикул'}: ${product.article} </li>`;
            }

            $('#priceProduct').html(htmlPrice);
        }

        function priceUpdate(shopData){
            var htmlPrice = ``;
            if(shopData.discounted_price>0){
                htmlPrice+=`
                    <li>
                        <div><del class="f12l16">${shopData.price + ' ' + valutaJs}</del></div>
                        <div style="font-size: 16px;font-weight: 600;">${shopData.discounted_price + ' ' + valutaJs}</div>
                    </li>`;
                htmlPrice+=`<li>${languagesJs=="RUS" ? 'Артикул' : 'Артикул'}: ${product.article} </li>`;
            }else{
                htmlPrice+=`<li style="font-size: 16px;font-weight: 600;">${shopData.price + ' ' + valutaJs}</li>`;
                htmlPrice+=`<li>${languagesJs=="RUS" ? 'Артикул' : 'Артикул'}: ${product.article} </li>`;
            }

            $('#priceProduct').html(htmlPrice);
        }

        //обновление количества
        function quantityProduct(shopData,key=null){
            var htmlQuantity = ``;
            var active = false;
            const notAvailable = $('#not-available');

            if(shopData.variable=="true"){
                if(key==null){
                    if(shopData.variations[0].quantity>0){
                        notAvailable.css('display', 'none');
                        active = true;
                        htmlQuantity = `<h4>Количество: </h4>
                                        <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus" data-type="plus" data-field="">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="1" class="product_quantity_number js-number" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus" data-type="minus" data-field="" data-count="${shopData.variations[0].quantity}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>`;
                    }else{
                        htmlQuantity+=`<h4>Данный вариант товара отсутствует в данном магазине. </h4>`;
                        active = false;
                        notAvailable.css('display', 'block');
                    }
                }else{
                    if(shopData.variations[key].quantity>0){
                        notAvailable.css('display', 'none');
                        active = true;
                        htmlQuantity = `<h4>Количество: </h4>
                                        <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus" data-type="plus" data-field="">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="1" class="product_quantity_number js-number" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus" data-type="minus" data-field="" data-count="${shopData.variations[key].quantity}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>`;
                    }else{
                        htmlQuantity+=`<h4>Данный вариант товара отсутствует в данном магазине. </h4>`;
                        active = false;
                        notAvailable.css('display', 'block');
                    }
                }
            }else{
                if(shopData.quantity>0){
                    notAvailable.css('display', 'none');
                    active = true;
                    htmlQuantity = `<h4>Количество: </h4>
                                        <div class="quantity">
                                            <button type="button" class="quantity-right-plus btn btn-number js-plus" data-type="plus" data-field="">
                                                <span class="plus-icon"><i class="ion-ios-minus-empty"></i></span>
                                            </button>
                                            <input type="text" name="number" value="1" class="product_quantity_number js-number" disabled>
                                            <button type="button" class="quantity-left-minus btn btn-number js-minus" data-type="minus" data-field="" data-count="${shopData.quantity}">
                                                <span class="minus-icon"><i class="ion-ios-plus-empty"></i></span>
                                            </button>
                                        </div>`;
                }else{
                    htmlQuantity+=`<h4>Данный товара отсутствует в данном магазине. </h4>`;
                    active = false;
                    notAvailable.css('display', 'block');
                }
            }

            $('#quantityProduct').html(htmlQuantity);

            var addToBag = `<button type="button" class="add">
                                <span class="icon icon-ecommerce-basket"></span>
                                <small class="line1"> </small>
                                <span style="right: -15%; width: 100%;">${languagesJs=="RUS" ? 'В корзину' : 'Себетке'}</span>
                            </button>
                            `;

            if(active){
                $('.add-to-bag').html(addToBag);
                $('#quantityProduct .quantity input').val(1);
            }else{
                $('.add-to-bag').html('');
            }
        }

        $(document).on('click','.add-to-bag .add',function(){

            var shopProductIdCart = shop.id;
            var variableCart = shop.variable;
            var variationsCart = null;
            var quantityCart = $('.product_quantity_number.js-number').val();
            if(variableCart=='true'){
                var keyVariant = $('.variants-product ul li.active').data('key');
                variationsCart = shop.variations[keyVariant];
            }

            // console.log(shopProductIdCart);
            addToCart(shopProductIdCart,variableCart,variationsCart,quantityCart);
        });

    });

</script>


@endpush