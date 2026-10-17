@extends('site.master')

@section('title', 'История покупок')

@section('styles')
<style>
    .orders-wrapper {
        width: 100%;
        display: flex;
        gap: 5%;
    }
    .orders-left {
        width: 65%;
    }
    .orders-right {
        width: 30%;
    }
    .order_product__details {
        display: flex;
        gap: 10px;
        padding: 10px 5px;
        border-bottom: 1px solid #ddd;
    }
    .order_product__details .product_image {
        width: 70px;
        height: 70px;
    }
    .order_product__details .product_image img {
        max-width: 70px;
        height: 70px;
        object-fit: contain;
    }
    .order_product__details .product_name {
        max-width: 80%;
        line-height: 16px;
    }
    .total_price_div {
        padding: 10px 5px;
        border-bottom: 1px dashed #eee;
    }
    .rightside_block {
        padding: 8px 5px 10px;
        border-bottom: 1px solid #ddd;

    }

    .product_price,
    .product_count,
    .is_payed {
      text-align: right;
    }

    .product_cost {
      min-width: 140px;
    }

    .cancel_order {
        text-decoration: dashed underline;
        color: var(--secondary-color);
    }

    @media (max-width: 768px) {
        .orders-left {
            width: 100%;
        }
        .orders-right {
            width: 100%;
            gap: 15px;
        }
        .delivery_block {
            width: 58%;
        }
        .payment_block {
            width: 40%;
        }
        .rightside_block {
            border: 0;
        }
    }
    @media (max-width: 576px) {
        .delivery_block {
            width: 100%;
        }
        .payment_block {
            width: 100%;
        }
        .rightside_block {
            border-bottom: 1px solid #ddd;
        }
        .order_product__details {
            display: unset;
            gap: 0;
        }
        .product_cost {
          margin-top: 10px;
        }
    }

    .accordion {
  --bs-accordion-color: var(--bs-body-color);
  --bs-accordion-bg: var(--bs-body-bg);
  --bs-accordion-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, border-radius 0.15s ease;
  --bs-accordion-border-color: var(--bs-border-color);
  --bs-accordion-border-width: var(--bs-border-width);
  --bs-accordion-border-radius: var(--bs-border-radius);
  --bs-accordion-inner-border-radius: calc(var(--bs-border-radius) - (var(--bs-border-width)));
  --bs-accordion-btn-padding-x: 1.25rem;
  --bs-accordion-btn-padding-y: 1rem;
  --bs-accordion-btn-color: var(--bs-body-color);
  --bs-accordion-btn-bg: var(--bs-accordion-bg);
  --bs-accordion-btn-icon: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23212529' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M2 5L8 11L14 5'/%3e%3c/svg%3e");
  --bs-accordion-btn-icon-width: 1.25rem;
  --bs-accordion-btn-icon-transform: rotate(-180deg);
  --bs-accordion-btn-icon-transition: transform 0.2s ease-in-out;
  --bs-accordion-btn-active-icon: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23052c65' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M2 5L8 11L14 5'/%3e%3c/svg%3e");
  --bs-accordion-btn-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  --bs-accordion-body-padding-x: 1.25rem;
  --bs-accordion-body-padding-y: 1rem;
  --bs-accordion-active-color: var(--bs-primary-text-emphasis);
  --bs-accordion-active-bg: var(--bs-primary-bg-subtle);
}

.accordion-button {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  padding: var(--bs-accordion-btn-padding-y) var(--bs-accordion-btn-padding-x);
  font-size: 1rem;
  color: var(--bs-accordion-btn-color);
  text-align: left;
  background-color: var(--bs-accordion-btn-bg);
  border: 0;
  border-radius: 0;
  overflow-anchor: none;
  transition: var(--bs-accordion-transition);
}
@media (prefers-reduced-motion: reduce) {
  .accordion-button {
    transition: none;
  }
}
.accordion-button:not(.collapsed) {
  color: var(--bs-accordion-active-color);
  background-color: var(--bs-accordion-active-bg);
  box-shadow: inset 0 calc(-1 * var(--bs-accordion-border-width)) 0 var(--bs-accordion-border-color);
}
.accordion-button:not(.collapsed)::after {
  background-image: var(--bs-accordion-btn-active-icon);
  transform: var(--bs-accordion-btn-icon-transform);
}
.accordion-button::after {
  flex-shrink: 0;
  width: var(--bs-accordion-btn-icon-width);
  height: var(--bs-accordion-btn-icon-width);
  margin-left: auto;
  content: "";
  background-image: var(--bs-accordion-btn-icon);
  background-repeat: no-repeat;
  background-size: var(--bs-accordion-btn-icon-width);
  transition: var(--bs-accordion-btn-icon-transition);
}
@media (prefers-reduced-motion: reduce) {
  .accordion-button::after {
    transition: none;
  }
}
.accordion-button:hover {
  z-index: 2;
}
.accordion-button:focus {
  z-index: 3;
  outline: 0;
  box-shadow: var(--bs-accordion-btn-focus-box-shadow);
}

.accordion-header {
  margin-bottom: 0;
}

.accordion-item {
  color: var(--bs-accordion-color);
  background-color: var(--bs-accordion-bg);
  border: var(--bs-accordion-border-width) solid var(--bs-accordion-border-color);
}
.accordion-item:first-of-type {
  border-top-left-radius: var(--bs-accordion-border-radius);
  border-top-right-radius: var(--bs-accordion-border-radius);
}
.accordion-item:first-of-type > .accordion-header .accordion-button {
  border-top-left-radius: var(--bs-accordion-inner-border-radius);
  border-top-right-radius: var(--bs-accordion-inner-border-radius);
}
.accordion-item:not(:first-of-type) {
  border-top: 0;
}
.accordion-item:last-of-type {
  border-bottom-right-radius: var(--bs-accordion-border-radius);
  border-bottom-left-radius: var(--bs-accordion-border-radius);
}
.accordion-item:last-of-type > .accordion-header .accordion-button.collapsed {
  border-bottom-right-radius: var(--bs-accordion-inner-border-radius);
  border-bottom-left-radius: var(--bs-accordion-inner-border-radius);
}
.accordion-item:last-of-type > .accordion-collapse {
  border-bottom-right-radius: var(--bs-accordion-border-radius);
  border-bottom-left-radius: var(--bs-accordion-border-radius);
}

.accordion-body {
  padding: var(--bs-accordion-body-padding-y) var(--bs-accordion-body-padding-x);
}

.accordion-flush > .accordion-item {
  border-right: 0;
  border-left: 0;
  border-radius: 0;
}
.accordion-flush > .accordion-item:first-child {
  border-top: 0;
}
.accordion-flush > .accordion-item:last-child {
  border-bottom: 0;
}
.accordion-flush > .accordion-item > .accordion-header .accordion-button, .accordion-flush > .accordion-item > .accordion-header .accordion-button.collapsed {
  border-radius: 0;
}
.accordion-flush > .accordion-item > .accordion-collapse {
  border-radius: 0;
}

[data-bs-theme=dark] .accordion-button::after {
  --bs-accordion-btn-icon: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236ea8fe'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
  --bs-accordion-btn-active-icon: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236ea8fe'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

.circle-status {
  width: 10px;
  height: 10px;

  margin-left: 10px;
  border-radius: 50%;
}

.circle-status.obrabotka{
  background: #005e97;
}
.circle-status.otmena{
  background: red;
}
.circle-status.obrabotan{
  background: orange;
}
.circle-status.otdan {
  background: green;
}
</style>
@endsection

@section('content')
<main class="main__content_wrapper">
    <!-- Start product details section -->
    <section style="padding-bottom: 5rem !important;" class="section--padding mt-5 pt-0">
        <div class="container">
            <div class="accordion" id="accordionListOrders">
                @forelse($baskets as $key=>$basket)
                  <div class="accordion-item">
                      <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$key}}" aria-expanded="false" aria-controls="collapse{{$key}}" style="font-size: 16px;  border: 1px solid #ddd;">
                          Заказ #{{$basket->id}}
                          <span class="circle-status {{$basket->obrabotka}}" style=""></span>
                      </button>
                      </h2>
                      <div id="collapse{{$key}}" class="accordion-collapse collapse" data-bs-parent="#accordionListOrders">
                          <div class="accordion-body">
                              <div class="orders-wrapper flex-wrap flex-md-nowrap">
                                  <div class="orders-left">
                                      @foreach($basket->basketitems as $item)
                                          <div class="order_product__details">
                                              <div class="d-flex gap-3">
                                                  <div class="product_image">
                                                    @if($item->imgUrl)
                                                      <img src="{{$item->imgUrl}}" alt="product-img">
                                                    @else
                                                      <img src="{{asset('assetsSite/images/no_image.jpg')}}" alt="product-img">
                                                    @endif
                                                  </div>
                                                  <div>
                                                      <div class="product_name">
                                                      <p class="black-clr">
                                                          {{ get_languages_from_cookie() == "RUS" ? $item->product_name_ru : $item->product_name_kz }}
                                                          {{$item->isVariation == 1 ? (
                                                              get_languages_from_cookie() == "RUS" ? 
                                                              '('.$item->nameAttributeRu.': '.$item->nameVariationRu.')' : 
                                                              '('.$item->nameAttributeKz.': '.$item->nameVariationKz.')'
                                                          ) : ''}}
                                                      </p>
                                                      </div>
                                                      <div class="f12l16">Можно забрать в магазине: {{$item->shopMagazine['name_ru']}}({{$item->shopMagazine['country']}})</div>
                                                  </div>
                                              </div>
                                              <div class="product_cost ms-auto">
                                                  <div class="product_price f20l24 d-flex flex-column">
                                                      @if($item->discount>0)
                                                        <span class="f12l16 text-right"><del>{{$item->price}} {{get_valuta_user($basket->country)}}</del></span>
                                                        <span>{{$item->discount}} {{get_valuta_user($basket->country)}}</span>
                                                      @else
                                                        <span>{{$item->price}} {{get_valuta_user($basket->country)}}</span>
                                                      @endif
                                                  </div>
                                                  <div class="product_quantity f12l16 product_count">
                                                      <span>х {{$item->quantity}}{{get_languages_from_cookie() == "RUS" ? 'шт.':'шт.'}}</span>
                                                  </div>
                                                  <div class="is_payed">
                                                    @if ($item->no_payment == 1)
                                                      <span class="f12l16" style="color:green !important;">Оплачено</span>
                                                    @else
                                                      <span class="f12l16" style="color:red !important;">Не оплачено</span>
                                                    @endif
                                                  </div>
                                              </div>
                                          </div>
                                      @endforeach
                                      <div class="total_block">
                                          <div class="total_price_div d-flex justify-content-between">
                                              <span class="f16l20">Скидка:</span>
                                              <span class="f18l22">{{$basket->discount}} {{get_valuta_user($basket->country)}}</span>
                                          </div>
                                          <div class="total_price_div d-flex justify-content-between">
                                              <span class="f16l20">Скидка пользователя:</span>
                                              <span class="f18l22">{{$basket->discountUser}} {{get_valuta_user($basket->country)}}</span>
                                          </div>
                                          <div class="total_price_div d-flex justify-content-between">
                                              <span class="f16l20">Доставка:</span>
                                              <span class="f18l22">{{$basket->deliveryPrice}} {{get_valuta_user($basket->country)}}</span>
                                          </div>
                                          <div class="total_price_div d-flex justify-content-between">
                                              <span class="f16l20 fw600">Итого:</span>
                                              <span class="f18l22">{{$basket->fullPrice}} {{get_valuta_user($basket->country)}}</span>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="orders-right d-flex flex-column flex-sm-row flex-md-column">
                                      <div class="rightside_block order_status_block" data-id="$basket->id" data-url-update="">
                                          <div class="f20l24 fw60">
                                            Статус заказа:
                                            @if($basket->obrabotka == "obrabotka")
                                              <span class="f14l20 mt-4"> На обработке </sp>
                                            @elseif($basket->obrabotka == "obrabotan")
                                              <span class="f14l20 mt-4"> Обработан </sp>
                                            @elseif($basket->obrabotka == "otmena")
                                              <span class="f14l20 mt-4"> Отменен </sp>
                                            @else
                                              <span class="f14l20 mt-4"> Получен </sp>
                                            @endif
                                          </div>

                                          @if(get_languages_from_cookie() == "RUS" ? $basket->messageDostavka_ru != null : $basket->messageDostavka_kz != null)
                                            <div class="f20l24 fw60">
                                              Доп. информация:  <span class="f14l20 mt-4"> {{get_languages_from_cookie() == "RUS" ? $basket->messageDostavka_ru : $basket->messageDostavka_kz}} </sp>
                                            </div>
                                          @endif
                                          
                                      </div>
                                      <div class="rightside_block delivery_block">
                                          <div class="f20l24 fw600">
                                            Доставка:
                                            <span class="f14l20 mt-4">{{get_languages_from_cookie() == "RUS" ? $basket->nameDeliveryRu : $basket->nameDeliveryKz}}</span>
                                          </div>
                                          @if($basket->addressDelivery)
                                            <div class="f20l24 fw600 mt-4 mb-2">Адрес доставки:</div>

                                            <dt class="col-sm-6 px-0">Фамилия: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['first_name']}}</dd>

                                            <dt class="col-sm-6 px-0">Имя: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['name']}}</dd>

                                            <dt class="col-sm-6 px-0">Страна: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['country']}}</dd>

                                            <dt class="col-sm-6 px-0">Область: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['region']}}</dd>

                                            <dt class="col-sm-6 px-0">Город: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['city']}}</dd>

                                            <dt class="col-sm-6 px-0">Улица: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['street']}}</dd>

                                            <dt class="col-sm-6 px-0">Дом: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['home']}}</dd>

                                            <dt class="col-sm-6 px-0">Корпус: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['corpus'] ? $basket->addressDelivery['corpus'] : '-'}}</dd>

                                            <dt class="col-sm-6 px-0">Квартира: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['flat']}}</dd>
 
                                            <dt class="col-sm-6 px-0">Почтовый код: </dt>
                                            <dd class="col-sm-6 px-0">{{$basket->addressDelivery['postCode']}}</dd>
                                        @endif
                                          
                                      </div>
                                      <!-- <div class="rightside_block payment_block">
                                          <div class="f20l24 fw60">Оплата:</div>
                                          <p class="f14l20 mt-4">$basket->payment_type</p>
                                      </div> -->
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                @empty
                <div class="col-12 d-flex justify-content-center flex-column">
                  <svg xmlns="http://www.w3.org/2000/svg" class="m-auto" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="256" height="256" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><defs><linearGradient id="a" x1="256" x2="256" y1="488" y2="24" gradientUnits="userSpaceOnUse"><stop stop-opacity="1" stop-color="#e9a412" offset="0"></stop><stop stop-opacity="1" stop-color="#ffc637" offset="0.5"></stop><stop stop-opacity="1" stop-color="#eeef30" offset="1"></stop></linearGradient></defs><path d="M493.9 485.4a8 8 0 0 0 2.069-6.1l-24-272A8 8 0 0 0 464 200h-40v-24a56 56 0 0 0-112 0v24h-24V64a40.045 40.045 0 0 0-40-40H56a40.045 40.045 0 0 0-40 40v384a40.045 40.045 0 0 0 40 40h432a8 8 0 0 0 5.9-2.6ZM56 40h192a24.039 24.039 0 0 1 22.624 16H33.376A24.039 24.039 0 0 1 56 40Zm192 432H56a24.039 24.039 0 0 1-22.624-16h237.248A24.039 24.039 0 0 1 248 472Zm24-32H32V72h240Zm56-264a40 40 0 0 1 80 0v24h-80Zm-48.022 296A39.788 39.788 0 0 0 288 448V216h24v32a8 8 0 0 0 16 0v-32h80v32a8 8 0 0 0 16 0v-32h32.675l22.588 256ZM328 280a8 8 0 1 1-8-8 8 8 0 0 1 8 8Zm96 0a8 8 0 1 1-8-8 8 8 0 0 1 8 8Zm-224 24H72a24 24 0 0 0 0 48h144a8 8 0 0 0 0-16h-9.376a23.923 23.923 0 0 0-.321-16.846 24 24 0 0 0 3.074-45.245l14.37-71.85L229.766 176H248a8 8 0 0 0 0-16h-24a8 8 0 0 0-7.59 5.47L210.234 192H192a56 56 0 0 0-112 0H56a8 8 0 0 0-7.845 9.569l16 80A8 8 0 0 0 72 288h128a8 8 0 0 1 0 16ZM72 336a8 8 0 1 1 8-8 8.009 8.009 0 0 1-8 8Zm89.376 0H94.624a23.944 23.944 0 0 0 0-16h66.752a23.944 23.944 0 0 0 0 16ZM184 336a8 8 0 1 1 8-8 8.009 8.009 0 0 1-8 8ZM70.559 232l-4.8-24h16.582a55.824 55.824 0 0 0 8.065 16.454l.755 7.546Zm8 40-4.8-24h19l2.4 24ZM128 272h-16.76l-2.4-24H128Zm32.76 0H144v-24h19.16ZM136 232a40 40 0 1 1 40-40 40.045 40.045 0 0 1-40 40Zm57.441 40h-16.6l2.4-24h19Zm8-40h-20.6l.755-7.546A55.824 55.824 0 0 0 189.659 208h16.582Zm-43.784-53.657a8 8 0 0 1 0 11.314l-24 24a8 8 0 0 1-11.314 0l-8-8a8 8 0 0 1 11.314-11.314l2.343 2.344 18.343-18.344a8 8 0 0 1 11.314 0ZM256 344a8 8 0 1 1-8-8 8 8 0 0 1 8 8Z" style="fill:url(#a)" fill=""></path></g></svg>
                  <h2 class="text-center">Мы пока не получили от вас заказ, но вы всегда можете просмотреть наш каталог и выбрать то, что вам по душе.</h2>
                  <p class="text-center" style="font-size: 16px;"> У нас огромный выбор продукции!</p>
                  <a href="{{route('catalogAll')}}" class="btn btn-continue" style="margin: auto; font-size: 16px;">В каталог</a>
                </div>
                @endforelse
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        $('.cancel_order').on('click', function () {
            id = $(this).closest('.rightside_block.order_status_block').data('id');
            url = $(this).closest('.rightside_block.order_status_block').data('url-update');

            $.ajax({
					type: 'POST',
					url: url,
					headers: {
						'X-CSRF-TOKEN': "{{ csrf_token() }}"
					},
					success: function (data) {
						if(data == 'true'){
							location.reload();
						}else{
							toastAlert(false, 'Произошла ошибка')
						}
					},
					error: function (error) {
						console.log(error);
					}
				});
        });
    });

    $(document).ready(function() {
        $('[data-bs-toggle="collapse"]').on('click', function() {
            var $this = $(this);
            var $parent = $this.parents('.card');
            var $collapse = $this.attr('data-bs-target');
            $($collapse).collapse('toggle');
            $parent.find('.accordion-button').toggleClass('collapsed');
        });
    });

</script>
@endpush
