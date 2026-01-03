<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id('memberId');
            $table->string('firstName', 50);
            $table->string('lastName', 50);
            $table->string('phoneNumber', 20);
            $table->date('birthDate');
            $table->enum('gender', ['M', 'F']);
            $table->text('address');
            $table->string('email', 100)->nullable();
            $table->string('institutionFrom', 100)->default('Non spécifié');
            $table->string('photo', 255)->nullable();
            $table->string('idCard', 255)->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
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
        Schema::dropIfExists('members');
    }
}
