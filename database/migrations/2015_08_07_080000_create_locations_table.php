<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locations', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('owner_id')->unsigned();
			$table->string('owner_type');
			
			$table->string('type')->nullable()->default(null);
			$table->string('referrer')->nullable()->default(null);
			
			$table->string('address1');
			$table->string('address2')->nullable()->default(null);
			$table->string('neighbourhood')->nullable()->default(null);
			$table->string('postal')->nullable()->default(null);
			$table->string('city');
			$table->string('province');
			$table->string('country');
			
			$table->string('lat');
			$table->string('lng');
			
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
		Schema::drop('locations');
	}

}
