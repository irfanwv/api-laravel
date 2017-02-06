<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePassportsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("passports", function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('user_id')->on('customers');

            $table->integer('number');

            $table->integer('city_id')->unsigned()->nullable()->default(null);
            $table->foreign('city_id')->references('id')->on('tags');

            $table->timestamp('activated_at')->nullable()->default(null);
            $table->timestamp('expires_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("passports_studios", function (Blueprint $table)
        {
            $table->integer('passport_id')->unsigned();
            $table->foreign('passport_id')->references('id')->on('passports');

            $table->integer('studio_id')->unsigned();
            $table->foreign('studio_id')->references('owner_id')->on('studios');

            $table->integer('marked_by')->unsigned();
            $table->foreign('marked_by')->references('id')->on('users');

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("passports_studios", function (Blueprint $table)
        {
            $table->dropForeign('passports_studios_passport_id_foreign');
            $table->dropForeign('passports_studios_studio_id_foreign');
            $table->dropForeign('passports_studios_marked_by_foreign');
        });
        
        Schema::drop('passports_studios');

        Schema::table("passports", function (Blueprint $table)
        {
            $table->dropForeign('passports_city_id_foreign');
            $table->dropForeign('passports_customer_id_foreign');
        });
        
        Schema::drop('passports');
    }
}
