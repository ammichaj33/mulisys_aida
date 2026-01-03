<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenaltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penalities', function (Blueprint $table) {
            $table->id('penalityId');
            $table->timestamp('recordedDate')->useCurrent();
            $table->foreignId('loanDocIdFk')->constrained('loandocs', 'loanDocId')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['paid', 'notPaid'])->default('notPaid');
            $table->timestamp('paidAt')->nullable();
            $table->decimal('paidAmount', 15, 2)->nullable();
            $table->enum('reason', ['retard', 'montant_inferieur']);
            $table->text('description')->nullable();
            $table->foreignId('memberIdFk')->nullable()->constrained('members', 'memberId')->onDelete('cascade');
            $table->foreignId('createdBy')->nullable()->constrained('users', 'userId')->onDelete('set null');
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
        Schema::dropIfExists('penalities');
    }
}
