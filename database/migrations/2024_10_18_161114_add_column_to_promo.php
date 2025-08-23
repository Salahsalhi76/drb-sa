<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPromo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promo', function (Blueprint $table) {
            $table->enum('promo_type', ['squential', 'no-squential'])->default('squential');
        	$table->string('sequential_times')->nullable()->default(null);
        	$table->json('discount_sequential')->nullable()->default(null);
        });
    
    	 Schema::table('promo_users', function (Blueprint $table) {
            $table->integer('time_left')->nullable()->default(null);
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promo', function (Blueprint $table) {
        	$table->dropColumn('promo_type');
        });
    
    	Schema::table('promo_users', function (Blueprint $table) {
        	$table->dropColumn('time_left');
        });
    }
}
