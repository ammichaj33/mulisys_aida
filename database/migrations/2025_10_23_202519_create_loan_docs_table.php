<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loandocs', function (Blueprint $table) {
            $table->id('loanDocId');
            $table->string('refNumber', 20)->unique();
            $table->date('submitDate');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'accepted', 'rejected', 'validated', 'done', 'toreviewed', 'finalized'])->default('draft');
            $table->decimal('requestAmount', 15, 2);
            $table->integer('loanMonths');
            $table->decimal('interestRate', 5, 2)->default(10.00);
            $table->foreignId('memberIdFk')->constrained('members', 'memberId')->onDelete('cascade');
            $table->string('docPath', 255)->nullable();
            $table->date('endedDate')->nullable();
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
        Schema::dropIfExists('loandocs');
    }
}
