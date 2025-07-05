<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
             $table->foreign('transaction_id')
                  ->references('id')
                  ->on('transaction_headers')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('transaction_category_id');
            $table->string('name');
            $table->double('value_idr');
            $table->tinyInteger('group')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
