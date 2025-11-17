<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersTableSeeder extends Seeder
{
    protected $customers = [
        [
            'name' => 'Иван Петров',
            'company_name' => 'ООО \"Север\"',
            'address' => 'Москва, Ленинский проспект, д. 34',
            'phone' => '+79991234567',
            'email' => 'ivan_petrov@example.ru'
        ],
        [
            'name' => 'Анна Смирнова',
            'company_name' => 'ЗАО \"Южное сияние\"',
            'address' => 'Санкт-Петербург, Васильевский остров, д. 15',
            'phone' => '+79991234568',
            'email' => 'anna_smirnova@example.ru'
        ],
        [
            'name' => 'Алексей Иванов',
            'company_name' => 'ИП \"Центр здоровья\"',
            'address' => 'Новосибирск, Красный проспект, д. 10',
            'phone' => '+79991234569',
            'email' => 'aleksey_ivanov@example.ru'
        ],
        [
            'name' => 'Светлана Кузнецова',
            'company_name' => 'ООО \"Восточный ветер\"',
            'address' => 'Екатеринбург, ул. Гагарина, д. 15',
            'phone' => '+79991234570',
            'email' => 'svetlana_kuznetsova@example.ru'
        ],
        [
            'name' => 'Денис Николаев',
            'company_name' => 'ИП \"Снежинка\"',
            'address' => 'Казань, ул. Татарстан, д. 10',
            'phone' => '+79991234571',
            'email' => 'denis_nikolaev@example.ru'
        ],
        [
            'name' => 'Татьяна Алексеева',
            'company_name' => 'ООО \"Теплый уют\"',
            'address' => 'Ростов-на-Дону, ул. Пушкинская, д. 15',
            'phone' => '+79991234572',
            'email' => 'tatyana_alekseeva@example.ru'
        ],
        [
            'name' => 'Сергей Крылов',
            'company_name' => 'ЗАО \"Новый век\"',
            'address' => 'Самара, Московское шоссе, д. 10',
            'phone' => '+79991234573',
            'email' => 'sergey_krylov@example.ru'
        ],
        [
            'name' => 'Ольга Гордеева',
            'company_name' => 'ООО \"Веселый урожай\"',
            'address' => 'Краснодар, ул. Красная, д. 15',
            'phone' => '+79991234574',
            'email' => 'olga_gordeeva@example.ru'
        ],
        [
            'name' => 'Дмитрий Соловьев',
            'company_name' => 'ИП \"Солнечные лучи\"',
            'address' => 'Омск, ул. Ленина, д. 10',
            'phone' => '+79991234575',
            'email' => 'dmitriy_solovyev@example.ru'
        ],
        [
            'name' => 'Наталия Потапова',
            'company_name' => 'ООО \"Мир растений\"',
            'address' => 'Челябинск, ул. Труда, д. 15',
            'phone' => '+79991234576',
            'email' => 'natalija_potapova@example.ru'
        ]
    ];

    /**
     * Fill the customers table with data
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->customers as $customerData) {
            DB::table('customers')->insert($customerData);
        }
    }
}
