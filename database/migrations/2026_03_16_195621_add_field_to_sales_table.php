<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("CREATE INDEX idx_sales_agent_id ON sales(agent_id)");
        DB::statement("CREATE INDEX idx_sales_customer_id ON sales(customer_id)");
        DB::statement("CREATE INDEX idx_sales_supplier_id ON sales(supplier_id)");
        DB::statement("CREATE INDEX idx_sales_product_id ON sales(product_id)");
        DB::statement("CREATE INDEX idx_sales_created_by_id ON sales(created_by_id)");
        DB::statement("CREATE INDEX idx_sales_product_category_id ON sales(product_category_id)");

        DB::statement("CREATE INDEX idx_sales_status ON sales(status)");
        DB::statement("CREATE INDEX idx_sales_sale_date ON sales(sale_date)");
        DB::statement("CREATE INDEX idx_sales_due_date ON sales(due_date)");
        DB::statement("CREATE INDEX idx_sales_actual_delivery_date ON sales(actual_delivery_date)");
        DB::statement("CREATE INDEX idx_sales_total_price ON sales(total_price)");

        DB::statement("CREATE INDEX idx_sales_agent_status ON sales(agent_id, status)");
        DB::statement("CREATE INDEX idx_sales_sale_date_status ON sales(sale_date, status)");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
