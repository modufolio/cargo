<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRedeliveryCountToProofOfDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proof_of_deliveries', function (Blueprint $table) {
            $table->integer('redelivery_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proof_of_deliveries', function (Blueprint $table) {
            $table->dropColumn('redelivery_count');
        });
    }
}
