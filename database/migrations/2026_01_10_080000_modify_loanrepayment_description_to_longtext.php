<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyLoanrepaymentDescriptionToLongtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modifier la colonne description en LONGTEXT pour permettre plus de caractères
        DB::statement('ALTER TABLE `loanrepayment` MODIFY COLUMN `description` LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revenir à TEXT (mais garder nullable)
        DB::statement('ALTER TABLE `loanrepayment` MODIFY COLUMN `description` TEXT NULL');
    }
}

