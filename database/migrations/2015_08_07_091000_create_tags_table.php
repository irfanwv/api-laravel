<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tags', function(Blueprint $table)
		{
			$table->increments('id');
			
			$table->integer('parent_id')
				->unsigned()
				->nullable()
				->default(null);
			
			$table->foreign('parent_id')
				->references('id')
				->on('tags');
			
			$table->string('name');
			$table->string('type');

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
        Schema::table("tags", function (Blueprint $table)
        {
            $table->dropForeign('tags_parent_id_foreign');
        });

		Schema::drop('tags');
	}

}
