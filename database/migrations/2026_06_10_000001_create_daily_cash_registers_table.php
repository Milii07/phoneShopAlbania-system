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
        Schema::create('daily_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->date('register_date')->unique()->index();
            $table->unsignedBigInteger('employee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed', 'balanced'])->default('open')->index();
            $table->decimal('total_opening', 15, 2)->default(0);
            $table->decimal('total_closing', 15, 2)->default(0);
            $table->decimal('total_transactions', 15, 2)->default(0);
            $table->decimal('total_adjustments', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash_registers');
    }
};
