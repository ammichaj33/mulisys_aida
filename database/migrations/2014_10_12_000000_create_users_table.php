<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('userId');
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('fullName', 100);
            $table->enum('role', ['receptionniste', 'charge_credits', 'gerant', 'caissiere', 'directeur', 'membre']);
            $table->string('phoneNumber', 20)->nullable();
            $table->string('email', 100)->nullable();
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
        Schema::dropIfExists('users');
    }
}
