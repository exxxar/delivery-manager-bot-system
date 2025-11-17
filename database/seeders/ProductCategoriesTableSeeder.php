<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoriesTableSeeder extends Seeder
{
    /**
     * Продуктовые категории и их описания
     *
     * @var array
     */
    protected $categories = [
        [
            'name' => 'Мясо и птица',
            'description' => 'Свежие мясные продукты, полуфабрикаты и деликатесы.'
        ],
        [
            'name' => 'Рыба и морепродукты',
            'description' => 'Свежая и замороженная рыба, креветки, кальмары и прочие морские продукты.'
        ],
        [
            'name' => 'Молочные изделия',
            'description' => 'Сыр, молоко, йогурт, творог, сливочное масло и мороженое.'
        ],
        [
            'name' => 'Овощи и фрукты',
            'description' => 'Свежие сезонные овощи и фрукты, ягоды и зелень.'
        ],
        [
            'name' => 'Выпечка и хлебобулочные изделия',
            'description' => 'Хлеб, пироги, печенье, торты и сладкая выпечка.'
        ],
        [
            'name' => 'Напитки',
            'description' => 'Безалкогольные напитки, соки, минеральная вода, чай и кофе.'
        ],
        [
            'name' => 'Крупы и макаронные изделия',
            'description' => 'Рис, гречка, овсянка, спагетти, вермишель и прочее.'
        ],
        [
            'name' => 'Кондитерские изделия',
            'description' => 'Шоколад, конфеты, карамель, мармелад и пастила.'
        ],
        [
            'name' => 'Замороженные продукты',
            'description' => 'Пельмени, вареники, пиццы, блины и десерты.'
        ],
        [
            'name' => 'Специи и приправы',
            'description' => 'Перец, соль, корица, лавровый лист и др.'
        ]
    ];

    /**
     * Заполнение таблицы product_categories
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories as $category) {
            DB::table('product_categories')->insert([
                'name' => $category['name'],
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
