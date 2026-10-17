<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ShopProduct;

class ProductDetailController extends Controller
{
    //-----рабочая без фильтров
    // public function productDetails($slug)
    // {


    // $country = get_country_from_cookie();
    // // Получаем соответствующие продукты для выбранных product_id
    // $product = Product::where('slug', $slug)
    //     ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user','variants.attribute','metkas'])
    //     ->first();


    // // Создаем пустой массив для хранения уникальных атрибутов с их вариантами
    // $uniqueAttributes = [];

    // // Проходимся по вариантам атрибутов продукта и группируем их по атрибутам
    // foreach ($product->variants as $variant) {
    //     // Проверяем, существует ли уже атрибут с таким ID в массиве $uniqueAttributes
    //     if (!isset($uniqueAttributes[$variant->attribute_id]) && $variant->attribute->haracteristic == 1) {
    //         // Если нет, создаем новый элемент массива с данными атрибута
    //         $uniqueAttributes[$variant->attribute_id] = [
    //             'id' => $variant->attribute_id,
    //             'name_ru' => $variant->attribute->name_ru,
    //             'name_kz' => $variant->attribute->name_kz,
    //             'slug' => $variant->attribute->slug,
    //             'filter' => $variant->attribute->filter,
    //             'haracteristic' => $variant->attribute->haracteristic,
    //             'order' => $variant->attribute->order,
    //             'variants' => [],
    //         ];
    //     }

    //     // Проверяем, существует ли уже вариант в массиве вариантов для данного атрибута
    //     if ($variant->attribute->haracteristic == 1 && !in_array($variant->name_ru, array_column($uniqueAttributes[$variant->attribute_id]['variants'], 'name_ru'))) {
    //         // Если нет, добавляем его в массив вариантов данного атрибута
    //         $uniqueAttributes[$variant->attribute_id]['variants'][] = [
    //             'id' => $variant->id,
    //             'name_ru' => $variant->name_ru,
    //             'name_kz' => $variant->name_kz,
    //             'order' => $variant->order,
    //         ];
    //     }
    // }

    // // Сортируем атрибуты и их варианты в соответствии с порядком
    // usort($uniqueAttributes, function ($a, $b) {
    //     return $a['order'] <=> $b['order'];
    // });

    // foreach ($uniqueAttributes as &$attribute) {
    //     usort($attribute['variants'], function ($a, $b) {
    //         return $a['order'] <=> $b['order'];
    //     });
    // }

    // // Преобразуем значения массива в индексированный массив
    // $uniqueAttributes = array_values($uniqueAttributes);

    // // Заменяем поле 'variants' в продукте на поле 'attributes' с уникальными атрибутами и их вариантами
    // $product->attributes = $uniqueAttributes;

    // // Удаляем поле 'variants', если оно больше не нужно
    // unset($product->variants);

    //  // Получение уникальных продуктов по стране
    //  $uniqueProductIds = ShopProduct::select('product_id')
    //     ->where('country', $country)
    //     ->distinct()
    //     ->inRandomOrder()
    //     ->pluck('product_id'); // Получаем только идентификаторы продуктов

    //     // Получение продуктов по идентификаторам
    //     $productsUnical = Product::whereIn('id', $uniqueProductIds)
    //         ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas'])
    //         ->take(7)
    //         ->get();
    // //  dd($uniqueAttributes);

    // // Проверяем, есть ли продукт в списке желаний пользователя
    // // $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();

    //     // dd($product);


    //     return view('site.product.product_details',['uniqueAttributes'=>$uniqueAttributes,'productsUnical'=>$productsUnical,'product'=>$product->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at'])]);
    // }
    //---конец рабочего

    public function productDetails($slug)
    {
        $country = get_country_from_cookie();

        // Получаем продукт по slug с предзагрузкой связанных данных
        $product = Product::query()->where('slug', $slug)
            ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'variants.attribute', 'metkas'])
            ->first();
        $shops = $product->shopProductsWeb;

        $arr = $shops->map(function ($item) {
            if ($item->variable == 'true'){
                $variations = $item->variations;
                $item->variations = json_decode(json_encode(array_values(array_filter($variations, function ($variation) {
                    return intval($variation['quantity']) > 0;
                }))), true);
            }
            return $item;
        })->values()->filter(function ($item) {
            if ($item->variable == 'false'){
                return true;
            }else{
                return count($item->variations) > 0;
            }
        })->values();

        $product->shopProductsWeb = $arr;

        // Создаем массив для хранения уникальных атрибутов с их вариантами
        $uniqueAttributes = [];

        foreach ($product->variants as $variant) {
            if (!isset($uniqueAttributes[$variant->attribute_id]) && $variant->attribute->haracteristic == 1) {
                $uniqueAttributes[$variant->attribute_id] = [
                    'id' => $variant->attribute_id,
                    'name_ru' => $variant->attribute->name_ru,
                    'name_kz' => $variant->attribute->name_kz,
                    'slug' => $variant->attribute->slug,
                    'filter' => $variant->attribute->filter,
                    'haracteristic' => $variant->attribute->haracteristic,
                    'order' => $variant->attribute->order,
                    'variants' => [],
                ];
            }

            if ($variant->attribute->haracteristic == 1 && !in_array($variant->name_ru, array_column($uniqueAttributes[$variant->attribute_id]['variants'], 'name_ru'))) {
                $uniqueAttributes[$variant->attribute_id]['variants'][] = [
                    'id' => $variant->id,
                    'name_ru' => $variant->name_ru,
                    'name_kz' => $variant->name_kz,
                    'order' => $variant->order,
                ];
            }
        }

        usort($uniqueAttributes, fn($a, $b) => $a['order'] <=> $b['order']);
        foreach ($uniqueAttributes as &$attribute) {
            usort($attribute['variants'], fn($a, $b) => $a['order'] <=> $b['order']);
        }
        $uniqueAttributes = array_values($uniqueAttributes);

        // Заменяем поле 'variants' на уникальные атрибуты
        $product->attributes = $uniqueAttributes;
        unset($product->variants);

        // // Получаем уникальные продукты для рекомендации
        // $uniqueProductIds = ShopProduct::select('product_id')
        //     ->where('country', $country)
        //     ->distinct()
        //     ->inRandomOrder()
        //     ->pluck('product_id');

        // $productsUnical = Product::whereIn('id', $uniqueProductIds)
        //     ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas'])
        //     ->take(7)
        //     ->get();

        // Уникальные продукты
        $uniqueProductIds = ShopProduct::select('product_id')
            ->where('country', $country)
            ->distinct()
            ->inRandomOrder()
            ->pluck('product_id');

        // Загружаем продукты с магазинами
        $productsUnical = Product::whereIn('id', $uniqueProductIds)
            ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas'])
            ->get()
            ->filter(function ($product) {
                // Оставляем только магазины с доступными продуктами
                $product->shopProductsWeb = $product->shopProductsWeb->filter(function ($shopProduct) {
                    if ($shopProduct->variable) {
                        // Фильтруем вариации
                        $shopProduct->variations = collect($shopProduct->variations)
                            ->filter(fn($variation) => $variation['quantity'] > 0)
                            ->values() // Сбрасываем ключи
                            ->toArray();

                        // Исключаем магазин, если все вариации недоступны
                        return !empty($shopProduct->variations);
                    }

                    // Проверка для товаров без вариаций
                    return $shopProduct->quantity > 0;
                });

                // Исключаем товар, если все магазины недоступны
                return $product->shopProductsWeb->isNotEmpty();
            })
            ->take(30);

        return view('site.product.product_details', [
            'uniqueAttributes' => $uniqueAttributes,
            'productsUnical' => $productsUnical,
            'product' => $product->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
        ]);
    }


    public function productDetailsShop($shopId, $slug)
    {
        $country = get_country_from_cookie();
        $shop = Shop::query()->where('id', $shopId)->first();
        // Получаем продукт по slug с предзагрузкой связанных данных
        $product = Product::query()->where('slug', $slug)
            ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'variants.attribute', 'metkas'])
            ->first();
        $shops = $product->shopProductsWeb;

        $arr = $shops->map(function ($item) {
            if ($item->variable == 'true'){
                $variations = $item->variations;
                $item->variations = json_decode(json_encode(array_values(array_filter($variations, function ($variation) {
                    return intval($variation['quantity']) > 0;
                }))), true);
            }
            return $item;
        })->values()->filter(function ($item) {
            if ($item->variable == 'false'){
                return true;
            }else{
                return count($item->variations) > 0;
            }
        })
            ->filter(function ($item) use ($shopId){
            return $item->shop_id === intval($shopId);
        })
            ->values();

        $product->shopProductsWeb = $arr;

        // Создаем массив для хранения уникальных атрибутов с их вариантами
        $uniqueAttributes = [];

        foreach ($product->variants as $variant) {
            if (!isset($uniqueAttributes[$variant->attribute_id]) && $variant->attribute->haracteristic == 1) {
                $uniqueAttributes[$variant->attribute_id] = [
                    'id' => $variant->attribute_id,
                    'name_ru' => $variant->attribute->name_ru,
                    'name_kz' => $variant->attribute->name_kz,
                    'slug' => $variant->attribute->slug,
                    'filter' => $variant->attribute->filter,
                    'haracteristic' => $variant->attribute->haracteristic,
                    'order' => $variant->attribute->order,
                    'variants' => [],
                ];
            }

            if ($variant->attribute->haracteristic == 1 && !in_array($variant->name_ru, array_column($uniqueAttributes[$variant->attribute_id]['variants'], 'name_ru'))) {
                $uniqueAttributes[$variant->attribute_id]['variants'][] = [
                    'id' => $variant->id,
                    'name_ru' => $variant->name_ru,
                    'name_kz' => $variant->name_kz,
                    'order' => $variant->order,
                ];
            }
        }

        usort($uniqueAttributes, fn($a, $b) => $a['order'] <=> $b['order']);
        foreach ($uniqueAttributes as &$attribute) {
            usort($attribute['variants'], fn($a, $b) => $a['order'] <=> $b['order']);
        }
        $uniqueAttributes = array_values($uniqueAttributes);

        // Заменяем поле 'variants' на уникальные атрибуты
        $product->attributes = $uniqueAttributes;
        unset($product->variants);

        // // Получаем уникальные продукты для рекомендации
        // $uniqueProductIds = ShopProduct::select('product_id')
        //     ->where('country', $country)
        //     ->distinct()
        //     ->inRandomOrder()
        //     ->pluck('product_id');

        // $productsUnical = Product::whereIn('id', $uniqueProductIds)
        //     ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas'])
        //     ->take(7)
        //     ->get();

        // Уникальные продукты
        $uniqueProductIds = ShopProduct::select('product_id')
            ->where('country', $country)
            ->distinct()
            ->inRandomOrder()
            ->pluck('product_id');

        // Загружаем продукты с магазинами
        $productsUnical = Product::whereIn('id', $uniqueProductIds)
            ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas'])
            ->get()
            ->filter(function ($product) {
                // Оставляем только магазины с доступными продуктами
                $product->shopProductsWeb = $product->shopProductsWeb->filter(function ($shopProduct) {
                    if ($shopProduct->variable) {
                        // Фильтруем вариации
                        $shopProduct->variations = collect($shopProduct->variations)
                            ->filter(fn($variation) => $variation['quantity'] > 0)
                            ->values() // Сбрасываем ключи
                            ->toArray();

                        // Исключаем магазин, если все вариации недоступны
                        return !empty($shopProduct->variations);
                    }

                    // Проверка для товаров без вариаций
                    return $shopProduct->quantity > 0;
                });

                // Исключаем товар, если все магазины недоступны
                return $product->shopProductsWeb->isNotEmpty();
            })
            ->take(30);

        return view('site.product.product_details_shop', [
            'uniqueAttributes' => $uniqueAttributes,
            'productsUnical' => $productsUnical,
            'shop' => $shop,
            'product' => $product->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
        ]);
    }

}
