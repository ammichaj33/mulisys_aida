<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriqueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historique', function (Blueprint $table) {
            $table->id('operationId');
            $table->unsignedBigInteger('recordIdFk');
            $table->string('recordStatus', 50);
            $table->timestamp('trackedDate')->useCurrent();
            $table->text('operDescription');
            $table->foreignId('userIdFk')->constrained('users', 'userId')->onDelete('cascade');
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
        Schema::dropIfExists('historique');
    }
}
