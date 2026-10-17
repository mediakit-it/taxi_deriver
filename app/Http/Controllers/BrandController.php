<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    public function getBrandsCount(Request $request){
        $user = Auth::user();
    
        if ($user) {
            $country = $user->country;
            $uniqueProductIds = ShopProduct::select('product_id')
                ->where('country', $country)
                ->distinct()
                ->get();

            // Получаем соответствующие продукты для выбранных product_id
            $products = Product::whereIn('id', $uniqueProductIds->pluck('product_id'))
                ->with(['shopProducts.shop','brand','category','reviews.user'])
                ->get();
            // $products = Product::where('country', $country)->take(10)->get();

            $brands = Brand::inRandomOrder()->take(4)->get();

            foreach ($brands as $brand) {
                $brand->product_count = $products->where('brand_id', $brand->id)->count();
            }

            return response()->json([
                'status' => 'success',
                'brands' => $brands,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getBrandsAll(Request $request){
        $user = Auth::user();
    
        if ($user) {
            $country = $user->country;
            $uniqueProductIds = ShopProduct::select('product_id')
                ->where('country', $country)
                ->distinct()
                ->get();

            // Получаем соответствующие продукты для выбранных product_id
            $products = Product::whereIn('id', $uniqueProductIds->pluck('product_id'))
                ->with(['shopProducts.shop','brand','category','reviews.user'])
                ->get();
            // $products = Product::where('country', $country)->take(10)->get();

            $brands = Brand::all()->sortBy('order')->values();

            foreach ($brands as $brand) {
                $brand->product_count = $products->where('brand_id', $brand->id)->count();
            }

            return response()->json([
                'status' => 'success',
                'brands' => $brands,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getAllProductBrand($slug,Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $country = $user->country;
            $brandIshod = Brand::where('slug',$slug)->first();
            $perPage = $request->input('perPage', 10); // Количество товаров на странице
            $page = $request->input('page', 1); // Номер текущей страницы

            // Создаем запрос для продуктов с соответствующими связями
            $productsQuery = Product::query()
                ->whereHas('shopProducts', function ($query) use ($country) {
                    $query->where('country', $country);
                })
                ->with(['shopProducts.shop', 'brand', 'category', 'reviews.user','metkas'])
                ->where('brand_id',$brandIshod->id);

            $metkasFilter = $request->input('metkaIds');

            // Применяем фильтр по брендам, если он был отправлен
            if ($metkasFilter) {
                $metkasFilterArray = explode(',', $metkasFilter);
                $productsQuery->whereHas('metkas', function ($query) use ($metkasFilterArray) {
                    $query->whereIn('metkas.id', $metkasFilterArray);
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

            $selectedVariantIds = $request->input('selectedVariantIds');

            if($selectedVariantIds){
                $productsQuery->whereHas('variants', function ($query) use ($selectedVariantIds) {
                    // dd($query);
                    $query->whereIn('attribute_variants.id', explode(',', $selectedVariantIds));
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
                ->with(['brand','attributes','variants'])
                ->where('brand_id',$brandIshod->id)
                ->get();

            // Получаем все атрибуты и варианты из $allProducts
            $filterAttributes = collect($allProducts)->pluck('attributes')->flatten()->unique('id');
            $filterVariants = collect($allProducts)->pluck('variants')->flatten()->unique('id');

            $metkas = $allProducts->pluck('metkas')
            ->flatten(1)
            ->unique('id')
            ->map(function ($metka) {
                return $metka->makeHidden(['pivot']);
            })
            ->values()
            ->all();

            $shops = $allProducts->pluck('shopProducts')->flatten(1)->pluck('shop')->unique('id')->values()->all();

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
                'totalPages' => $totalPages,
                'metkas' => $metkas,
                'shops' => $shops,
                'filterAttributeVariant' => $mergedData,
                'brand' => $brandIshod,
            ]);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
    
}
