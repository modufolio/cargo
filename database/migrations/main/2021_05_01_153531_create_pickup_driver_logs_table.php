<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickupDriverLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickup_driver_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('branch_from')->nullable();
            $table->unsignedBigInteger('branch_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pickup_driver_logs');
    }
}
