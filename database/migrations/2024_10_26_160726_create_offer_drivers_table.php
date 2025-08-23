<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('offer_id');
            $table->unsignedInteger('driver_id');
            $table->integer('count')->default(0);


            $table->timestamps();

            $table->foreign('offer_id')
            ->references('id')
            ->on('offers')
            ->onDelete('cascade');


            $table->foreign('driver_id')
                    ->references('id')
                    ->on('drivers')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_drivers');
    }
}
