<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CookiesController extends Controller
{
    /**
     * Устанавливает куки.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function setCookie(Request $request)
    {
        // Пример установки куки
        $country = $request->input('country', 'belarus');
        $language = $request->input('language', 'russian_lan');

        // Устанавливаем куки на 5 дней
        $minutes = 5 * 24 * 60;
        Cookie::queue(Cookie::make('Country', $country, $minutes));
        Cookie::queue(Cookie::make('Langviges', $language, $minutes));

        session()->forget('cart');

        return response()->json(['message' => 'Cookies set successfully']);
    }

    /**
     * Устанавливает куки страны.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function setCookieCountry(Request $request)
    {
        // Пример установки куки
        $country = $request->input('country', 'belarus');
        // Устанавливаем куки на 5 дней
        $minutes = 5 * 24 * 60;
        Cookie::queue(Cookie::make('Country', $country, $minutes));

        session()->forget('cart');

        return response()->json(['message' => 'Cookies set successfully']);
    }

    

    /**
     * Устанавливает куки языка.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function setCookieLanguage(Request $request)
    {
        // Пример установки куки
        $language = $request->input('language', 'russian_lan');

        // Устанавливаем куки на 5 дней
        $minutes = 5 * 24 * 60;
        Cookie::queue(Cookie::make('Langviges', $language, $minutes));


        return response()->json(['message' => 'Cookies set successfully']);
    }

    /**
     * Получает значения куки.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCookie(Request $request)
    {
        $country = Cookie::get('Country');
        $language = Cookie::get('Langviges');

        return response()->json(['country' => $country, 'language' => $language]);
    }

    public function checkCookie()
    {
        $hasCookies = Cookie::has('Country') && Cookie::has('Langviges');
        return response()->json(['hasCookies' => $hasCookies]);
    }

    public function setCookieElement(Request $request)
    {

        $name = $request->input('name');
        $value = $request->input('value', 'true');

        // Устанавливаем куки на 15 дней
        $minutes = 15 * 24 * 60;
        Cookie::queue(Cookie::make($name, $value, $minutes));

        return response()->json(['message' => 'Cookies set successfully']);
    }
}
