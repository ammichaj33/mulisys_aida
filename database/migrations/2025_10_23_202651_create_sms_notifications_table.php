<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id('notificationId');
            $table->foreignId('memberIdFk')->constrained('members', 'memberId')->onDelete('cascade');
            $table->foreignId('loanDocIdFk')->nullable()->constrained('loandocs', 'loanDocId')->onDelete('set null');
            $table->text('message');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->timestamp('sentAt')->nullable();
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
        Schema::dropIfExists('sms_notifications');
    }
}
