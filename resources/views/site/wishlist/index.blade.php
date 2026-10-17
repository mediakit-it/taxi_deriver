@extends('site.master')

@section('content')

 <!-- banner -->
 <section class="banner-top-v1 banner-blog">
        <h3>{{get_languages_from_cookie()=="RUS" ? 'Избранное' : 'Избранное'}}</h3>
        <!-- <p>Lorem Ipsum is not simply random text.</p> -->
    </section>
    <!-- end/banner -->
    <!-- content-slidebar -->
    <section class="content-slidebar content-details-product">
        <div class="container">
            <div class="row">
                <div class="col-md-12 list-product-right">
                    <div id="product-partial">
                        <div class="products products-sidebar">
                            <div class="row">
                                @forelse($products as $product)
                                    @include('site.product.cart_product_wishlist',['product'=>$product])
                                @empty
                                   <center><p>Вы пока что ничего не добавили в избранное</p></center>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end/content-slidebar -->

@endsection

@push('scripts')


@endpush