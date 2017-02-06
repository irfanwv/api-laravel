<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            
            $table->increments('id');
            
            $table->integer('display_id')
                ->unsigned()
                ->nullable()
                ->default(null);
            
            $table->string('email')
                ->nullable() // not everyone needs to log in
                ->default(null)
                ->unique();
            
            $table->string('first_name')
                ->nullable()
                ->default(null);
            
            $table->string('last_name')
                ->nullable()
                ->default(null);
            
            $table->string('phone')
                ->nullable()
                ->default(null);

            $table->boolean('promo')->default(false);

            $table->boolean('active')
                ->default(false);
            
            $table->string('activation_code')
                ->nullable()
                ->default(null);

            $table->string('password', 60)
                ->nullable()
                ->default(null);
            
            $table->rememberToken();
            
            $table->timestamp('last_activity')
                ->nullable()
                ->default(null);
            
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
        Schema::drop('users');
    }
}
