<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterestCancellationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interest_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loanDocIdFk')->constrained('loandocs', 'loanDocId')->onDelete('cascade');
            $table->foreignId('repaymentIdFk')->constrained('loanrepayment', 'loanRepaymentId')->onDelete('cascade');
            $table->string('cancelledMonth', 7);
            $table->decimal('cancelledInterestAmount', 15, 2);
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
        Schema::dropIfExists('interest_cancellations');
    }
}
