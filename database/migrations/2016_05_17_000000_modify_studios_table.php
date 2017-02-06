<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyStudiosTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('studios', function(Blueprint $table)
        {
            $table->renameColumn ('retail', 'is_retailer');
            $table->renameColumn ('retail_only', 'has_classes');
            $table->dropColumn ('branch_no');
            $table->dropColumn ('transit_no');
            $table->dropColumn ('account_no');
        });

        $all = \App\Studios\Studio::withTrashed()->get();

        $retailers = $all->reject(function ($s) { return !$s->has_classes; });
        $studios = $all->reject(function ($s) { return $s->has_classes; });

        $retailers->each(function ($s)
        { 
            $s->has_classes = false;
            $s->is_retailer = false;
            $s->save(); 
        });

        $studios->each (function ($s)
        {
            $s->has_classes = true;
            $s->save(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
