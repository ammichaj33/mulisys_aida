<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToLoanrepaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loanrepayment', function (Blueprint $table) {
            $table->text('description')->nullable()->after('userIdFk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loanrepayment', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
