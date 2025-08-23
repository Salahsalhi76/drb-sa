<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_location_id');
            $table->longText('subject')->nullable()->default(null);
            $table->integer('request_number');
            $table->decimal('earning_price');
            $table->enum('user_type',['user', 'driver'])->default('driver');


            $table->dateTime('from_date');

            $table->dateTime('to_date');
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->foreign('service_location_id')
            ->references('id')
            ->on('service_locations')
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
        Schema::dropIfExists('offers');
    }
}
