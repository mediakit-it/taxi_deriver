const csrfToken = $('meta[name="csrf-token"]').attr('content');

function addToCart(shopProductId, variable, variations, quantity) {
    updateCartView();
    $.ajax({
        url: '/cart/add',
        method: 'POST',
        data: {
            shopProductId: shopProductId,
            variable: variable,
            variations: variations,
            quantity: quantity,
            _token: csrfToken
        },
        success: function (response) {
            toastAlert(true, 'Товар добавлен в корзину');
            updateCartView(response.countCartItems, response.htmlCartItems);
        }
    });
}

function updateCartView(countCartIcon, htmlCartItems) {
    //pk
    $('.item-icon ul li.hover-cart-user a p').text(countCartIcon);
    $('li.hover-cart-user .submenu.mini-cart').html(htmlCartItems);
    //mob
    // $('.item-icon ul li.hover-cart-user a p').text(countCartIcon);
    $('li .submenu.mini-cart.overlay .overlay-content').html(htmlCartItems);
}

function updateCart(shopProductId, quantity) {
    $.ajax({
        url: '/cart/update',
        method: 'POST',
        data: {
            shopProductId: shopProductId,
            quantity: quantity,
            _token: csrfToken
        },
        success: function (response) {
            alert(response.message);
            console.log(response.cart);
        }
    });
}

function removeFromCart(shopProductId) {
    $.ajax({
        url: '/cart/remove',
        method: 'POST',
        data: {
            shopProductId: shopProductId,
            _token: csrfToken
        },
        success: function (response) {
            if (response.success) {
                toastAlert(true, 'Товар удален из корзины');
                console.log(response);
                updateCartView(response.countCartItems, response.htmlCartItems);
                if (window.location.href.indexOf('cart-order') > -1) {
                    $('#cartIshodItems').html(response.htmlIshodCartOrder);
                }
                if (window.location.href.indexOf('plasing-an-order') > -1) {
                    $('#plasing_result').html(response.htmlIshodPlasing);
                }
            } else {
                toastAlert(false, 'Ошибка удаления товара');
                updateCartView(response.countCartItems, response.htmlCartItems);
            }
        }
    });
}

function cartPlus(shopProductId) {
    $.ajax({
        url: '/cart/plus',
        method: 'POST',
        data: {
            shopProductId: shopProductId,
            _token: csrfToken
        },
        success: function (response) {
            alert(response.message);
            console.log(response.cart);
        }
    });
}

function cartMinus(shopProductId) {
    $.ajax({
        url: '/cart/minus',
        method: 'POST',
        data: {
            shopProductId: shopProductId,
            _token: csrfToken
        },
        success: function (response) {
            alert(response.message);
            console.log(response.cart);
        }
    });
}

function getCartCount() {
    $.ajax({
        url: '/cart/count',
        method: 'GET',
        success: function (response) {
            alert('Items in cart: ' + response.cartCount);
        }
    });
}



$(document).ready(function () {
    $(document).on('click', '.delete_from_cart', function () {
        var idCart = $(this).closest('.item.clearfix').data('product');
        $('.modalDeleteCartSubmit').data('id', idCart);
        $('#modalDeleteCart').modal('show');

        $('.submenu.mini-cart.overlay').removeClass('myNav');
    });

    $(document).on('click', '.modalDeleteCartClose', function () {
        $('#modalDeleteCart').modal('hide');
    });

    $(document).on('click', '.modalDeleteCartSubmit', function () {
        $('#modalDeleteCart').modal('hide');
        var itemId = $(this).data('id');
        console.log(itemId);
        removeFromCart(itemId);
    });
});
