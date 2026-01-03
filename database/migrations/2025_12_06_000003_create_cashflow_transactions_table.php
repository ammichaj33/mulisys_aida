<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashflowTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashflow_transactions', function (Blueprint $table) {
            $table->id('cashflowTransactionId');
            $table->date('transactionDate');
            $table->enum('transactionType', ['income', 'expense']);
            $table->foreignId('categoryIdFk')->constrained('cashflow_categories', 'categoryId');
            $table->foreignId('accountIdFk')->nullable()->constrained('cashflow_accounts', 'accountId')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->enum('paymentMethod', ['cash', 'bank', 'mobile_money', 'check'])->default('cash');
            $table->string('referenceNumber', 100)->nullable();
            $table->foreignId('loanDocIdFk')->nullable()->constrained('loandocs', 'loanDocId')->onDelete('set null');
            $table->foreignId('memberIdFk')->nullable()->constrained('members', 'memberId')->onDelete('set null');
            $table->foreignId('userIdFk')->constrained('users', 'userId');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamp('confirmedAt')->nullable();
            $table->foreignId('confirmedBy')->nullable()->constrained('users', 'userId')->onDelete('set null');
            $table->string('attachmentPath', 255)->nullable();
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
        Schema::dropIfExists('cashflow_transactions');
    }
}

