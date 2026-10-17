<div class="products products-sidebar">
    <div class="row d-flex flex-wrap">
        @foreach($products as $product)
            @include('site.product.cart_product_category',['product'=>$product])
        @endforeach
    </div>
</div>

<!-- Пагинация -->
{{$products->render('pagination.default') }}
                        