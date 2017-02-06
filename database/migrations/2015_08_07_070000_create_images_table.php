<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('images', function(Blueprint $table)
		{
			$table->increments('id');
			
			$table->integer('owner_id')->unsigned();
			$table->string('owner_type');

			$table->string('content');
			
			$table->string('path');
			$table->string('filename');
			$table->string('extension');
			$table->string('mime');
			$table->string('size');
			$table->string('caption')->nullable()->default(null);

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
		Schema::drop('images');
	}

}
