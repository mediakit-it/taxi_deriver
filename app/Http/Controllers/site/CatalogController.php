<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShopProduct;

class CatalogController extends Controller
{


    public function index($slug, Request $request)
    {
        $category = Category::where('slug', 'parfium')->firstOrFail();
        $country = get_country_from_cookie();

        $perPage = $request->input('perPage', 12);
        $page = $request->input('page', 1);
        $shopProducts = ShopProduct::with('product')
            ->where('country', $country)
            ->get()->filter(function ($item){
                if ($item->variable == 'false'){
                    return intval($item['quantity']) > 0;
                }
                else{
                    if (count($item['variations']) == 0){
                        return false;
                    }
                    $tempCount = count(array_values(array_filter($item['variations'], function ($item){
                        return $item['quantity'] > 0;
                    })));

                    return $tempCount > 0;
                }
            });

        if ($shopFilter = $request->input('shopId')){
            $shopProducts = $shopProducts->toQuery()->whereIn('shop_id', explode(',', $shopFilter))->get();
        }
        // Получаем категории сразу в виде массива идентификаторов
        $productsQuery = Product::query()
            ->with(['shopProductsWeb','shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas', 'variants', 'attributes'])
            ->where('category_id', $category->id)
            ->whereIn('id', $shopProducts->pluck('product_id'))
            ->get()
            ->toQuery();

            $categories = [];
            $categories[] = $category;

            if ($brandsFilter = $request->input('brandIds')) {
                $productsQuery->whereIn('brand_id', explode(',', $brandsFilter));
            }

            if ($metkasFilter = $request->input('metkaIds')) {
                $productsQuery->whereHas('metkas', function ($query) use ($metkasFilter) {
                    $query->whereIn('metkas.id', explode(',', $metkasFilter));
                });
            }

            if ($selectedVariantIds = $request->input('selectedVariantIds')) {
                $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
                    $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
                });
            }



            $allProducts = $productsQuery->get();

            // Получаем уникальные значения для фильтров на основе всех товаров
            $brands = $allProducts->pluck('brand')->unique('id')->values();
            $shops = $allProducts->pluck('shopProductsWeb')->flatten(1)->pluck('shop')->unique('id')->values();
            $metkas = $allProducts->pluck('metkas')->flatten(1)->unique('id')->map(function ($metka) {
                return $metka->makeHidden(['pivot']);
            })->values();

            $filterAttributes = $allProducts->pluck('attributes')->flatten()->unique('id');
            $filterVariants = $allProducts->pluck('variants')->flatten()->unique('id');

            // Создаем атрибуты и варианты для фильтров
            $mergedData = $filterAttributes->map(function ($attribute) use ($filterVariants) {
                $attribute['variants'] = $filterVariants->where('attribute_id', $attribute['id'])->values();
                return $attribute;
            });



            // Пагинация
            $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
                $allProducts->forPage($page, $perPage),
                $allProducts->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Возвращаем ответ
            if ($request->ajax()) {
                return response()->json([
                    'products' => view('site.catalog.products_partial', ['products' => $paginatedProducts])->render(),
                    'newInfoUpdate' => [
                        'firstItem' => $paginatedProducts->firstItem(),
                        'lastItem' => $paginatedProducts->lastItem(),
                        'total' => $paginatedProducts->total(),
                    ],
                ]);
            }

            return view('site.catalog.indexAll', [
                'categories' => $categories,
                'products' => $paginatedProducts,
                'brands' => $brands,
                'metkas' => $metkas,
                'totalPages' => $paginatedProducts->lastPage(),
                'filterAttributeVariant' => $mergedData,
                'shops' => $shops
            ]);
    }


    public function indexAll(Request $request)
    {
        $categories = Category::all();
        $country = get_country_from_cookie();

        $perPage = $request->input('perPage', 12);
        $page = $request->input('page', 1);

        // Получаем категории сразу в виде массива идентификаторов
        $categoryIds = $categories->pluck('id')->toArray();

        $shopProducts = ShopProduct::with('product')
            ->where('country', $country)
            ->get()->filter(function ($item){
                if ($item->variable == 'false'){
                    return intval($item['quantity']) > 0;
                }
                else{
                    if (count($item['variations']) == 0){
                        return false;
                    }
                    $tempCount = count(array_values(array_filter($item['variations'], function ($item){
                        return $item['quantity'] > 0;
                    })));

                    return $tempCount > 0;
                }
            });

        if ($shopFilter = $request->input('shopId')){
            $shopProducts = $shopProducts->toQuery()->whereIn('shop_id', explode(',', $shopFilter))->get();
        }

        $productsQuery = Product::query()
            ->with(['shopProductsWeb','shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas', 'variants', 'attributes'])
            ->whereIn('category_id', $categoryIds)
            ->whereIn('id', $shopProducts->pluck('product_id'))
            ->get()
            ->toQuery();


        // Получаем все продукты для фильтров (не зависимо от текущих фильтров)
//        $allProducts = Product::query()
//            ->whereIn('category_id', $categoryIds)
//            ->whereHas('shopProductsWeb', function ($query) use ($country) {
//                $query->where('country', $country);
//            })
//            ->with(['brand', 'metkas', 'variants', 'attributes', 'shopProductsWeb.shop'])
//            ->get();
        if ($brandsFilter = $request->input('brandIds')) {
            $productsQuery->whereIn('brand_id', explode(',', $brandsFilter));
        }

        if ($metkasFilter = $request->input('metkaIds')) {
            $productsQuery->whereHas('metkas', function ($query) use ($metkasFilter) {
                $query->whereIn('metkas.id', explode(',', $metkasFilter));
            });
        }

        if ($selectedVariantIds = $request->input('selectedVariantIds')) {
            $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
                $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
            });
        }

//        if ($shopFilter = $request->input('shopId')) {
//            $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilter) {
//                $query->whereIn('shop_id', explode(',', $shopFilter));
//            });
//        }

        $allProducts = $productsQuery->get();

        // Получаем уникальные значения для фильтров на основе всех товаров
        $brands = $allProducts->pluck('brand')->unique('id')->values();
        $shops = $allProducts->pluck('shopProductsWeb')->flatten(1)->pluck('shop')->unique('id')->values();
        $metkas = $allProducts->pluck('metkas')->flatten(1)->unique('id')->map(function ($metka) {
            return $metka->makeHidden(['pivot']);
        })->values();

        $filterAttributes = $allProducts->pluck('attributes')->flatten()->unique('id');
        $filterVariants = $allProducts->pluck('variants')->flatten()->unique('id');

        // Создаем атрибуты и варианты для фильтров
        $mergedData = $filterAttributes->map(function ($attribute) use ($filterVariants) {
            $attribute['variants'] = $filterVariants->where('attribute_id', $attribute['id'])->values();
            return $attribute;
        });

        $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
            $allProducts->forPage($page, $perPage),
            $allProducts->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Возвращаем ответ
        if ($request->ajax()) {
            return response()->json([
                'products' => view('site.catalog.products_partial', ['products' => $paginatedProducts])->render(),
                'newInfoUpdate' => [
                    'firstItem' => $paginatedProducts->firstItem(),
                    'lastItem' => $paginatedProducts->lastItem(),
                    'total' => $paginatedProducts->total(),
                ],
            ]);
        }

        return view('site.catalog.indexAll', [
            'categories' => $categories,
            'products' => $paginatedProducts,
            'brands' => $brands,
            'metkas' => $metkas,
            'totalPages' => $paginatedProducts->lastPage(),
            'filterAttributeVariant' => $mergedData,
            'shops' => $shops
        ]);
    }


    //----- рабочая
//     public function search(Request $request){

//         // $categories = Category::all();
//         $country = get_country_from_cookie();

//         $perPage = $request->input('perPage', 12);
//         $page = $request->input('page', 1);

//         // Получаем категории сразу в виде массива идентификаторов
//         // $categoryIds = $categories->pluck('id')->toArray();

//         $lang = get_languages_from_cookie() == 'RUS' ? 'name_ru' : 'name_kz';
//         $searchText = $request->input('search');


//         // Получаем все продукты для фильтров (не зависимо от текущих фильтров)
//         $allProducts = Product::query()
//             // ->whereIn('category_id', $categoryIds)
//             ->where($lang, 'like', '%' . $searchText . '%')
//             ->whereHas('shopProductsWeb', function ($query) use ($country) {
//                 $query->where('country', $country);
//             })
//             ->with(['brand', 'metkas', 'variants', 'attributes', 'shopProductsWeb.shop'])
//             ->get();

//         // Получаем уникальные значения для фильтров на основе всех товаров
//         $brands = $allProducts->pluck('brand')->unique('id')->values();
//         $shops = $allProducts->pluck('shopProductsWeb')->flatten(1)->pluck('shop')->unique('id')->values();
//         $metkas = $allProducts->pluck('metkas')->flatten(1)->unique('id')->map(function ($metka) {
//             return $metka->makeHidden(['pivot']);
//         })->values();

//         $filterAttributes = $allProducts->pluck('attributes')->flatten()->unique('id');
//         $filterVariants = $allProducts->pluck('variants')->flatten()->unique('id');

//         // Создаем атрибуты и варианты для фильтров
//         $mergedData = $filterAttributes->map(function ($attribute) use ($filterVariants) {
//             $attribute['variants'] = $filterVariants->where('attribute_id', $attribute['id'])->values();
//             return $attribute;
//         });

//         // Создаем запрос для продуктов с предзагрузкой необходимых связей, но уже с фильтрами
//         $productsQuery = Product::query()
//             ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas', 'variants', 'attributes'])
//             ->where($lang, 'like', '%' . $searchText . '%')
//             ->whereHas('shopProductsWeb', function ($query) use ($country) {
//                 $query->where('country', $country);
//             });

//         // Применяем фильтры, если они были отправлены
//         if ($brandsFilter = $request->input('brandIds')) {
//             $productsQuery->whereIn('brand_id', explode(',', $brandsFilter));
//         }

//         if ($metkasFilter = $request->input('metkaIds')) {
//             $productsQuery->whereHas('metkas', function ($query) use ($metkasFilter) {
//                 $query->whereIn('metkas.id', explode(',', $metkasFilter));
//             });
//         }

//         if ($selectedVariantIds = $request->input('selectedVariantIds')) {
//             $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
//                 $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
//             });
//         }

//         if ($shopFilter = $request->input('shopId')) {
//             $productsQuery->whereHas('shopProductsWeb', function ($query) use ($shopFilter) {
//                 $query->whereIn('shop_id', explode(',', $shopFilter));
//             });
//         }

//         // Пагинация и получение продуктов
//         $products = $productsQuery->paginate($perPage);

//         if ($request->ajax()) {
//             return response()->json([
//                 'products' => view('site.catalog.products_partial', compact('products'))->render(),
//                 'newInfoUpdate' => [
//                     'firstItem' => $products->firstItem(),
//                     'lastItem' => $products->lastItem(),
//                     'total' => $products->total(),
//                 ],
//             ]);
//         }

//         return view('site.search.indexAll', [
//             'products' => $products,
//             'searchText' => $searchText,
//             'brands' => $brands,
//             'metkas' => $metkas,
//             'totalPages' => $products->lastPage(),
//             'filterAttributeVariant' => $mergedData,
//             'shops' => $shops
//         ]);


//    }
    //конец рабочего

    public function search(Request $request)
    {
        $country = get_country_from_cookie();
        $lang = get_languages_from_cookie() == 'RUS' ? 'name_ru' : 'name_kz';

        $perPage = $request->input('perPage', 12);
        $searchText = $request->input('search', '');
        $shopProducts = ShopProduct::with('product')
            ->where('country', $country)
            ->get()->filter(function ($item){
                if ($item->variable == 'false'){
                    return intval($item['quantity']) > 0;
                }
                else{
                    if (count($item['variations']) == 0){
                        return false;
                    }
                    $tempCount = count(array_values(array_filter($item['variations'], function ($item){
                        return $item['quantity'] > 0;
                    })));

                    return $tempCount > 0;
                }
            });
        if ($shopFilter = $request->input('shopId')){
            $shopProducts = $shopProducts->toQuery()->whereIn('shop_id', explode(',', $shopFilter))->get();
        }
        // Базовый запрос для продуктов с учетом страны
        $productsQuery = Product::query()
            ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas', 'variants', 'attributes'])
            ->where($lang, 'like', '%' . $searchText . '%')
            ->whereIn('id',$shopProducts->pluck('product_id'))
            ->get()
            ->toQuery();

        // Применение фильтров
        if ($brandsFilter = $request->input('brandIds')) {
            $productsQuery->whereIn('brand_id', explode(',', $brandsFilter));
        }

        if ($metkasFilter = $request->input('metkaIds')) {
            $productsQuery->whereHas('metkas', function ($query) use ($metkasFilter) {
                $query->whereIn('metkas.id', explode(',', $metkasFilter));
            });
        }

        if ($selectedVariantIds = $request->input('selectedVariantIds')) {
            $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
                $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
            });
        }



        // Получаем отфильтрованные продукты с пагинацией
        $products = $productsQuery->paginate($perPage);

        // Получение всех продуктов для фильтров
        $allProducts = Product::query()
            ->where($lang, 'like', '%' . $searchText . '%')
            ->whereHas('shopProductsWeb', function ($query) use ($country) {
                $query->where('country', $country);
            })
            ->with(['brand', 'metkas', 'variants', 'attributes', 'shopProductsWeb.shop'])
            ->get();

        // Уникальные фильтры для брендов, магазинов, меток
        $brands = $allProducts->pluck('brand')->unique('id')->values();
        $shops = $allProducts->pluck('shopProductsWeb')->flatten(1)->pluck('shop')->unique('id')->values();
        $metkas = $allProducts->pluck('metkas')->flatten(1)->unique('id')->map(function ($metka) {
            return $metka->makeHidden(['pivot']);
        })->values();

        // Атрибуты и варианты для фильтров
        $filterAttributes = $allProducts->pluck('attributes')->flatten()->unique('id');
        $filterVariants = $allProducts->pluck('variants')->flatten()->unique('id');
        $filterAttributeVariant = $filterAttributes->map(function ($attribute) use ($filterVariants) {
            $attribute['variants'] = $filterVariants->where('attribute_id', $attribute['id'])->values();
            return $attribute;
        });

        // Возврат данных для AJAX-запросов
        if ($request->ajax()) {
            return response()->json([
                'products' => view('site.catalog.products_partial', compact('products'))->render(),
                'filters' => [
                    'brands' => $brands,
                    'metkas' => $metkas,
                    'shops' => $shops,
                    'filterAttributeVariant' => $filterAttributeVariant,
                ],
                'newInfoUpdate' => [
                    'firstItem' => $products->firstItem(),
                    'lastItem' => $products->lastItem(),
                    'total' => $products->total(),
                ],
            ]);
        }

        // Возврат полной страницы для обычных запросов
        return view('site.search.indexAll', [
            'products' => $products,
            'searchText' => $searchText,
            'brands' => $brands,
            'metkas' => $metkas,
            'filterAttributeVariant' => $filterAttributeVariant,
            'shops' => $shops,
            'totalPages' => $products->lastPage(),
        ]);
    }



}
