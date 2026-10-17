<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\Baner;

class MainController extends Controller
{
    // public function index() {
    //     // Cookie::queue('Country', 'belarus', 60);
    //     // Cookie::queue('Langviges', 'kazach_lan', 60);
    //     // Получение значений куки
    //     $countryResult = get_country_from_cookie();
    //     $languageResult = get_languages_from_cookie();
        
       

    //     // Получение уникальных продуктов по стране
    //     $uniqueProductIds = ShopProduct::select('product_id')
    //         ->where('country', $countryResult)
    //         ->distinct()
    //         ->inRandomOrder()
    //         ->pluck('product_id'); // Получаем только идентификаторы продуктов

    //     // Получение продуктов по идентификаторам
    //     $products = Product::whereIn('id', $uniqueProductIds)
    //         ->with(['shopProductsWeb.shop', 'brand', 'category', 'reviews.user', 'metkas'])->limit(30)
    //         ->get();

    //     $banners = Baner::where('country',$countryResult)->get();

    //     return view('site.index', [
    //         'products' => $products->makeHidden(['category_id', 'brand_id', 'created_at', 'updated_at']),
    //         'banners'=>$banners
    //     ]);
    // }

    public function index()
    {
       

        return view('site.index', [
            'products' => collect(),
            'banners' => collect(),
        ]);
    }



}
