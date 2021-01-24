<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickupPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickup_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_id');
            $table->string('status');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('pickup_plans');
    }
}
