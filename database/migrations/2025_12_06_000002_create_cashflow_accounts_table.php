<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashflowAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashflow_accounts', function (Blueprint $table) {
            $table->id('accountId');
            $table->string('accountName', 100);
            $table->enum('accountType', ['cash', 'bank', 'mobile_money']);
            $table->decimal('initialBalance', 15, 2)->default(0);
            $table->decimal('currentBalance', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
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
        Schema::dropIfExists('cashflow_accounts');
    }
}

