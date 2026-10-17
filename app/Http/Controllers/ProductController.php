<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\Category;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Log;

use App\Models\Metka;
use App\Models\Brand;
use App\Models\Shop;
use App\Models\AttributeVariant;
use App\Models\Attribute;






use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{

    //получаем на главной странице 10 продуктов
    public function indexMain(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = Auth::user();
    
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $country = $user->country;
            $uniqueProductIds = ShopProduct::select('product_id')
                ->where('country', $country)
                ->distinct()
                ->inRandomOrder()
                // ->take(10)
                ->get();

            // Получаем соответствующие продукты для выбранных product_id
            // $products = Product::whereIn('id', $uniqueProductIds->pluck('product_id'))
            //     ->with(['shopProducts.shop','brand','category','metkas'])
            //     ->take(20)
            //     ->get();

            // Загружаем продукты с магазинами
            $products = Product::whereIn('id', $uniqueProductIds->pluck('product_id'))
            ->with(['shopProducts.shop', 'brand', 'category', 'metkas'])
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
                })->values();

                // Исключаем товар, если все магазины недоступны
                return $product->shopProductsWeb->isNotEmpty();
            })->take(20)->values();
            // $products = Product::where('country', $country)->take(10)->get();

             // Проверяем, есть ли продукт в списке желаний пользователя
            $products->each(function ($product) use ($user) {
                $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
            });

            return response()->json([
                'status' => 'success',
                'products' => $products->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }



    // public function getProduct($slug)
    // {
    //     // Получаем аутентифицированного пользователя по токену
    //     $user = Auth::user();

    //     // Если пользователь аутентифицирован, используем его страну для запроса магазинов
    //     if ($user) {
    //         $country = $user->country;
    //         // Получаем соответствующие продукты для выбранных product_id
    //         // $product = Product::where('slug', $slug)
    //         //     ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','variants.attribute','metkas'])
    //         //     ->first();

    //         $product = Product::where('slug', $slug)
    //         ->with([
    //             'shopProducts.shop',
    //             'brand',
    //             'category',
    //             'reviews.user',
    //             'variants.attribute',
    //             'metkas',
    //         ])
    //         ->first();

    //         $shops = $product->shopProducts;

    //     $arr = $shops->map(function ($item) {
    //         if ($item->variable == 'true'){
    //             $variations = $item->variations;
    //             $item->variations = json_decode(json_encode(array_values(array_filter($variations, function ($variation) {
    //                 return intval($variation['quantity']) > 0;
    //             }))), true);
    //         }
    //         return $item;
    //     })->values()->filter(function ($item) {
    //         if ($item->variable == 'false'){
    //             return true;
    //         }else{
    //             return count($item->variations) > 0;
    //         }
    //     })->values();

    //     $product->shop_products = $arr;


    //         // Создаем пустой массив для хранения уникальных атрибутов с их вариантами
    //         $uniqueAttributes = [];

    //         // Проходимся по вариантам атрибутов продукта и группируем их по атрибутам
    //         foreach ($product->variants as $variant) {
    //             // Проверяем, существует ли уже атрибут с таким ID в массиве $uniqueAttributes
    //             if (!isset($uniqueAttributes[$variant->attribute_id]) && $variant->attribute->haracteristic == 1) {
    //                 // Если нет, создаем новый элемент массива с данными атрибута
    //                 $uniqueAttributes[$variant->attribute_id] = [
    //                     'id' => $variant->attribute_id,
    //                     'name_ru' => $variant->attribute->name_ru,
    //                     'name_kz' => $variant->attribute->name_kz,
    //                     'slug' => $variant->attribute->slug,
    //                     'filter' => $variant->attribute->filter,
    //                     'haracteristic' => $variant->attribute->haracteristic,
    //                     'order' => $variant->attribute->order,
    //                     'variants' => [],
    //                 ];
    //             }
                
    //             // Проверяем, существует ли уже вариант в массиве вариантов для данного атрибута
    //             if ($variant->attribute->haracteristic == 1 && !in_array($variant->name_ru, array_column($uniqueAttributes[$variant->attribute_id]['variants'], 'name_ru'))) {
    //                 // Если нет, добавляем его в массив вариантов данного атрибута
    //                 $uniqueAttributes[$variant->attribute_id]['variants'][] = [
    //                     'id' => $variant->id,
    //                     'name_ru' => $variant->name_ru,
    //                     'name_kz' => $variant->name_kz,
    //                     'order' => $variant->order,
    //                 ];
    //             }
    //         }

    //         // Сортируем атрибуты и их варианты в соответствии с порядком
    //         usort($uniqueAttributes, function ($a, $b) {
    //             return $a['order'] <=> $b['order'];
    //         });

    //         foreach ($uniqueAttributes as &$attribute) {
    //             usort($attribute['variants'], function ($a, $b) {
    //                 return $a['order'] <=> $b['order'];
    //             });
    //         }

    //         // Преобразуем значения массива в индексированный массив
    //         $uniqueAttributes = array_values($uniqueAttributes);

    //         // Заменяем поле 'variants' в продукте на поле 'attributes' с уникальными атрибутами и их вариантами
    //         $product->attributes = $uniqueAttributes;

    //         // Удаляем поле 'variants', если оно больше не нужно
    //         unset($product->variants);


    //         // Проверяем, есть ли продукт в списке желаний пользователя
    //         $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();

    //         return response()->json([
    //             'status' => 'success',
    //             'product' => $product->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
    //         ]);
    //     } else {
    //         // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }
    // }

    // public function getProduct($slug)
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }

    //     $product = Product::where('slug', $slug)
    //         ->with([
    //             'shopProducts.shop',
    //             'brand',
    //             'category',
    //             'reviews.user',
    //             'variants.attribute',
    //             'metkas',
    //         ])
    //         ->first();

    //     if (!$product) {
    //         return response()->json(['error' => 'Product not found'], 404);
    //     }

    //     $filteredProducts = collect([$product])->filter(function ($product) {
    //         $product->shop_products = $product->shopProducts->filter(function ($shopProduct) {
    //             if ($shopProduct->variable === 'true') {
    //                 $shopProduct->variations = collect($shopProduct->variations)->filter(function ($variation) {
    //                     return (int)$variation['quantity'] > 0;
    //                 })->values();
    //                 return count($shopProduct->variations) > 0;
    //             } else {
    //                 return (int)$shopProduct->quantity > 0;
    //             }
    //         })->values();

    //         return count($product->shop_products) > 0;
    //     })->values();

    //     $filteredProducts = $filteredProducts->reject(function ($product) {
    //         $product->shop_products = $product->shop_products->reject(function ($shopProduct) {
    //             if ($shopProduct->variable === 'true') {
    //                 $shopProduct->variations = collect($shopProduct->variations)->reject(function ($variation) {
    //                     return (int)$variation['quantity'] <= 0;
    //                 })->values();
                    
        
    //                 // Оставляем только те магазины, у которых есть вариации
    //                 return count($shopProduct->variations) === 0;
    //             } else {
    //                 // Оставляем магазины, у которых количество больше 0
    //                 return (int)$shopProduct->quantity <= 0;
    //             }
    //         })->values();
        
    //         // Убираем продукт, если после фильтрации у него не осталось магазинов
    //         return count($product->shop_products) === 0;
    //     })->values();

        

        

    //     $filteredProducts->each(function ($product) {
    //         $product->shop_products->each(function ($shopProduct) {
    //             if (isset($shopProduct->variations) && is_array($shopProduct->variations)) {
    //                 $shopProduct->variations = array_values($shopProduct->variations);
    //             }
    //         });
    //     });

        

    //     $product = $filteredProducts->first();

        
    //     if (!$product) {
    //         return response()->json(['error' => 'No available products'], 404);
    //     }

    //     $uniqueAttributes = [];

    //     foreach ($product->variants as $variant) {
    //         if (!isset($uniqueAttributes[$variant->attribute_id]) && $variant->attribute->haracteristic == 1) {
    //             $uniqueAttributes[$variant->attribute_id] = [
    //                 'id' => $variant->attribute_id,
    //                 'name_ru' => $variant->attribute->name_ru,
    //                 'name_kz' => $variant->attribute->name_kz,
    //                 'slug' => $variant->attribute->slug,
    //                 'filter' => $variant->attribute->filter,
    //                 'haracteristic' => $variant->attribute->haracteristic,
    //                 'order' => $variant->attribute->order,
    //                 'variants' => [],
    //             ];
    //         }
            
    //         if ($variant->attribute->haracteristic == 1 && !in_array($variant->name_ru, array_column($uniqueAttributes[$variant->attribute_id]['variants'], 'name_ru'))) {
    //             $uniqueAttributes[$variant->attribute_id]['variants'][] = [
    //                 'id' => $variant->id,
    //                 'name_ru' => $variant->name_ru,
    //                 'name_kz' => $variant->name_kz,
    //                 'order' => $variant->order,
    //             ];
    //         }
    //     }

    //     usort($uniqueAttributes, fn($a, $b) => $a['order'] <=> $b['order']);

    //     foreach ($uniqueAttributes as &$attribute) {
    //         usort($attribute['variants'], fn($a, $b) => $a['order'] <=> $b['order']);
    //     }

    //     $product->attributes = array_values($uniqueAttributes);
    //     unset($product->variants);

    //     $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();

    //     return response()->json([
    //         'status' => 'success',
    //         'product' => $product->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
    //     ]);
    // }

    
    public function getProduct($slug)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    
        $product = Product::where('slug', $slug)
            ->with([
                'shopProducts.shop',
                'brand',
                'category',
                'reviews.user',
                'variants.attribute',
                'metkas',
            ])
            ->first();
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    
        // Фильтрация продуктов по магазинам и наличию товара
        $product->shop_products = $product->shopProducts->filter(function ($shopProduct) {
            if ($shopProduct->variable === 'true') {
                // Проверяем наличие вариаций
                if (isset($shopProduct->variations) && !empty($shopProduct->variations)) {
                    // Если variations строка в формате JSON, декодируем её
                    $variations = is_string($shopProduct->variations)
                        ? collect(json_decode($shopProduct->variations, true))
                        : collect($shopProduct->variations);
    
                    // Фильтруем вариации по количеству больше 0
                    $variations = $variations->filter(function ($variation) {
                        return (int)$variation['quantity'] > 0;
                    })->values();
    
                    // Если хотя бы одна вариация в наличии, добавляем товар в результат
                    if ($variations->isNotEmpty()) {
                        $shopProduct->variations = $variations;
                        return true;
                    }
                }
            } else {
                // Для не вариативных товаров проверяем наличие товара
                return (int)$shopProduct->quantity > 0;
            }
    
            return false; // Если ни одна из проверок не прошла, товар не добавляется
        })->values();
    
        // Если после фильтрации нет доступных товаров, возвращаем ошибку
        if ($product->shop_products->isEmpty()) {
            return response()->json([
                'status' => 'notProduct'
            ]);
        }
    
        // Обработка уникальных атрибутов товара
        $uniqueAttributes = [];
    
        foreach ($product->variants as $variant) {
            if ($variant->attribute->haracteristic == 1) {
                if (!isset($uniqueAttributes[$variant->attribute_id])) {
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
    
                if (!in_array($variant->name_ru, array_column($uniqueAttributes[$variant->attribute_id]['variants'], 'name_ru'))) {
                    $uniqueAttributes[$variant->attribute_id]['variants'][] = [
                        'id' => $variant->id,
                        'name_ru' => $variant->name_ru,
                        'name_kz' => $variant->name_kz,
                        'order' => $variant->order,
                    ];
                }
            }
        }
    
        // Сортировка атрибутов и вариантов по порядку
        usort($uniqueAttributes, fn($a, $b) => $a['order'] <=> $b['order']);
        foreach ($uniqueAttributes as &$attribute) {
            usort($attribute['variants'], fn($a, $b) => $a['order'] <=> $b['order']);
        }
    
        // Присваиваем отфильтрованные атрибуты продукту
        $product->attributes = array_values($uniqueAttributes);
        unset($product->variants); // Убираем исходный список вариаций из продукта
    
        // Проверка на наличие товара в списке желаемого
        $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    
        return response()->json([
            'status' => 'success',
            'product' => $product->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
        ]);
    }
    
    
    

    


    public function initializeData()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $country = $user->country;

        // Проверяем и получаем кэшированные данные
        $brands = Cache::remember("brands_{$country}", 3600, function() use ($country) {
            return $this->getBrands($country);
        });
        $metkas = Cache::remember("metkas_{$country}", 3600, function() use ($country) {
            return $this->getMetkas($country);
        });
        $shops = Cache::remember("shops_{$country}", 3600, function() use ($country) {
            return $this->getShops($country);
        });
        $filterAttributes = Cache::remember("filterAttributes_{$country}", 3600, function() use ($country) {
            return $this->getFilterAttributes($country);
        });
        $filterVariants = Cache::remember("filterVariants_{$country}", 3600, function() use ($country) {
            return $this->getFilterVariants($country);
        });

        return response()->json([
            'status' => 'success',
            'brands' => $brands,
            'metkas' => $metkas,
            'shops' => $shops,
            'filterAttributes' => $filterAttributes,
            'filterVariants' => $filterVariants,
        ]);
    }


    //раб
    public function getAllProduct(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $country = $user->country;
            $perPage = $request->input('perPage', 20); // Количество товаров на странице
            $page = $request->input('page', 1); // Номер текущей страницы

            // Получаем базовый запрос всех продуктов для страны
            // $productsQuery = Product::query()
            //     ->whereHas('shopProducts', function ($query) use ($country) {
            //         $query->where('country', $country);
            //     })->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas']);

            $productsQuery = Product::query()
            ->whereHas('shopProducts', function ($query) use ($country) {
                // Фильтруем по стране
                $query->where('country', $country);
            })
            ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user', 'metkas']);
        
        
            
            // Список всех продуктов для страны
            $products = $productsQuery->get();

        $filteredProducts = $products->filter(function ($product) {
            // Для каждого продукта фильтруем shopProducts
            $product->shop_products = $product->shopProducts->filter(function ($shopProduct) {
                // Если товар вариативный
                if ($shopProduct->variable === 'true') {
                    // Фильтруем вариации с quantity > 0
                    $shopProduct->variations = collect($shopProduct->variations)->filter(function ($variation) {
                        return (int)$variation['quantity'] > 0;
                    });

                    // Если после фильтрации остались вариации, оставляем товар
                    return count($shopProduct->variations) > 0 ;
                } else {
                    // Если товар не вариативный, проверяем его общее количество
                    return (int)$shopProduct->quantity > 0;
                }
            })->values();

            // Если для продукта остались shopProducts, возвращаем его
            return count($product->shopProducts)>0;
        })->values();

        $filteredProducts = $filteredProducts->reject(function ($product) {
            // Проходим по всем магазинам внутри товара
            $product->shopProducts = $product->shopProducts->reject(function ($shopProduct) {
                // Если товар вариативный
                if ($shopProduct->variable === 'true') {
                    // Фильтруем вариации с quantity > 0
                    $shopProduct->variations = collect($shopProduct->variations)->reject(function ($variation) {
                        // Исключаем вариации с количеством 0
                        return (int)$variation['quantity'] > 0;
                    });
        
                    // Если после фильтрации не осталось вариаций, исключаем магазин
                    return count($shopProduct->variations)>0;
                } else {
                    // Если товар не вариативный, проверяем его общее количество
                    return (int)$shopProduct->quantity > 0;
                }
            })->values();
        
            // Если после фильтрации для товара остались хотя бы 1 магазин, оставляем товар
            return count($product->shopProducts)>0;
        })->values();


        $filteredProducts->each(function ($product) {
            $product->shop_products->each(function ($shopProduct) {
                // Проверяем, если вариации существуют, преобразуем их
                if (isset($shopProduct->variations) && is_array($shopProduct->variations)) {
                    $shopProduct->variations = array_values($shopProduct->variations);  // Убираем числовые ключи
                }
            });
        });
        
        

            

            // Получаем уникальные бренды из продуктов
            $brandIds = $filteredProducts->pluck('brand_id')->unique();
            $brands = Brand::whereIn('id', $brandIds)->get();

            // Получаем уникальные метки из продуктов
            $metkaIds = $filteredProducts->pluck('metkas')->flatten()->pluck('id')->unique();
            $metkas = Metka::whereIn('id', $metkaIds)->get();

            // Получаем уникальные магазины из shopProducts
            $shopIds = $filteredProducts->pluck('shopProducts')->flatten()->pluck('shop_id')->unique();
            $shops = Shop::whereIn('id', $shopIds)->get();

            // Получаем уникальные атрибуты и их варианты из продуктов
            $variantIds = $filteredProducts->pluck('variants')->flatten()->pluck('id')->unique();
            $filterVariants = AttributeVariant::whereIn('id', $variantIds)->get();

            $attributeIds = $filterVariants->pluck('attribute_id')->unique();
            $filterAttributes = Attribute::whereIn('id', $attributeIds)->get();

            // Применяем фильтры
            $brandsFilter = $request->input('brandIds');
            if ($brandsFilter) {
                $brandsFilterArray = explode(',', $brandsFilter);
                $filteredProducts->whereIn('brand_id', $brandsFilterArray);
            }

            $metkasFilter = $request->input('metkaIds');
            if ($metkasFilter) {
                $metkasFilterArray = explode(',', $metkasFilter);
                $filteredProducts->whereHas('metkas', function ($query) use ($metkasFilterArray) {
                    $query->whereIn('metkas.id', $metkasFilterArray);
                });
            }

            $selectedVariantIds = $request->input('selectedVariantIds');
            if ($selectedVariantIds) {
                $filteredProducts->whereHas('variants', function ($query) use ($selectedVariantIds) {
                    $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
                });
            }

            $shopFilter = $request->input('shopId');
            if ($shopFilter) {
                $shopFilterArray = explode(',', $shopFilter);
                $filteredProducts->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
                    $query->whereIn('shop_id', $shopFilterArray);
                });
            }

            // Пагинация
            $totalProducts = $filteredProducts->count();
            $totalPages = ceil($totalProducts / $perPage);
            $offset = ($page - 1) * $perPage;

            $paginatedProducts = $filteredProducts->slice($offset,$perPage);
            // // Получаем продукты с учетом фильтров и пагинации
            // $paginatedProducts = $productsQuery
            //     ->offset($offset)
            //     ->limit($perPage)
            //     ->get();

            // Проверяем, есть ли продукт в списке желаний пользователя
            $paginatedProducts->each(function ($product) use ($user) {
                $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
            });

            return response()->json([
                'status' => 'success',
                'products' => $paginatedProducts,
                'brands' => $brands,
                'shops' => $shops,
                'metkas' => $metkas,
                'totalPages' => $totalPages,
                'filterAttributeVariant' => $filterAttributes->map(function ($attribute) use ($filterVariants) {
                    $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
                    $attribute['variants'] = $variants;
                    return $attribute;
                }),
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }



    // public function getAllProduct(Request $request)
    // {
    //     $user = Auth::user();
    //     if ($user) {
    //         $country = $user->country;
    //         $perPage = $request->input('perPage', 20); // Количество товаров на странице
    //         $page = $request->input('page', 1); // Номер текущей страницы
    
    //         // Проверяем кэш для данных
    //         $brands = Cache::get("brands_{$country}");
    //         $metkas = Cache::get("metkas_{$country}");
    //         $shops = Cache::get("shops_{$country}");
    //         $filterAttributes = Cache::get("filterAttributes_{$country}");
    //         $filterVariants = Cache::get("filterVariants_{$country}");
    
    //         // Если данных нет в кэше, инициализируем их
    //         if (!$brands || !$metkas || !$shops || !$filterAttributes || !$filterVariants) {
    //             $response = $this->initializeData();
    //             $brands = $response->original['brands'];
    //             $metkas = $response->original['metkas'];
    //             $shops = $response->original['shops'];
    //             $filterAttributes = $response->original['filterAttributes'];
    //             $filterVariants = $response->original['filterVariants'];
    //         }
    
    //         // Создаем запрос для продуктов с соответствующими связями
    //         $productsQuery = Product::query()
    //             ->whereHas('shopProducts', function ($query) use ($country) {
    //                 $query->where('country', $country);
    //             })
    //             ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas']);
                
    
    //         // Применяем фильтры
    //         $brandsFilter = $request->input('brandIds');
    //         if ($brandsFilter) {
    //             $brandsFilterArray = explode(',', $brandsFilter);
    //             $productsQuery->whereIn('brand_id', $brandsFilterArray);
    //         }
    
    //         $metkasFilter = $request->input('metkaIds');
    //         if ($metkasFilter) {
    //             $metkasFilterArray = explode(',', $metkasFilter);
    //             $productsQuery->whereHas('metkas', function ($query) use ($metkasFilterArray) {
    //                 $query->whereIn('metkas.id', $metkasFilterArray);
    //             });
    //         }
    
    //         $selectedVariantIds = $request->input('selectedVariantIds');
    //         if($selectedVariantIds){
    //             $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
    //                 $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
    //             });
    //         }
    
    //         $shopFilter = $request->input('shopId');
    //         if ($shopFilter) {
    //             $shopFilterArray = explode(',', $shopFilter);
    //             $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
    //                 $query->whereIn('shop_id', $shopFilterArray);
    //             });
    //         }
    
    //         // Получаем общее количество доступных товаров
    //         $totalProducts = $productsQuery->count();
    //         $totalPages = ceil($totalProducts / $perPage);
    //         $offset = ($page - 1) * $perPage;
    
    //         // Получаем продукты с учетом фильтров и пагинацией
    //         $products = $productsQuery
    //             ->offset($offset)
    //             ->limit($perPage)
    //             ->get();
    
    //         // Проверяем, есть ли продукт в списке желаний пользователя
    //         $products->each(function ($product) use ($user) {
    //             $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    //         });
    
    //         return response()->json([
    //             'status' => 'success',
    //             'products' => $products,
    //             'brands' => $brands,
    //             'shops' => $shops,
    //             'metkas' => $metkas,
    //             'totalPages' => $totalPages,
    //             'filterAttributeVariant' => $filterAttributes->map(function ($attribute) use ($filterVariants) {
    //                 $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
    //                 $attribute['variants'] = $variants;
    //                 return $attribute;
    //             }),
    //         ]);
    //     } else {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }
    // }

//     public function getAllProduct(Request $request)
// {
//     $user = Auth::user();
//     if ($user) {
//         $country = $user->country;
//         $perPage = $request->input('perPage', 20); // Количество товаров на странице
//         $page = $request->input('page', 1); // Номер текущей страницы

//         // Получаем базовый запрос всех продуктов для страны
//         $productsQuery = Product::query()
//             ->whereHas('shopProducts', function ($query) use ($country) {
//                 $query->where('country', $country);
//             })->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas']);

//         $availableProducts = collect();

//         foreach ($productsQuery->get() as $product) {
        
//             $availableShopProducts = collect();
        
//             foreach ($product->shopProducts as $shopProduct) {
        
//                 if ($shopProduct->variable == "true") {
//                     // Собираем доступные вариации
//                     $availableVariations = collect($shopProduct->variations)->filter(function ($variation) {
//                         return (int)$variation['quantity'] > 0;
//                     });
        
//                     \Log::info('Available variations:', $availableVariations->toArray());
        
//                     // Если есть доступные вариации, добавляем магазин
//                     if ($availableVariations->isNotEmpty()) {
//                         \Log::info('аааааа - '.$availableVariations->isNotEmpty());
//                         $shopProduct->variations = $availableVariations;
//                         $availableShopProducts->push($shopProduct);
//                     }else{
//                         \Log::info('bbbb - '.$availableVariations->isNotEmpty());
//                     }
//                 } else {
//                     // Если магазин не содержит вариаций, проверяем общее количество
//                     if ((int)$shopProduct->quantity > 0) {
//                         \Log::info("Adding shop product ID: {$shopProduct->id} with quantity: {$shopProduct->quantity}");
//                         $availableShopProducts->push($shopProduct);
//                     }
//                 }
//             }
        
//             \Log::info("Available shop products for product ID {$product->id}:", $availableShopProducts->toArray());
        
//             // Если есть доступные магазины, добавляем продукт
//             if ($availableShopProducts->isNotEmpty()) {
//                 $product->shop_products = $availableShopProducts;
//                 $availableProducts->push($product);
//             }else{
//                 $product->shop_products = collect();
//             }
//             \Log::info("Available shop products for product ID {$product->id}:", $product->shopProducts->toArray());


//         }
        
//         \Log::info('Final available products:', $availableProducts->toArray());
        
//         $products = $availableProducts;
        
        
        
        

//         // Получаем уникальные бренды из продуктов
//         $brandIds = $products->pluck('brand_id')->unique();
//         $brands = Brand::whereIn('id', $brandIds)->get();

//         // Получаем уникальные метки из продуктов
//         $metkaIds = $products->pluck('metkas')->flatten()->pluck('id')->unique();
//         $metkas = Metka::whereIn('id', $metkaIds)->get();

//         // Получаем уникальные магазины из shopProducts
//         $shopIds = $products->pluck('shopProducts')->flatten()->pluck('shop_id')->unique();
//         $shops = Shop::whereIn('id', $shopIds)->get();

//         // Получаем уникальные атрибуты и их варианты из продуктов
//         $variantIds = $products->pluck('variants')->flatten()->pluck('id')->unique();
//         $filterVariants = AttributeVariant::whereIn('id', $variantIds)->get();

//         $attributeIds = $filterVariants->pluck('attribute_id')->unique();
//         $filterAttributes = Attribute::whereIn('id', $attributeIds)->get();

//         // Применяем фильтры
//         $brandsFilter = $request->input('brandIds');
//         if ($brandsFilter) {
//             $brandsFilterArray = explode(',', $brandsFilter);
//             $products->whereIn('brand_id', $brandsFilterArray);
//         }

//         $metkasFilter = $request->input('metkaIds');
//         if ($metkasFilter) {
//             $metkasFilterArray = explode(',', $metkasFilter);
//             $products->whereHas('metkas', function ($query) use ($metkasFilterArray) {
//                 $query->whereIn('metkas.id', $metkasFilterArray);
//             });
//         }

//         $selectedVariantIds = $request->input('selectedVariantIds');
//         if ($selectedVariantIds) {
//             $products->whereHas('variants', function ($query) use ($selectedVariantIds) {
//                 $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
//             });
//         }

//         $shopFilter = $request->input('shopId');
//         if ($shopFilter) {
//             $shopFilterArray = explode(',', $shopFilter);
//             $products->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
//                 $query->whereIn('shop_id', $shopFilterArray);
//             });
//         }

//         // Пагинация
//         $totalProducts = $products->count();
//         $totalPages = ceil($totalProducts / $perPage);
//         $offset = ($page - 1) * $perPage;

//         // Получаем продукты с учетом фильтров и пагинации
//         $paginatedProducts = $products
//             ->offset($offset)
//             ->limit($perPage)
//             ->get();

//         // Проверяем, есть ли продукт в списке желаний пользователя
//         $paginatedProducts->each(function ($product) use ($user) {
//             $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
//         });

//         return response()->json([
//             'status' => 'success',
//             'products' => $paginatedProducts,
//             'brands' => $brands,
//             'shops' => $shops,
//             'metkas' => $metkas,
//             'totalPages' => $totalPages,
//             'filterAttributeVariant' => $filterAttributes->map(function ($attribute) use ($filterVariants) {
//                 $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
//                 $attribute['variants'] = $variants;
//                 return $attribute;
//             }),
//         ]);
//     } else {
//         return response()->json(['error' => 'Unauthenticated'], 401);
//     }
// }

    // public function getAllProduct(Request $request)
    // {
    //     $user = Auth::user();
    //     if ($user) {
    //         $country = $user->country;
    //         $perPage = $request->input('perPage', 20); // Количество товаров на странице
    //         $page = $request->input('page', 1); // Номер текущей страницы

    //         // Получаем базовый запрос всех продуктов для страны
    //         $productsQuery = Product::query()
    //             ->whereHas('shopProducts', function ($query) use ($country) {
    //                 $query->where('country', $country);
    //             })->with(['shopProducts.shop', 'brand', 'category', 'reviews.user', 'metkas']);

    //         $availableProducts = collect();

    //         foreach ($productsQuery->get() as $product) {
            
    //             $availableShopProducts = collect();
            
    //             foreach ($product->shopProducts as $shopProduct) {
            
    //                 if ($shopProduct->variable == "true") {
    //                     // Собираем доступные вариации
    //                     $availableVariations = collect($shopProduct->variations)->filter(function ($variation) {
    //                         return (int)$variation['quantity'] > 0;
    //                     });
            
            
    //                     // Если есть доступные вариации, добавляем магазин
    //                     if (count($availableVariations)>0) {
    //                         $shopProduct->variations = $availableVariations;
    //                         $availableShopProducts->push($shopProduct);
    //                     } else {
    //                     }
    //                 } else {
    //                     // Если магазин не содержит вариаций, проверяем общее количество
    //                     if ((int)$shopProduct->quantity > 0) {
    //                         $availableShopProducts->push($shopProduct);
    //                     }
    //                 }
    //             }
            
            
    //             // Если есть доступные магазины, добавляем продукт
    //             if ($availableShopProducts->isNotEmpty()) {
    //                 $product->shop_product = $availableShopProducts;
    //                 $availableProducts->push($product);
    //             }
    //         }
            
            
    //         $products = $availableProducts;

    //         // Получаем уникальные бренды из продуктов
    //         $brandIds = $products->pluck('brand_id')->unique();
    //         $brands = Brand::whereIn('id', $brandIds)->get();

    //         // Получаем уникальные метки из продуктов
    //         $metkaIds = $products->pluck('metkas')->flatten()->pluck('id')->unique();
    //         $metkas = Metka::whereIn('id', $metkaIds)->get();

    //         // Получаем уникальные магазины из shopProducts
    //         $shopIds = $products->pluck('shopProducts')->flatten()->pluck('shop_id')->unique();
    //         $shops = Shop::whereIn('id', $shopIds)->get();

    //         // Получаем уникальные атрибуты и их варианты из продуктов
    //         $variantIds = $products->pluck('variants')->flatten()->pluck('id')->unique();
    //         $filterVariants = AttributeVariant::whereIn('id', $variantIds)->get();

    //         $attributeIds = $filterVariants->pluck('attribute_id')->unique();
    //         $filterAttributes = Attribute::whereIn('id', $attributeIds)->get();

    //         // Применяем фильтры
    //         $brandsFilter = $request->input('brandIds');
    //         if ($brandsFilter) {
    //             $brandsFilterArray = explode(',', $brandsFilter);
    //             $products->whereIn('brand_id', $brandsFilterArray);
    //         }

    //         $metkasFilter = $request->input('metkaIds');
    //         if ($metkasFilter) {
    //             $metkasFilterArray = explode(',', $metkasFilter);
    //             $products->whereHas('metkas', function ($query) use ($metkasFilterArray) {
    //                 $query->whereIn('metkas.id', $metkasFilterArray);
    //             });
    //         }

    //         $selectedVariantIds = $request->input('selectedVariantIds');
    //         if ($selectedVariantIds) {
    //             $products->whereHas('variants', function ($query) use ($selectedVariantIds) {
    //                 $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
    //             });
    //         }

    //         $shopFilter = $request->input('shopId');
    //         if ($shopFilter) {
    //             $shopFilterArray = explode(',', $shopFilter);
    //             $products->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
    //                 $query->whereIn('shop_id', $shopFilterArray);
    //             });
    //         }

    //         // Пагинация на уровне запроса
    //         $totalProducts = $products->count();
    //         $totalPages = ceil($totalProducts / $perPage);
    //         $offset = ($page - 1) * $perPage;

    //         // // Получаем продукты с учетом фильтров и пагинации
    //         // $paginatedProducts = $productsQuery
    //         //     ->offset($offset)
    //         //     ->limit($perPage)
    //         //     ->get();

    //         $paginatedProducts = $products->slice($offset, $perPage);

    //         // Проверяем, есть ли продукт в списке желаний пользователя
    //         $paginatedProducts->each(function ($product) use ($user) {
    //             $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    //         });

    //         return response()->json([
    //             'status' => 'success',
    //             'products' => $paginatedProducts,
    //             'brands' => $brands,
    //             'shops' => $shops,
    //             'metkas' => $metkas,
    //             'totalPages' => $totalPages,
    //             'filterAttributeVariant' => $filterAttributes->map(function ($attribute) use ($filterVariants) {
    //                 $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
    //                 $attribute['variants'] = $variants;
    //                 return $attribute;
    //             }),
    //         ]);
    //     } else {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }
    // }

    // public function getAllProduct(Request $request)
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }

    //     $country = $user->country;
    //     $perPage = $request->input('perPage', 20); // Количество товаров на странице
    //     $page = $request->input('page', 1); // Номер текущей страницы

    //     // Получаем базовый запрос всех продуктов для страны
    //     $productsQuery = Product::query()
    //         ->whereHas('shopProducts', function ($query) use ($country) {
    //             $query->where('country', $country);
    //         })
    //         ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user', 'metkas']);

    //     $availableProducts = $productsQuery->get()->filter(function ($product) {
    //         $availableShopProducts = collect($product->shopProducts)->filter(function ($shopProduct) {
    //             if ($shopProduct->variable == "true") {
    //                 // Фильтруем доступные вариации
    //                 $availableVariations = collect($shopProduct->variations)->filter(function ($variation) {
    //                     return isset($variation['quantity']) && (int)$variation['quantity'] > 0;
    //                 });

    //                 if ($availableVariations->isNotEmpty()) {
    //                     $shopProduct->variations = $availableVariations;
    //                     return true;
    //                 }
    //             } else {
    //                 // Проверяем общее количество, если нет вариаций
    //                 return (int)$shopProduct->quantity > 0;
    //             }
    //             return false;
    //         })->values();

    //         if ($availableShopProducts->isNotEmpty()) {
    //             $product->shop_products = $availableShopProducts;
    //             return true;
    //         }
    //         return false;
    //     })->values();

    //     // Получаем уникальные данные для фильтров
    //     $brands = Brand::whereIn('id', $availableProducts->pluck('brand_id')->unique())->get();
    //     $metkas = Metka::whereIn('id', $availableProducts->pluck('metkas')->flatten()->pluck('id')->unique())->get();
    //     $shops = Shop::whereIn('id', $availableProducts->pluck('shopProducts')->flatten()->pluck('shop_id')->unique())->get();

    //     $filterVariants = AttributeVariant::whereIn(
    //         'id',
    //         $availableProducts->pluck('variants')->flatten()->pluck('id')->unique()
    //     )->get();

    //     $filterAttributes = Attribute::whereIn('id', $filterVariants->pluck('attribute_id')->unique())->get();

    //     // Применяем фильтры
    //     $products = $availableProducts;

    //     if ($brandIds = $request->input('brandIds')) {
    //         $brandIdsArray = explode(',', $brandIds);
    //         $products = $products->filter(function ($product) use ($brandIdsArray) {
    //             return in_array($product->brand_id, $brandIdsArray);
    //         });
    //     }

    //     if ($metkaIds = $request->input('metkaIds')) {
    //         $metkaIdsArray = explode(',', $metkaIds);
    //         $products = $products->filter(function ($product) use ($metkaIdsArray) {
    //             return $product->metkas->pluck('id')->intersect($metkaIdsArray)->isNotEmpty();
    //         });
    //     }

    //     if ($selectedVariantIds = $request->input('selectedVariantIds')) {
    //         $variantIdsArray = explode(',', $selectedVariantIds);
    //         $products = $products->filter(function ($product) use ($variantIdsArray) {
    //             return $product->variants->pluck('id')->intersect($variantIdsArray)->isNotEmpty();
    //         });
    //     }

    //     if ($shopIds = $request->input('shopId')) {
    //         $shopIdsArray = explode(',', $shopIds);
    //         $products = $products->filter(function ($product) use ($shopIdsArray) {
    //             return $product->shopProducts->pluck('shop_id')->intersect($shopIdsArray)->isNotEmpty();
    //         });
    //     }

    //     // Пагинация на уровне коллекции
    //     $totalProducts = $products->count();
    //     $totalPages = ceil($totalProducts / $perPage);
    //     $paginatedProducts = $products->slice(($page - 1) * $perPage, $perPage);

    //     // Проверяем, есть ли продукт в списке желаний пользователя
    //     $paginatedProducts->each(function ($product) use ($user) {
    //         $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    //     });

    //     return response()->json([
    //         'status' => 'success',
    //         'products' => $paginatedProducts->values(),
    //         'brands' => $brands,
    //         'shops' => $shops,
    //         'metkas' => $metkas,
    //         'totalPages' => $totalPages,
    //         'filterAttributeVariant' => $filterAttributes->map(function ($attribute) use ($filterVariants) {
    //             $attribute->variants = $filterVariants->where('attribute_id', $attribute->id)->values();
    //             return $attribute;
    //         }),
    //     ]);
    // }




    // public function getAllProduct(Request $request)
    // {
    //     $user = Auth::user();
    //     if ($user) {
    //         $country = $user->country;
    //         $perPage = $request->input('perPage', 20); // Количество товаров на странице
    //         $page = $request->input('page', 1); // Номер текущей страницы

    //         // Проверяем кэш для данных
    //         $brands = Cache::get("brands_{$country}");
    //         $metkas = Cache::get("metkas_{$country}");
    //         $shops = Cache::get("shops_{$country}");
    //         $filterAttributes = Cache::get("filterAttributes_{$country}");
    //         $filterVariants = Cache::get("filterVariants_{$country}");

    //         // Если данных нет в кэше, инициализируем их
    //         if (!$brands || !$metkas || !$shops || !$filterAttributes || !$filterVariants) {
    //             $response = $this->initializeData();
    //             $brands = $response->original['brands'];
    //             $metkas = $response->original['metkas'];
    //             $shops = $response->original['shops'];
    //             $filterAttributes = $response->original['filterAttributes'];
    //             $filterVariants = $response->original['filterVariants'];
    //         }

    //         // Создаем запрос для продуктов с соответствующими связями
    //         $productsQuery = Product::query()
    //             ->whereHas('shopProducts', function ($query) use ($country) {
    //                 $query->where('country', $country);
    //             })
    //             ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user', 'metkas']);

    //         // Применяем фильтры
    //         $brandsFilter = $request->input('brandIds');
    //         if ($brandsFilter) {
    //             $brandsFilterArray = explode(',', $brandsFilter);
    //             $productsQuery->whereIn('brand_id', $brandsFilterArray);
    //         }

    //         $metkasFilter = $request->input('metkaIds');
    //         if ($metkasFilter) {
    //             $metkasFilterArray = explode(',', $metkasFilter);
    //             $productsQuery->whereHas('metkas', function ($query) use ($metkasFilterArray) {
    //                 $query->whereIn('metkas.id', $metkasFilterArray);
    //             });
    //         }

    //         $selectedVariantIds = $request->input('selectedVariantIds');
    //         if ($selectedVariantIds) {
    //             $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
    //                 $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
    //             });
    //         }

    //         $shopFilter = $request->input('shopId');
    //         if ($shopFilter) {
    //             $shopFilterArray = explode(',', $shopFilter);
    //             $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
    //                 $query->whereIn('shop_id', $shopFilterArray);
    //             });
    //         }

    //         // Получаем продукты с учетом фильтров и пагинацией
    //         $products = $productsQuery->get()
    //             ->filter(function ($product) {
    //                 // Оставляем только магазины с доступными продуктами
    //                 $product->shopProductsWeb = $product->shopProductsWeb->filter(function ($shopProduct) {
    //                     if ($shopProduct->variable) {
    //                         // Фильтруем вариации
    //                         $shopProduct->variations = collect($shopProduct->variations)
    //                             ->filter(fn($variation) => $variation['quantity'] > 0)
    //                             ->values() // Сбрасываем ключи
    //                             ->toArray();

    //                         // Исключаем магазин, если все вариации недоступны
    //                         return !empty($shopProduct->variations);
    //                     }

    //                     // Проверка для товаров без вариаций
    //                     return $shopProduct->quantity > 0;
    //                 });

    //                 // Исключаем товар, если все магазины недоступны
    //                 return $product->shopProductsWeb->isNotEmpty();
    //             });

    //         // Пагинация вручную
    //         $totalProducts = $products->count();
    //         $totalPages = ceil($totalProducts / $perPage);
    //         $paginatedProducts = $products->slice(($page - 1) * $perPage, $perPage)->values();

    //         // Проверяем, есть ли продукт в списке желаний пользователя
    //         $paginatedProducts->each(function ($product) use ($user) {
    //             $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    //         });

    //         return response()->json([
    //             'status' => 'success',
    //             'products' => $paginatedProducts,
    //             'brands' => $brands,
    //             'shops' => $shops,
    //             'metkas' => $metkas,
    //             'totalPages' => $totalPages,
    //             'filterAttributeVariant' => $filterAttributes->map(function ($attribute) use ($filterVariants) {
    //                 $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
    //                 $attribute['variants'] = $variants;
    //                 return $attribute;
    //             }),
    //         ]);
    //     } else {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }
    // }


    // public function getAllProduct(Request $request)
    // {
    //     $user = Auth::user();
    //     if ($user) {
    //         $country = $user->country;
    //         $perPage = $request->input('perPage', 10); // Количество товаров на странице
    //         $page = $request->input('page', 1); // Номер текущей страницы

    //         // Создаем запрос для продуктов с соответствующими связями
    //         $productsQuery = Product::query()
    //             ->whereHas('shopProducts', function ($query) use ($country) {
    //                 $query->where('country', $country);
    //             })
    //             ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas']);

           
    //         //фильтр
    //         $brandsFilter = $request->input('brandIds');

    //         // Применяем фильтр по брендам, если он был отправлен
    //         if ($brandsFilter) {
    //             $brandsFilterArray = explode(',', $brandsFilter);
    //             $productsQuery->whereIn('brand_id', $brandsFilterArray);
    //         }

    //         $metkasFilter = $request->input('metkaIds');

    //         // Применяем фильтр по брендам, если он был отправлен
    //         if ($metkasFilter) {
    //             $metkasFilterArray = explode(',', $metkasFilter);
    //             $productsQuery->whereHas('metkas', function ($query) use ($metkasFilterArray) {
    //                 $query->whereIn('metkas.id', $metkasFilterArray);
    //             });
    //         }

    //         $selectedVariantIds = $request->input('selectedVariantIds');

    //         if($selectedVariantIds){
    //             $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
    //                 // dd($query);
    //                 $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
    //             });
    //         }

    //         // Фильтр по магазинам
    //         $shopFilter = $request->input('shopId');
    //         if ($shopFilter) {
    //             $shopFilterArray = explode(',', $shopFilter);
    //             $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
    //                 $query->whereIn('shop_id', $shopFilterArray);
    //             });
    //         }

    //         // Получаем общее количество доступных товаров для данной страны
    //         $totalProducts = $productsQuery->count();
            
    //         // Вычисляем общее количество страниц
    //         $totalPages = ceil($totalProducts / $perPage);
            
    //         // Вычисляем смещение для пропуска записей в зависимости от текущей страницы и количества товаров на странице
    //         $offset = ($page - 1) * $perPage;

    //         // Получаем продукты с учетом фильтров и пагинацией
    //         $products = $productsQuery
    //             ->offset($offset)
    //             ->limit($perPage)
    //             ->get();

    //         // Получаем список брендов
    //         //фильтр
    //         $allUniqueProductIds = ShopProduct::select('product_id')
    //         ->where('country', $country)
    //         ->distinct()
    //         ->get();

    //         $allProducts = Product::whereIn('id', $allUniqueProductIds->pluck('product_id'))
    //             ->with(['brand','attributes','variants','metkas'])
    //             ->get();

    //         $brands = $allProducts->pluck('brand')->unique('id')->values()->all();

    //         $metkas = $allProducts->pluck('metkas')
    //         ->flatten(1)
    //         ->unique('id')
    //         ->map(function ($metka) {
    //             return $metka->makeHidden(['pivot']);
    //         })
    //         ->values()
    //         ->all();

    //         $shops = $allProducts->pluck('shopProducts')->flatten(1)->pluck('shop')->unique('id')->values()->all();
           

    //         // Получаем все атрибуты и варианты из $allProducts
    //         $filterAttributes = collect($allProducts)->pluck('attributes')->flatten()->unique('id');
    //         $filterVariants = collect($allProducts)->pluck('variants')->flatten()->unique('id');

    //         // Создаем пустую коллекцию для хранения объединенных данных
    //         $mergedData = new Collection();

    //         // Обходим все атрибуты
    //         $filterAttributes->each(function ($attribute) use ($filterVariants, $mergedData) {
    //             // Получаем варианты атрибута
    //             $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
                
    //             // Добавляем варианты к атрибуту
    //             $attribute['variants'] = $variants;
                
    //             // Добавляем атрибут и его варианты к объединенным данным
    //             $mergedData->push($attribute);
    //         });

    //         // Проверяем, есть ли продукт в списке желаний пользователя
    //         $products->each(function ($product) use ($user) {
    //             $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    //         });

    //         return response()->json([
    //             'status' => 'success',
    //             'products' => $products,
    //             'brands' => $brands,
    //             'shops' => $shops,
    //             'metkas' => $metkas,
    //             'totalPages' => $totalPages,
    //             'filterAttributeVariant' => $mergedData,
    //         ]);
    //     } else {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }
    // }

    public function getAllProductCategory($slug,Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $country = $user->country;
            $category = Category::where('slug',$slug)->first();
            $perPage = $request->input('perPage', 10); // Количество товаров на странице
            $page = $request->input('page', 1); // Номер текущей страницы

            // Создаем запрос для продуктов с соответствующими связями
            $productsQuery = Product::query()
                ->whereHas('shopProducts', function ($query) use ($country) {
                    $query->where('country', $country);
                })
                ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas'])
                ->where('category_id',$category->id);

           
            //фильтр
            $brandsFilter = $request->input('brandIds');

            // Применяем фильтр по брендам, если он был отправлен
            if ($brandsFilter) {
                $brandsFilterArray = explode(',', $brandsFilter);
                $productsQuery->whereIn('brand_id', $brandsFilterArray);
            }

            $metkasFilter = $request->input('metkaIds');

            // Применяем фильтр по брендам, если он был отправлен
            if ($metkasFilter) {
                $metkasFilterArray = explode(',', $metkasFilter);
                $productsQuery->whereHas('metkas', function ($query) use ($metkasFilterArray) {
                    $query->whereIn('metkas.id', $metkasFilterArray);
                });
            }

            $selectedVariantIds = $request->input('selectedVariantIds');

            if($selectedVariantIds){
                $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
                    // dd($query);
                    $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
                });
            }

            // Фильтр по магазинам
            $shopFilter = $request->input('shopId');
            if ($shopFilter) {
                $shopFilterArray = explode(',', $shopFilter);
                $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
                    $query->whereIn('shop_id', $shopFilterArray);
                });
            }

            // Получаем общее количество доступных товаров для данной страны
            $totalProducts = $productsQuery->count();
            
            // Вычисляем общее количество страниц
            $totalPages = ceil($totalProducts / $perPage);
            
            // Вычисляем смещение для пропуска записей в зависимости от текущей страницы и количества товаров на странице
            $offset = ($page - 1) * $perPage;

            // Получаем продукты с учетом фильтров и пагинацией
            $products = $productsQuery
                ->offset($offset)
                ->limit($perPage)
                ->get();

            // Получаем список брендов
            //фильтр
            $allUniqueProductIds = ShopProduct::select('product_id')
            ->where('country', $country)
            ->distinct()
            ->get();

            $allProducts = Product::whereIn('id', $allUniqueProductIds->pluck('product_id'))
                ->with(['brand','attributes','variants','metkas'])
                ->where('category_id',$category->id)
                ->get();

            $brands = $allProducts->pluck('brand')->unique('id')->values()->all();


            $metkas = $allProducts->pluck('metkas')
            ->flatten(1)
            ->unique('id')
            ->map(function ($metka) {
                return $metka->makeHidden(['pivot']);
            })
            ->values()
            ->all();

            $shops = $allProducts->pluck('shopProducts')->flatten(1)->pluck('shop')->unique('id')->values()->all();
           

            // Получаем все атрибуты и варианты из $allProducts
            $filterAttributes = collect($allProducts)->pluck('attributes')->flatten()->unique('id');
            $filterVariants = collect($allProducts)->pluck('variants')->flatten()->unique('id');

            // Создаем пустую коллекцию для хранения объединенных данных
            $mergedData = new Collection();

            // Обходим все атрибуты
            $filterAttributes->each(function ($attribute) use ($filterVariants, $mergedData) {
                // Получаем варианты атрибута
                $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
                
                // Добавляем варианты к атрибуту
                $attribute['variants'] = $variants;
                
                // Добавляем атрибут и его варианты к объединенным данным
                $mergedData->push($attribute);
            });

            // Проверяем, есть ли продукт в списке желаний пользователя
            $products->each(function ($product) use ($user) {
                $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
            });

            return response()->json([
                'status' => 'success',
                'products' => $products,
                'brands' => $brands,
                'metkas' => $metkas,
                'shops' => $shops,
                'totalPages' => $totalPages,
                'filterAttributeVariant' => $mergedData,
                'category' => $category,
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function productsAndBrandInCategory(Request $request){
        $user = Auth::user();
        if ($user) {
            $country = $user->country;
            $categories = Category::all()->sortBy('order')->values();
    
            foreach ($categories as $category) {
                $productsQuery = Product::query()
                    ->whereHas('shopProducts', function ($query) use ($country) {
                        $query->where('country', $country);
                    })
                    ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas'])
                    ->where('category_id', $category->id)
                    ->get();

                    $productsQuery->each(function ($product) use ($user) {
                        $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
                    });
    
                $category->products = $productsQuery;
            }
    
            return response()->json([
                'status' => 'success',
                'categories' => $categories,
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
    
    public function searchProduct(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $country = $user->country;
            $query = $request->input('searchText', '');

            $uniqueProductIds = ShopProduct::select('product_id')
                ->where('country', $country)
                ->distinct()
                ->get();

            // Фильтруем продукты с использованием оператора LIKE
            $products = Product::whereIn('id', $uniqueProductIds->pluck('product_id'))
                ->where(function($q) use ($query) {
                    $q->where('name_ru', 'LIKE', '%' . $query . '%')
                    ->orWhere('name_kz', 'LIKE', '%' . $query . '%');
                    // ->orWhere('description_ru', 'LIKE', '%' . $query . '%')
                    // ->orWhere('description_kz', 'LIKE', '%' . $query . '%');
                })
                ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas'])
                ->get();

            $products->each(function ($product) use ($user) {
                $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
            });

            return response()->json([
                'status' => 'success',
                'products' => $products->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function searchViewProduct(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $query = $request->input('searchText', '');
            $country = $user->country;
            $perPage = $request->input('perPage', 10); // Количество товаров на странице
            $page = $request->input('page', 1); // Номер текущей страницы

            // Создаем запрос для продуктов с соответствующими связями
            $productsQuery = Product::query()
                ->whereHas('shopProducts', function ($query) use ($country) {
                    $query->where('country', $country);
                })
                ->where(function($q) use ($query) {
                    $q->where('name_ru', 'LIKE', '%' . $query . '%')
                    ->orWhere('name_kz', 'LIKE', '%' . $query . '%');
                    // ->orWhere('description_ru', 'LIKE', '%' . $query . '%')
                    // ->orWhere('description_kz', 'LIKE', '%' . $query . '%');
                })
                ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas']);

           
            //фильтр
            $brandsFilter = $request->input('brandIds');

            // Применяем фильтр по брендам, если он был отправлен
            if ($brandsFilter) {
                $brandsFilterArray = explode(',', $brandsFilter);
                $productsQuery->whereIn('brand_id', $brandsFilterArray);
            }

            $selectedVariantIds = $request->input('selectedVariantIds');

            if($selectedVariantIds){
                $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
                    // dd($query);
                    $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
                });
            }

            // Фильтр по магазинам
            $shopFilter = $request->input('shopId');
            if ($shopFilter) {
                $shopFilterArray = explode(',', $shopFilter);
                $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilterArray) {
                    $query->whereIn('shop_id', $shopFilterArray);
                });
            }

            $metkasFilter = $request->input('metkaIds');

            // Применяем фильтр по брендам, если он был отправлен
            if ($metkasFilter) {
                $metkasFilterArray = explode(',', $metkasFilter);
                $productsQuery->whereHas('metkas', function ($query) use ($metkasFilterArray) {
                    $query->whereIn('metkas.id', $metkasFilterArray);
                });
            }

            // Получаем общее количество доступных товаров для данной страны
            $totalProducts = $productsQuery->count();
            
            // Вычисляем общее количество страниц
            $totalPages = ceil($totalProducts / $perPage);
            
            // Вычисляем смещение для пропуска записей в зависимости от текущей страницы и количества товаров на странице
            $offset = ($page - 1) * $perPage;

            // Получаем продукты с учетом фильтров и пагинацией
            $products = $productsQuery
                ->offset($offset)
                ->limit($perPage)
                ->get();

            // Получаем список брендов
            //фильтр
            $allUniqueProductIds = ShopProduct::select('product_id')
            ->where('country', $country)
            ->distinct()
            ->get();

            $allProducts = Product::whereIn('id', $allUniqueProductIds->pluck('product_id'))
                ->with(['brand','attributes','variants','metkas'])
                ->where(function($q) use ($query) {
                    $q->where('name_ru', 'LIKE', '%' . $query . '%')
                    ->orWhere('name_kz', 'LIKE', '%' . $query . '%');
                    // ->orWhere('description_ru', 'LIKE', '%' . $query . '%')
                    // ->orWhere('description_kz', 'LIKE', '%' . $query . '%');
                })
                ->get();

            $brands = $allProducts->pluck('brand')->unique('id')->values()->all();

            $metkas = $allProducts->pluck('metkas')
            ->flatten(1)
            ->unique('id')
            ->map(function ($metka) {
                return $metka->makeHidden(['pivot']);
            })
            ->values()
            ->all();

            $shops = $allProducts->pluck('shopProducts')->flatten(1)->pluck('shop')->unique('id')->values()->all();
           

           

            // Получаем все атрибуты и варианты из $allProducts
            $filterAttributes = collect($allProducts)->pluck('attributes')->flatten()->unique('id');
            $filterVariants = collect($allProducts)->pluck('variants')->flatten()->unique('id');

            // Создаем пустую коллекцию для хранения объединенных данных
            $mergedData = new Collection();

            // Обходим все атрибуты
            $filterAttributes->each(function ($attribute) use ($filterVariants, $mergedData) {
                // Получаем варианты атрибута
                $variants = $filterVariants->where('attribute_id', $attribute['id'])->values()->all();
                
                // Добавляем варианты к атрибуту
                $attribute['variants'] = $variants;
                
                // Добавляем атрибут и его варианты к объединенным данным
                $mergedData->push($attribute);
            });

            // Проверяем, есть ли продукт в списке желаний пользователя
            $products->each(function ($product) use ($user) {
                $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
            });

            return response()->json([
                'status' => 'success',
                'products' => $products,
                'brands' => $brands,
                'metkas' => $metkas,
                'shops' => $shops,
                'totalPages' => $totalPages,
                'filterAttributeVariant' => $mergedData,
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    // public function metkasInProduct(Request $request){
    //     $user = Auth::user();
    //     if ($user) {
    //         $country = $user->country;
    //         $metkas = Metka::where('activeMain',true)->get();
    
    //         foreach ($metkas as $metka) {
    //             $productsQuery = Product::query()
    //                 ->whereHas('shopProducts', function ($query) use ($country) {
    //                     $query->where('country', $country);
    //                 })
    //                 ->whereHas('metkas', function ($query) use ($metka) {
    //                     $query->where('metka_id', $metka->id);
    //                 })
    //                 ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas'])
    //                 ->get();

    //                 $productsQuery->each(function ($product) use ($user) {
    //                     $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
    //                 });
    
    //             $metka->products = $productsQuery;
    //         }
    
    //         return response()->json([
    //             'status' => 'success',
    //             'metkas' => $metkas,
    //         ]);
    //     } else {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }
    // }

    public function metkasInProduct(Request $request) {
        $user = Auth::user();
        if ($user) {
            $country = $user->country;
            $metkas = Metka::where('activeMain', true)->get();
    
            $filteredMetkas = $metkas->filter(function ($metka) use ($country, $user) {
                $productsQuery = Product::query()
                    ->whereHas('shopProducts', function ($query) use ($country) {
                        $query->where('country', $country);
                    })
                    ->whereHas('metkas', function ($query) use ($metka) {
                        $query->where('metka_id', $metka->id);
                    })
                    ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user', 'metkas'])
                    ->get();
    
                // Добавляем флаг is_in_wishlist для каждого продукта
                $productsQuery->each(function ($product) use ($user) {
                    $product->is_in_wishlist = $user->whishlists()->where('product_id', $product->id)->exists();
                });
    
                // Сохраняем продукты в метке
                $metka->products = $productsQuery;
    
                // Возвращаем true только если у метки есть продукты
                return $productsQuery->isNotEmpty();
            });
    
            return response()->json([
                'status' => 'success',
                'metkas' => $filteredMetkas->values(), // Преобразуем коллекцию в массив
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
    

}
