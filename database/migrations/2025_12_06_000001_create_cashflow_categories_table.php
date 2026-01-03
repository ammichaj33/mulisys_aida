<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashflowCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashflow_categories', function (Blueprint $table) {
            $table->id('categoryId');
            $table->string('categoryName', 100);
            $table->enum('categoryType', ['income', 'expense']);
            $table->foreignId('parentCategoryId')->nullable()->constrained('cashflow_categories', 'categoryId')->onDelete('set null');
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
        Schema::dropIfExists('cashflow_categories');
    }
}

