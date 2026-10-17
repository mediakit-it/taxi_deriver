<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Discount;

class ActionController extends Controller
{
    public function actionPage($slug){
        $action = Discount::where('slug',$slug)->first();
        return view('site.action.action_page',['action'=>$action]);
    }

    public function promotions(){
        $actions = Discount::where('country',get_country_from_cookie())->get()->sortBy('order');
        return view('site.page.promotions',['actions'=>$actions]);
    }
}
