<div class="d-flex flex-column">
    <div class="pull-right f20l24 fw400 mb-3">
        Сумма: {{get_cart_items_all_maney_not_discount()}} {{get_valuta()}}
    </div>
    <div class="pull-right f20l24 fw400 mb-3">
        Скидка: {{get_cart_items_all_discount()}} {{get_valuta()}}
    </div>
    @auth('web')
        <div class="pull-right f20l24 fw400 mb-3">
            Пользовательская скидка: {{get_cart_items_all_discount_users()}} {{get_valuta()}}
        </div>
    @endauth
    <div class="pull-right f20l24 fw400">
        Итого: {{get_cart_items_all_maney()}} {{get_valuta()}}
    </div>
</div>
