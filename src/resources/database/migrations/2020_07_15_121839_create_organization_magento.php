<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationMagento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations_magento', function (Blueprint $table) {
            $table->bigInteger('customer_id')->unique();
            $table->bigInteger('organization_id')->unique()->unsigned();

            $table->foreign('organization_id')->references('id')->on('organizations');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations_magento');
    }
}
