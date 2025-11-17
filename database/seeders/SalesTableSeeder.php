<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesTableSeeder extends Seeder
{
    /**
     * All available agents, customers, suppliers, products
     *
     * @var object
     */
    protected $agents;
    protected $customers;
    protected $suppliers;
    protected $products;

    /**
     * Start running the database seeds
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        // Получаем все существующие агенты, клиенты, поставщики и товары
        $this->agents = Agent::query()->get();
        $this->suppliers = Supplier::query()->get();

        // Начало периода задач (текущий месяц)
        $startDate = Carbon::today()->startOfMonth();
        $endDate = Carbon::today()->endOfMonth();

        $admin = \App\Models\User::query()
            ->where("role", \App\Enums\RoleEnum::ADMIN->value)
            ->first();

        while ($startDate <= $endDate) {
            foreach ($this->agents as $agent) {
                foreach ($this->suppliers as $supplier) {
                    // Выбор случайного клиента и товара
                    $customer = Customer::query()->get()->random();;
                    $product = Product::query()->get()->random();

                    // Задача на доставку
                    DB::table('sales')->insert([
                        'title' => "Доставка заказа {$product->id}",
                        'description' => "Заказ отправлен клиентом #{$customer->id}, доставка должна быть выполнена поставщиком #{$supplier->id}.",
                        'status' => ['pending', 'assigned', 'completed', 'rejected'][array_rand(['pending', 'assigned', 'completed', 'rejected'])],
                        'due_date' => $startDate->format('Y-m-d'),
                        'agent_id' => $agent->id,
                        'customer_id' => $customer->id,
                        'supplier_id' => $supplier->id,
                        'product_id' => $product->id,
                        'created_by_id' => $admin->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Переходим к следующему дню
            $startDate->addDay();
        }

        $sales = Sale::query()
            ->with(["product"])
            ->where("status", "completed")
            ->get();

        foreach ($sales as $sale) {
            $sale->sale_date = Carbon::parse($sale->due_date)->format('Y-m-d');
            $sale->quantity = random_int(1, $sale->product->count);
            $sale->total_price = $sale->quantity * ($sale->product->price ?? 0);
            $sale->save();
        }
    }
}
