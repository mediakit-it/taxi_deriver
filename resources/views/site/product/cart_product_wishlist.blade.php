<div class="col-md-3 col-sm-6 col-xs-12 item prdHsId" data-product-id="{{$product->id}}"  data-product-name="{{$product->name}}">
    <div class="img-product">
        <a href="{{route('site.product',['slug'=>$product->slug])}}"><img class="img-responsive" src="{{$product->gallery ? $product->gallery[0] : ''}}" alt=""></a>
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
    </div>
    <div class="title">
        <h4 class="product-name"><a href="{{route('site.product',['slug'=>$product->slug])}}">{{get_languages_from_cookie()=="RUS" ? $product->name_ru : $product->name_kz}}</a></h4>
        <p><a href="#">{{get_languages_from_cookie()=="RUS" ? $product->brand->name_ru : $product->brand->name_kz}}</a></p>
        <!-- <ul>
            <li>
                <span class="pe-7s-star"></span>
                <span class="pe-7s-star"></span>
                <span class="pe-7s-star"></span>
                <span class="pe-7s-star"></span>
                <span class="pe-7s-star"></span>
            </li>
            <li>5 reviews</li>
            <li>Add your rating</li>
        </ul> -->
        <span class="prosuct-price">
            <b>Цена: {{get_price_range($product)}}</b>
            <!-- <b>300 {{get_valuta()}}</b>
            <del>250 {{get_valuta()}}</del> -->
        </span>
        <span class="sk">{{get_languages_from_cookie()=="RUS" ? 'Артикул' : 'Артикул'}}: {{$product->article}} </span>
        @php
            $description = get_languages_from_cookie() == "RUS" ? $product->description_ru : $product->description_kz;
            $processedDescription = get_new_replase_text($description, 100);
        @endphp

        <p class="title-list">
            <span>{{str_replace('<br />', '', nl2br(e($processedDescription))) }}</span>
        </p>
       
        <!-- <div class="share-product">
            <h4>share: </h4>
            <ul>
                <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                </li>
                <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                <li><a href="#"><i class="fa fa-tumblr" aria-hidden="true"></i></a></li>
            </ul>
        </div> -->
        <!-- <div class="icon-prd">
            <div class="add-to-bag">
                <button type="button" class="add">
                    <span class="icon icon-ecommerce-basket"></span>
                    <small class="line1"> </small>
                    <span class="txt-bag" style="right: -15%;">add to bag</span>
                </button>
                <button type="button"><span class="pe-7s-like"></span></button>
            </div>
        </div> -->
    </div>
</div>