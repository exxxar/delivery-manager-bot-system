<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{


    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {

        Schema::disableForeignKeyConstraints();
        // Очистим таблицу, если ранее была её засеяна
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();
        // Массив имен и емейлов
        $names = [
            'Иван Петров',
            'Марина Иванова',
            'Семен Сидоров',
            'Анна Сергеева',
            'Василий Ульянов',
            'Алёна Ильина',
            'Николай Федоров',
            'Александра Кузнецова',
            'Борис Воробьёв',
            'Елизавета Чернышёва',
            'Артем Голубев',
            'Дарья Тихонова',
            'Максим Матвеев',
            'Карина Павлова',
            'Антон Савченко',
            'Людмила Ромашкина',
            'Геннадий Александров',
            'Варвара Фёдоровна',
            'Константин Нестеров',
            'Арина Куликова',
            'Ева Супова',
            'Аглая Тарасова'
        ];

        // Циклом добавляем пользователей
        foreach ($names as $name) {
            list($firstName, $lastName) = explode(' ', $name);

            DB::table('users')->insert([
                'name' => $name,
                'email' => Str::slug($firstName) . '.' . Str::slug($lastName) . '@example.com',
                'fio_from_telegram' => Str::slug($firstName) . '.' . Str::slug($lastName),
                'telegram_chat_id' => null,
                'role' => random_int(0, 4),
                'percent' => round(rand(1, 100) / 10, 2),
                'email_verified_at' => now(),
                'password' => Hash::make('secret'), // Пароль secret
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
