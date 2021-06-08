<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableMedias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media',function (Blueprint  $table){
           $table->date('media_date')->nullable();
            $table->string('chunk_id')->nullable();
            $table->bigInteger('chunk_order')->nullable();
            $table->bigInteger('order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media',function (Blueprint  $table){
            $table->dropColumn('media_date');
            $table->dropColumn('chunk_id');
            $table->dropColumn('chunk_order');
            $table->dropColumn('order');
        });
    }
}
