<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Facades\Log;

use App\Models\Shop;
use App\Models\Product;
use App\Models\ShopProduct;

HeadingRowFormatter::default('none');
class ProductImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */

    public function model(array $row)
    {
        $shop = Shop::where('name_ru',$row['Название магазина'])->first();
        if (!$shop) {
            Log::error('Shop not found: ' . $row['Название магазина']);
            return null;
        }

        $product = Product::where('article',$row['Артикул товара'])->first();
        if (!$product) {
            Log::error('Product not found: ' . $row['Артикул товара'] .'--'. $row['Название магазина'].'----'.$row['Вариативный товар']);
            return null;
        }

        $shopProduct = ShopProduct::where([['shop_id',$shop->id],['product_id',$product->id]])->first();
        Log::error('row' . json_encode($row));

        $variableValue = trim($row['Вариативный товар']);
        Log::error('Variable value: "' . $variableValue . '", length: ' . strlen($variableValue));

        if ($variableValue === 'false') {
            Log::error('This is correctly identified as false - ' . $row['Артикул товара']);
            // здесь код для обработки не вариативного товара
        } else {
            Log::error('This is not identified as false - ' . $row['Артикул товара']);
            // здесь код для обработки вариативного товара
        }
        
        if ($shopProduct) {
            if($variableValue === 'false'){
                Log::error('false - '.$row['Артикул товара']);
                Log::error('idShop - '.$shopProduct->id);
                $shopProduct->update([
                    'price' => $row['Цена'],
                    'discounted_price'=> !empty($row['Цена со скидкой']) ? $row['Цена со скидкой'] : 0,
                    'quantity'=>$row['Количество'],
                    'variable'=>'false',
                    'variations'=>[],
                ]);
                Log::error('idShopImp - '.$shopProduct->discounted_price);
            }else{
                Log::error('true - '.$row['Артикул товара']);
                $variations = $row['Вариации'];
                $entries = array_filter(explode(';', $variations));

                $entries = array_filter($entries);

                $data = [];

                foreach ($entries as $entry) {
                    $pairs = explode(',', $entry);
                    $item = [];
                
                    foreach ($pairs as $pair) {
                        // Разбиваем пару на ключ и значение
                        list($key, $value) = explode(':', $pair, 2);
                        // Удаляем пробелы вокруг значений и добавляем в массив
                        $item[trim($key)] = trim($value);
                    }
                
                    // Добавляем полученный элемент в основной массив
                    $data[] = $item;
                }

                $shopProduct->update([
                    'variable'=>'true',
                    'variations'=>$data,
                ]);        
            }
        }else{
            if($variableValue === 'false'){
                Log::error('первое - '.$row['Артикул товара']);
                $shopProduct = ShopProduct::create([
                    'country' => $row['Страна'],
                    'shop_id' => $shop->id,
                    'product_id' => $product->id,
                    'price' => $row['Цена'],
                    'discounted_price' => !empty($row['Цена со скидкой']) ? $row['Цена со скидкой'] : 0,
                    'quantity' => $row['Количество'],
                    'variable' => 'false',
                ]);

            }else{
                Log::error('второе - '.$row['Артикул товара']);
                $variations = $row['Вариации'];
                $entries = array_filter(explode(';', $variations));

                $entries = array_filter($entries);

                $data = [];

                foreach ($entries as $entry) {
                    $pairs = explode(',', $entry);
                    $item = [];
                
                    foreach ($pairs as $pair) {
                        // Разбиваем пару на ключ и значение
                        list($key, $value) = explode(':', $pair, 2);
                        // Удаляем пробелы вокруг значений и добавляем в массив
                        $item[trim($key)] = trim($value);
                    }
                
                    // Добавляем полученный элемент в основной массив
                    $data[] = $item;
                }

                $shopProduct = ShopProduct::create([
                    'country' => $row['Страна'],
                    'shop_id' => $shop->id,
                    'product_id' => $product->id,
                    'price' => 0,
                    'discounted_price' => 0,
                    'quantity' => 0,
                    'variable' => 'true',
                    'variations' => $data,
                ]);

            }
        }

        Log::error('Следующий - ');

    }
}
