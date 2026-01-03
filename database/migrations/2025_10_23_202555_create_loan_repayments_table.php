<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loanrepayment', function (Blueprint $table) {
            $table->id('loanRepaymentId');
            $table->decimal('amount', 15, 2);
            $table->foreignId('loanDocIdFk')->constrained('loandocs', 'loanDocId')->onDelete('cascade');
            $table->date('repaymentDate');
            $table->foreignId('repaymentTypeIdFk')->constrained('repaymenttype', 'repaymentTypeID');
            $table->foreignId('userIdFk')->constrained('users', 'userId')->onDelete('cascade');
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
        Schema::dropIfExists('loanrepayment');
    }
}
