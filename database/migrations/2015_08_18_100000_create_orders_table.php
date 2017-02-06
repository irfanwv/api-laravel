<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("orders", function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('charge_id')->nullable();
            $table->index('charge_id');

            $table->integer('customer_id')->unsigned();
            $table->foreign('customer_id')->references('user_id')->on('customers');

            $table->integer('shipping_id')->unsigned()->nullable();
            $table->foreign('shipping_id')->references('id')->on('locations');

            $table->string('coupon')->nullable()->default(null);
            $table->string('currency')->default('cad');

            $table->decimal('subtotal', 7, 2);
            $table->decimal('shipping', 7, 2);
            $table->decimal('discount', 7, 2)->default(0);
            $table->decimal('taxrate', 2, 2);
            $table->decimal('taxes', 7, 2);
            $table->decimal('total', 7, 2);

            $table->string('source');
            $table->string('status');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("orders", function (Blueprint $table)
        {
            $table->dropForeign('orders_customer_id_foreign');
            $table->dropForeign('orders_shipping_id_foreign');
        });
        
        Schema::drop('orders');
    }
}
