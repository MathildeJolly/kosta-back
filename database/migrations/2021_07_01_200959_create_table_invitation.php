<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableInvitation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_invitations', function (Blueprint $table) {
            $table->id();
            $table->integer('fk_sender_id');
            $table->integer('fk_receiver_id');
            $table->integer('fk_album_id');
            $table->string('status')->default(\App\Models\Invitation::WAITING);
            $table->longText('hash');
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
        Schema::dropIfExists('cms_invitations');
    }
}
