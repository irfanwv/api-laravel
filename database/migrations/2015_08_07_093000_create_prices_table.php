<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePricesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('prices', function(Blueprint $table)
		{
			$table->increments('id');
			
			$table->integer('area_id')
				->unsigned();
			
			$table->foreign('area_id')
				->references('id')
				->on('tags');
			
			$table->decimal('unit_price', 5, 2)->default(30);
			$table->decimal('bulk_price', 5, 2)->default(30);
			$table->decimal('extend_1', 5, 2)->default(10);
			$table->decimal('extend_3', 5, 2)->default(15);
			$table->decimal('extend_6', 5, 2)->default(20);
			$table->decimal('lost', 5, 2)->default(5);

			$table->decimal('tax_rate', 2, 2)->default(0.13);

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
        Schema::table("prices", function (Blueprint $table)
        {
            $table->dropForeign('prices_area_id_foreign');
        });

		Schema::drop('prices');
	}

}
