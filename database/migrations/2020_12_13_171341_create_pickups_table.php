<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('fleet_id')->nullable();
            $table->unsignedBigInteger('promo_id')->nullable();
            $table->timestamp('picktime');
            $table->string('name');
            $table->string('phone');
            $table->enum('status', ['request', 'cancel', 'picked'])->default('order');
            // $table->text('address_sender');
            $table->unsignedBigInteger('sender_id');
            // $table->text('address_receiver');
            $table->unsignedBigInteger('receiver_id');
            // $table->text('address_billing');
            $table->unsignedBigInteger('debtor_id');
            $table->text('notes');
            $table->unsignedBigInteger('pickup_plan_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('pickups');
    }
}
