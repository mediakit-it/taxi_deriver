@extends('site.master')

@section('styles')
    <style>
        .loader_block {
            display: none;
        }
        .loader_block.active {
            position: fixed;
            top: 0;
            left: 0;
            background-color: #00000039;
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            z-index: 123;
        }
        .loader {
            width: 50px;
            aspect-ratio: 1;
            border-radius: 50%;
            border: 8px solid #000;
            animation:
                l20-1 0.8s infinite linear alternate,
                l20-2 1.6s infinite linear;
        }
        @keyframes l20-1{
            0%    {clip-path: polygon(50% 50%,0       0,  50%   0%,  50%    0%, 50%    0%, 50%    0%, 50%    0% )}
            12.5% {clip-path: polygon(50% 50%,0       0,  50%   0%,  100%   0%, 100%   0%, 100%   0%, 100%   0% )}
            25%   {clip-path: polygon(50% 50%,0       0,  50%   0%,  100%   0%, 100% 100%, 100% 100%, 100% 100% )}
            50%   {clip-path: polygon(50% 50%,0       0,  50%   0%,  100%   0%, 100% 100%, 50%  100%, 0%   100% )}
            62.5% {clip-path: polygon(50% 50%,100%    0, 100%   0%,  100%   0%, 100% 100%, 50%  100%, 0%   100% )}
            75%   {clip-path: polygon(50% 50%,100% 100%, 100% 100%,  100% 100%, 100% 100%, 50%  100%, 0%   100% )}
            100%  {clip-path: polygon(50% 50%,50%  100%,  50% 100%,   50% 100%,  50% 100%, 50%  100%, 0%   100% )}
        }
        @keyframes l20-2{ 
            0%    {transform:scaleY(1)  rotate(0deg)}
            49.99%{transform:scaleY(1)  rotate(135deg)}
            50%   {transform:scaleY(-1) rotate(0deg)}
            100%  {transform:scaleY(-1) rotate(-135deg)}
        }
    </style>
@endsection

@section('content')

 <!-- banner -->
 <section class="banner-top-v1 banner-blog">
        <h3>{{get_languages_from_cookie()=="RUS" ? $category->name_ru : $category->name_kz}}</h3>
        <!-- <p>Lorem Ipsum is not simply random text.</p> -->
    </section>
    <!-- end/banner -->
    <!-- content-slidebar -->
    <section class="content-slidebar content-details-product">
        <div class="container">
            <div class="row">
                <div class="col-md-2">
                    <div class="left-slidebar">
                        <div class="options">
                            <h3>{{get_languages_from_cookie()=="RUS" ? 'Фильтр' : 'Сүзгі'}}</h3>
                            <div class="item">
                                <h4>{{get_languages_from_cookie()=="RUS" ? 'Бренд' : 'Бренд'}}: </h4>
                                <ul class="brand-list">
                                    @foreach($brands as $key => $brand)
                                        <li class="brand-item {{ $key >= 5 ? 'hidden' : '' }}"><a href="#" data-id="{{$brand->id}}">{{ get_languages_from_cookie() == 'RUS' ? $brand->name_ru : $brand->name_kz }}</a></li>
                                    @endforeach
                                </ul>
                                @if(count($brands) > 5)
                                <ul>
                                    <li><a href="#" id="toggle-more">{{get_languages_from_cookie() == 'RUS' ? 'Показать еще' : 'Тағы көрсету'}}</a></li>
                                </ul>
                                @endif
                            </div>
                            <div class="item">
                                <h4>{{get_languages_from_cookie()=="RUS" ? 'Магазины' : 'Магазины'}}: </h4>
                                <ul class="shop-list">
                                    @foreach($shops as $key => $shop)
                                        <li class="shop-item {{ $key >= 5 ? 'hidden' : '' }}"><a href="#" data-id="{{$shop->id}}">{{ get_languages_from_cookie() == 'RUS' ? $shop->name_ru : $shop->name_kz }}</a></li>
                                    @endforeach
                                </ul>
                                @if(count($shops) > 5)
                                <ul>
                                    <li><a href="#" id="toggle-shops">{{get_languages_from_cookie() == 'RUS' ? 'Показать еще' : 'Тағы көрсету'}}</a></li>
                                </ul>
                                @endif
                            </div>
                            @if(is_array($metkas) && !empty($metkas))
                                <div class="item">
                                    <h4>{{ get_languages_from_cookie() == "RUS" ? 'Метки' : 'Белгілер' }}: </h4>
                                    <ul class="metka-list">
                                        @foreach($metkas as $key => $metka)
                                            <li class="metka-item {{ $key >= 5 ? 'hidden' : '' }}">
                                                <a href="#" data-id="{{$metka->id}}">{{ get_languages_from_cookie() == "RUS" ? $metka->name_ru : $metka->name_kz }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if(count($metkas) > 5)
                                        <ul>
                                            <li><a href="#" id="toggle-metkas">{{ get_languages_from_cookie() == "RUS" ? 'Показать еще' : 'Тағы көрсету' }}</a></li>
                                        </ul>
                                    @endif
                                </div>
                            @endif
                            @if(!empty($filterAttributeVariant))
                                @foreach($filterAttributeVariant as $attribute)
                                    <div class="item">
                                        <h4>{{ get_languages_from_cookie() == "RUS" ? $attribute->name_ru : $attribute->name_kz }}: </h4>
                                        <ul class="attribute-list">
                                            @foreach($attribute->variants as $key => $variant)
                                                <li class="attribute-item {{ $key >= 5 ? 'hidden' : '' }}">
                                                    <a href="#" data-id="{{$variant->id}}">{{ get_languages_from_cookie() == "RUS" ? $variant->name_ru : $variant->name_kz }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        @if(count($attribute->variants) > 5)
                                            <ul>
                                                <li><a href="#" class="toggle-attributes">{{ get_languages_from_cookie() == "RUS" ? 'Показать еще' : 'Тағы көрсету' }}</a></li>
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div id="open-filters">
                        <i class="fa fa-filter"></i>
                        <i class="ion-close"></i>
                    </div>
                </div>
                <div class="col-md-10 list-product-right">
                    <div class="group-menu clearfix" style="margin-top: 10px; margin-bottom: 30px;">
                        <div class="col-md-4 col-xs-12">
                            <div class="filter colproduct" style="display: inline-block;">
                                <p>
                                    @if(get_languages_from_cookie() == 'RUS')
                                        Показано {{ $products->firstItem() ? $products->firstItem()!=null : '0' }} - {{ $products->lastItem()!=null ? $products->lastItem() : '0' }} из {{ $products->total() }} товаров
                                    @else
                                        Көрсетілген {{ $products->firstItem() ? $products->firstItem()!=null : '0' }} - {{ $products->lastItem()!=null ? $products->lastItem() : '0' }} тауардың {{ $products->total() }}
                                    @endif
                                </p>
                            </div>
                            
                        </div>
                        <div class="col-md-8 ordering">
                            <!-- <div class="new">
                                <p>{{get_languages_from_cookie()=="RUS" ? 'Сортировка' : 'Сұрыптау'}}</p>
                                <select title="Pick a number" class="selectpicker">
                                    <option>{{get_languages_from_cookie()=="RUS" ? 'Нет' : 'Сұрыптау'}}</option>
                                    <option>{{get_languages_from_cookie()=="RUS" ? 'Популярные' : 'Танымал'}}</option>
                                    <option>{{get_languages_from_cookie()=="RUS" ? 'Новинки' : 'Жаңа өнімдер'}}</option>
                                    <option>{{get_languages_from_cookie()=="RUS" ? 'Цена от' : 'Бағасынан начинаючы'}}</option>
                                    <option>{{get_languages_from_cookie()=="RUS" ? 'Цена до' : 'Бағасына дейін'}}</option>
                                </select>
                            </div> -->
                            <ul style="float: right;" class="clearfix">
                                <li class="icon-right list">
                                    <img class="img-ic" src="{{asset('assetsSite/images/list.png')}}" alt="">
                                    <img class="act-img" src="{{asset('assetsSite/images/list_box_active.png')}}" alt="">
                                </li>
                                <li class="icon-right col active">
                                    <img class="img-ic" src="{{asset('assetsSite/images/list_box.png')}}" alt="">
                                    <img class="act-img" src="{{asset('assetsSite/images/list_active.png')}}" alt="">
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div id="product-partial">
                        <div class="products products-sidebar">
                            <div class="row d-flex flex-wrap">
                                @foreach($products as $product)
                                    @include('site.product.cart_product_category',['product'=>$product])
                                @endforeach
                            </div>
                        </div>

                        <!-- Пагинация -->
                        {{$products->render('pagination.default') }}
                        <!-- <div class="pagination-prd">
                            <div class="pagination-container">
                                <nav class="pagination">
                                    <span class="prev"><a href="#" title=""><i class="fa fa-angle-left"></i></a></span>
                                    <p>Page: </p>
                                    <span class="page"><a href="#" title="">1</a></span>
                                    <span class="page"><a href="#" title="">2</a></span>
                                    <span class="next"><a href="#" title=""><i class="fa fa-angle-right"></i></a></span>
                                </nav>
                            </div>
                        </div> -->
                    </div>

                    <div class="loader_block">
                        <div class="loader"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end/content-slidebar -->

@endsection

@push('scripts')
<script>
   $(document).ready(function() {
    // Функция для инициализации фильтров при загрузке страницы
    function initializeFiltersFromURL() {
        var urlParams = new URLSearchParams(window.location.search);

        // Инициализация брендов
        if (urlParams.has('brandIds')) {
            var brandIds = urlParams.get('brandIds').split(',');
            brandIds.forEach(function(id) {
                $('ul.brand-list li a[data-id="' + id + '"]').parent('li').addClass('active');
            });
        }

        // Инициализация меток
        if (urlParams.has('metkaIds')) {
            var metkaIds = urlParams.get('metkaIds').split(',');
            metkaIds.forEach(function(id) {
                $('ul.metka-list li a[data-id="' + id + '"]').parent('li').addClass('active');
            });
        }

        // Инициализация вариантов атрибутов
        if (urlParams.has('selectedVariantIds')) {
            var selectedVariantIds = urlParams.get('selectedVariantIds').split(',');
            selectedVariantIds.forEach(function(id) {
                $('ul.attribute-list li a[data-id="' + id + '"]').parent('li').addClass('active');
            });
        }

        // Инициализация магазинов
        if (urlParams.has('shopId')) {
            var selectedShopIds = urlParams.get('shopId').split(',');
            selectedShopIds.forEach(function(id) {
                $('ul.shop-list li a[data-id="' + id + '"]').parent('li').addClass('active');
            });
        }
    }

    // Вызываем функцию инициализации фильтров при загрузке страницы
    initializeFiltersFromURL();

    $('#toggle-more').click(function(event) {
        event.preventDefault();

        // Toggle the hidden class on the hidden brand items
        $('.brand-item.hidden').toggleClass('hidden');

        // Check the current text of the link and switch it
        var isExpanded = $(this).text() === '{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}';
        if (isExpanded) {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Показать еще" : "Тағы көрсету" }}');
            // Hide all items except the first 5
            $('.brand-item').each(function(index) {
                if (index >= 5) {
                    $(this).addClass('hidden');
                }
            });
        } else {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}');
        }
    });

    //магазины
    $('#toggle-shops').click(function(event) {
        event.preventDefault();

        // Toggle the hidden class on the hidden metka items
        $('.shop-item.hidden').toggleClass('hidden');

        // Check the current text of the link and switch it
        var isExpanded = $(this).text() === '{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}';
        if (isExpanded) {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Показать еще" : "Тағы көрсету" }}');
            // Hide all items except the first 5
            $('.shop-item').each(function(index) {
                if (index >= 5) {
                    $(this).addClass('hidden');
                }
            });
        } else {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}');
        }
    });

    $('#toggle-metkas').click(function(event) {
        event.preventDefault();

        // Toggle the hidden class on the hidden metka items
        $('.metka-item.hidden').toggleClass('hidden');

        // Check the current text of the link and switch it
        var isExpanded = $(this).text() === '{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}';
        if (isExpanded) {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Показать еще" : "Тағы көрсету" }}');
            // Hide all items except the first 5
            $('.metka-item').each(function(index) {
                if (index >= 5) {
                    $(this).addClass('hidden');
                }
            });
        } else {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}');
        }
    });

    $('.toggle-attributes').click(function(event) {
        event.preventDefault();

        // Toggle the hidden class on the hidden attribute items within the same item block
        $(this).closest('.item').find('.attribute-item.hidden').toggleClass('hidden');

        // Check the current text of the link and switch it
        var isExpanded = $(this).text() === '{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}';
        if (isExpanded) {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Показать еще" : "Тағы көрсету" }}');
            // Hide all items except the first 5 within the same item block
            $(this).closest('.item').find('.attribute-item').each(function(index) {
                if (index >= 5) {
                    $(this).addClass('hidden');
                }
            });
        } else {
            $(this).text('{{ get_languages_from_cookie() == "RUS" ? "Скрыть" : "Жасыру" }}');
        }
    });

    // Обновляем URL
    function updateURL() {
        var urlParams = new URLSearchParams(window.location.search);
        var brandIds = $('ul.brand-list li.active a').map(function() {
            return $(this).data('id');
        }).get().join(',');

        var metkaIds = $('ul.metka-list li.active a').map(function() {
            return $(this).data('id');
        }).get().join(',');

        var selectedVariantIds = $('ul.attribute-list li.active a').map(function() {
            return $(this).data('id');
        }).get().join(',');

        var selectedShopIds = $('ul.shop-list li.active a').map(function() {
            return $(this).data('id');
        }).get().join(',');

        if (brandIds.length > 0) {
            urlParams.set('brandIds', brandIds);
        } else {
            urlParams.delete('brandIds');
        }

        if (metkaIds.length > 0) {
            urlParams.set('metkaIds', metkaIds);
        } else {
            urlParams.delete('metkaIds');
        }

        if (selectedVariantIds.length > 0) {
            urlParams.set('selectedVariantIds', selectedVariantIds);
        } else {
            urlParams.delete('selectedVariantIds');
        }

        if (selectedShopIds.length > 0) {
            urlParams.set('shopId', selectedShopIds);
        } else {
            urlParams.delete('shopId');
        }

        var newURL = window.location.pathname + '?' + urlParams.toString();
        history.pushState(null, null, newURL);
    }

    // Обработчики кликов по фильтрам
    $('ul.brand-list, ul.metka-list, ul.attribute-list, ul.shop-list').on('click', 'li a', function(e) {
        e.preventDefault(); // Предотвращаем переход по ссылке
        $(this).parent('li').toggleClass('active'); // Добавляем или удаляем класс 'active'
        updateURL(); // Обновляем URL
        updateProductList(); // Обновляем список товаров
    });

    // Обработчик изменения страницы пагинации
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        updateProductList(page); // Обновляем список товаров для выбранной страницы
    });

    // Функция для обновления списка товаров с учетом текущих фильтров и номера страницы
    function updateProductList(page = 1) {
        $('.loader_block').addClass('active');
        $('body').addClass('overflow-hidden');

        var urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', page);

        var url = window.location.pathname + '?' + urlParams.toString();

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#product-partial').html(response.products);

               // Обновление информации о количестве товаров
                var info = response.newInfoUpdate;
                var infoText = '';
                if ('{{get_languages_from_cookie()}}' == 'RUS') {
                    infoText = 'Показано ' + (info.firstItem ? info.firstItem : 0) + ' - ' + (info.lastItem ? info.lastItem : 0) + ' из ' + info.total + ' товаров';
                } else {
                    infoText = 'Көрсетілген ' + (info.firstItem ? info.firstItem : 0) + ' - ' + (info.lastItem ? info.lastItem : 0)  + ' тауардың ' + info.total;
                }
                $('.filter.colproduct p').text(infoText);
            },
            error: function(xhr) {
                console.log('Error:', xhr);
            },
            complete: function () {
                $('.loader_block').removeClass('active');
                $('body').removeClass('overflow-hidden');
            }
        });
    }
});

</script>

@endpush