<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableMediasForChunk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint  $table){
           $table->string('media_date')->change();
        });

        Schema::table('cms_invitations', function (Blueprint  $table){
            $table->string('fk_receiver_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::table('media', function (Blueprint  $table){
        //    $table->date('media_date')->change();
        //});
    }
}