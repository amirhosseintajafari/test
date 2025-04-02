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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('amount');
            $table->enum('status',['send_to_bank','paid','failed','canceled','reversed','pending','blocked','under_review'])->default('send_to_bank');
            $table->string('gateway_name')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedSmallInteger('order_id')->nullable()->unique();
            $table->unsignedSmallInteger('transaction_code')->nullable()->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('creator_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
