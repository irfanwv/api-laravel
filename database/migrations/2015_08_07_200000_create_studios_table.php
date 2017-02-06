<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStudiosTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("studios", function (Blueprint $table)
        {
            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');

            $table->integer('location_id')->unsigned()->nullable()->default(null);
            $table->foreign('location_id')->references('id')->on('locations');

            $table->integer('area_id')->unsigned();
            $table->foreign('area_id')->references('id')->on('tags');

            $table->string('name');
            $table->string('phone')->nullable()->default(null);
            $table->string('email')->nullable()->default(null);
            $table->string('website')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->boolean('retail')->default(false);
            $table->boolean('retail_only')->default(false);

            $table->string('branch_no')->nullable()->default(null);
            $table->string('account_no')->nullable()->default(null);
            $table->string('transit_no')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();

            $table->primary('owner_id');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("studios", function (Blueprint $table)
        {
            $table->dropPrimary('studios_owner_id_primary');
            $table->dropForeign('studios_owner_id_foreign');
            $table->dropForeign('studios_location_id_foreign');
            $table->dropForeign('studios_area_id_foreign');
        });
        
        Schema::drop('studios');
    }
}
