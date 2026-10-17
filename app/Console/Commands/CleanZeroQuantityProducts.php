<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ShopProduct;
use Log;

class CleanZeroQuantityProducts extends Command
{
    protected $signature = 'products:clean-zero-quantity'; // Название команды
    protected $description = 'Очистка товаров с нулевым количеством';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Запуск очистки товаров с нулевым количеством...');

        // Получаем продукты с их связанными shopProducts
        $products = Product::with('shopProductsWeb2')->get();

        foreach ($products as $product) {
            foreach ($product->shopProductsWeb2 as $variant) {
                // Если товар не вариативный
                if ($variant->variable == "false") {
                    if ($variant->quantity == 0) {
                        $variant->delete();
                        Log::info("Товар с артикулом {$variant->product_id} и количеством 0 удален.");
                    }
                } else {
                    // В этом случае 'variations' уже будет массивом, так как мы задали кастинг
                    $variations = $variant->variations;

                    // Фильтруем вариации, удаляя те, у которых количество равно 0
                    $filteredVariations = array_filter($variations, function ($variation) {
                        return $variation['quantity'] > 0;
                    });

                    // Если после фильтрации вариации стали пустыми
                    if (empty($filteredVariations)) {
                        // Удаляем связь с магазином, если вариаций не осталось
                        $variant->delete(); // Удаляем запись ShopProduct
                        Log::info("Удалена связь с магазином для продукта: " . $variant->product_id);
                    } else {
                        // Обновляем вариации в базе данных, если остались
                        $variant->update([
                            'variations' => array_values($filteredVariations), // Обновляем поле с новыми вариациями
                        ]);
                        // Log::info("Обновлены вариации для продукта: " . $variant->product_id);
                    }
                }
            }
        }

        $this->info('Очистка завершена. Товары с нулевым количеством были удалены.');
    }
}
