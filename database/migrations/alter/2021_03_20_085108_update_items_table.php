<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('volume')->default('0');
            $table->string('volume_unit')->default('M3');
            $table->string('weight')->default('0');
            $table->string('weight_unit')->default('Kg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('volume');
            $table->dropColumn('volume_unit');
            $table->dropColumn('weight');
            $table->dropColumn('weight_unit');
        });
    }
}
