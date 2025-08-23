<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnServiceLoacationIdToFaqs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('faqs', function (Blueprint $table) {
            // Add the service_location_id column
            $table->uuid('service_location_id')->nullable(); // Nullable if necessary

            // Add the foreign key constraint
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
        Schema::table('faqs', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['service_location_id']);

            // Then drop the column
            $table->dropColumn('service_location_id');
        });
    }
}
