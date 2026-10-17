<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pageconstruct;
use App\Models\Shop;

class PageController extends Controller
{
    public function about(){

        $pageconstruct = Pageconstruct::where('slug','about')->first();
        return view('site.page.about', compact('pageconstruct'));

    }

    public function contact_us(){
        $pageconstruct = Pageconstruct::where('slug','contact-us')->first();

        $country = get_country_from_cookie();
        $shops = Shop::where('country',$country)->get();
        
        return view('site.page.contact_us',compact('pageconstruct','shops'));
    }


    public function shop(){
        $pageconstruct = Pageconstruct::where('slug')->first();

        $country = get_country_from_cookie();
        $shops = Shop::where('country',$country)->get();
        
        return view('site.page.shop',compact('pageconstruct','shops'));
    }


    public function garantiya_kachestva(){
        $pageconstruct = Pageconstruct::where('slug','garantiya-kachestva')->first();
        return view('site.page.garantia', compact('pageconstruct'));
     
    }

    public function dostavka_i_oplata(){
        return view('site.page.dostavka_i_oplata');
    }

    public function dostavka_i_oplata_rus(){
        return view('site.page.dostavka_i_oplata_rus');
    }

    public function franshiza(){
        $pageconstruct = Pageconstruct::where('slug','franshiza')->first();
        return view('site.page.franshiza', compact('pageconstruct'));
        
    }

    public function sistema_loyalnosti(){
        $pageconstruct = Pageconstruct::where('slug','sistema_loyalnosti')->first();
        return view('site.page.sistema_loyalnosti', compact('pageconstruct'));
    }

    public function podarichnyi_sertificat(){
        $pageconstruct = Pageconstruct::where('slug','podarichnyi_sertificat')->first();
        return view('site.page.podarichnyi_sertificat', compact('pageconstruct'));

    }

    public function informacia_dly_clientov(){
        $pageconstruct = Pageconstruct::where('slug','informacia_dly_clientov')->first();
        return view('site.page.informacia_dly_clientov', compact('pageconstruct'));
    }

    


}


