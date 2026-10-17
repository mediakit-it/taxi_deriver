<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopProduct;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Metka;
use App\Models\Product;

class ShopController extends Controller
{

    public function index(){

        // Получаем аутентифицированного пользователя по токену
        $user = Auth::user();

        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $country = $user->country;
            $shops = Shop::where('country', $country)->get();
            return response()->json([
                'status' => 'success',
                'shops' => $shops,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function showProducts($shopId, Request $request)
{
    // $query = ShopProduct::where('shop_id', $shopId)->with('product');

    // if ($request->has('category')) {
    //     $query->whereHas('product', function($q) use ($request) {
    //         $q->where('category_id', $request->input('category'));
    //     });
    // }

    // if ($request->has('brand')) {
    //     $query->whereHas('product', function($q) use ($request) {
    //         $q->where('brand_id', $request->input('brand'));
    //     });
    // }


    // $shopProducts = $query->paginate(12);
    // $shop = Shop::findOrFail($shopId);
    // $categories = Category::all();
    // $brands = Brand::all();
    // $shops = Shop::all();
    // $metkas = Metka::all();

    // return view('site.shop.products', compact('shop', 'shopProducts', 'categories', 'brands', 'shops', 'metkas'));
    $shop = Shop::findOrFail($shopId);
    $category = Category::where('slug', 'parfium')->firstOrFail();
    $country = get_country_from_cookie();

    $perPage = $request->input('perPage', 12);
    $page = $request->input('page', 1);

    // Получаем товары магазина для выбранного магазина
    $shopProducts = ShopProduct::with('product')
        ->where('shop_id', $shopId) // Фильтруем по выбранному магазину
        ->where('country', $country)
        ->get()->filter(function ($item) {
            // Существующая логика фильтрации
            if ($item->variable == 'false') {
                return intval($item['quantity']) > 0;
            } else {
                if (count($item['variations']) == 0) {
                    return false;
                }
                $tempCount = count(array_values(array_filter($item['variations'], function ($item) {
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

        return view('site.shop.products', [
            'categories' => $categories,
            'products' => $paginatedProducts,
            'brands' => $brands,
            'metkas' => $metkas,
            'totalPages' => $paginatedProducts->lastPage(),
            'filterAttributeVariant' => $mergedData,
            'shops' => $shops,
            'shop' => $shop,
            'shopId' => $shopId
        ]);
}


}
