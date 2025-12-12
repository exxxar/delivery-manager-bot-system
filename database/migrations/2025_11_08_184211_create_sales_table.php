<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {


        Schema::disableForeignKeyConstraints();

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('title'); //название задачи
            $table->text('description'); //описание задания
            $table->enum('status', ["pending","assigned","delivered","completed",'rejected']); // статус задачи
            $table->date('due_date')->nullable(); // дата назначения встречи
            $table->date('sale_date')->nullable(); //дата продажи
            $table->date('actual_delivery_date')->nullable(); //реальная фактическая дата доставки
            $table->integer('quantity')->default(0); //доставляемое число товара
            $table->decimal('total_price')->default(0); //сумма заказа
            $table->smallInteger('payment_type')->default(0); //способ оплаты
            $table->string('payment_document_name')->nullable(); //скриншот платежного документа
            $table->foreignId('agent_id')->nullable()->constrained('agents'); //агент, который взял в работу
            $table->foreignId('customer_id')->nullable()->constrained("customers"); //покупатель, которому доставляется (может не быть)
            $table->foreignId('supplier_id')->nullable()->constrained("suppliers"); //поставщик, чей товар доставляется
            $table->foreignId('product_id')->nullable()->constrained("products"); // продукт, который доставляется (может не быть)
            $table->foreignId('created_by_id')->nullable()->constrained("users"); //администратор, который сформировал задачу
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
