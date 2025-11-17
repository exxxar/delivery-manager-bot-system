<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    /**
     * Все имеющиеся поставщики и категории продуктов
     *
     * @var object
     */
    protected $suppliers;
    protected $categories;

    /**
     * Запускаем загрузчик данных
     *
     * @return void
     */
    public function run()
    {
        // Получаем все доступные ID поставщиков и категорий
        $this->suppliers = DB::table('suppliers')->select('id')->get();
        $this->categories = DB::table('product_categories')->select('id')->get();

        // Количество продуктов для добавления
        $numProducts = 50;

        for ($i = 0; $i < $numProducts; $i++) {
            // Выбираем случайного поставщика и категорию
            $supplierId = $this->suppliers[rand(0, count($this->suppliers) - 1)]->id;
            $categoryId = $this->categories[rand(0, count($this->categories) - 1)]->id;

            // Генерация случайных данных
            $name = "Продукт №{$i}";
            $description = "Описание продукта №{$i}. Отличается высоким качеством.";
            $price = round(rand(100, 10000) / 100, 2); // цена от 1 рубля до 100 рублей
            $count = rand(10, 1000); // количество товара от 10 до 1000 единиц

            // Вставка данных
            DB::table('products')->insert([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'count' => $count,
                'supplier_id' => $supplierId,
                'product_category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
