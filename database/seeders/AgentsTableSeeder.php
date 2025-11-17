<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgentsTableSeeder extends Seeder
{


    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {

        $supplierIds = Supplier::query()->pluck('id'); // Получаем все существующие ID поставщиков

        // Массивы регионов и телефонных номеров
        $regions = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Нижний Новгород'];
        $phones = ['+79991234567', '+79991234568', '+79991234569', '+79991234570', '+79991234571'];

        // Данные пользователей
        $users = User::query()->get();

        $index = 1;
        foreach ($supplierIds as $key => $supplierId) {
            // Выбираем случайный индекс региона и телефона
            $regionIndex = random_int(0, count($regions) - 1);
            $phoneIndex = random_int(0, count($phones) - 1);

            // Генератор имени агента
            $agentName = $users[$index]->name ?? "Агент_$key";


            // Вставляем записи
            DB::table('agents')->insert([
                'user_id' => $users[$index]->id,
                'name' => $agentName,
                'phone' => $phones[$phoneIndex],
                'email' => $users[$index]->email,
                'region' => $regions[$regionIndex],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $index = $index >= count($users)-1 ? 1 : $index + 1;
        }
    }
}
