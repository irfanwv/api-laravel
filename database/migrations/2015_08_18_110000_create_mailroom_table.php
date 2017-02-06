<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMailroomTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("mailroom", function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->integer('price_id')->unsigned();
            $table->foreign('price_id')->references('id')->on('prices');

            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('tags');
            
            $table->integer('number')->unsigned()->nullable();

            $table->string('type')->default('new');
            $table->string('status')->default('pending');

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
        Schema::table("mailroom", function (Blueprint $table)
        {
            $table->dropForeign('mailroom_order_id_foreign');
            $table->dropForeign('mailroom_price_id_foreign');
            $table->dropForeign('mailroom_city_id_foreign');
        });
        
        Schema::drop('mailroom');
    }
}
