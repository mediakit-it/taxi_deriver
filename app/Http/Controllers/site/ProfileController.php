<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Basket;

class ProfileController extends Controller
{
    public function listOrders(){
        $user = Auth::guard('web')->user();
        $baskets = Basket::where('email',$user->email)->with('basketitems')->orderByDesc('id')->get();

        return view('site.auth.list-orders',compact('baskets'));
    }
}
