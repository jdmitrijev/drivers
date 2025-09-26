<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_expenses_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_expense_id')->constrained('trip_expenses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['trip_expense_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_expenses_drivers');
    }
};


