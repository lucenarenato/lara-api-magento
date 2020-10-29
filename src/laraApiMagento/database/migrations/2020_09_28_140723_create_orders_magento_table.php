<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersMagentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_magento', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('customer_id');
            $table->integer('entity_id');
            $table->integer('increment_id');
            $table->string('customer_firstname');
            $table->dateTime('order_date');
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
        Schema::dropIfExists('orders_magento');
    }
}