<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_subtractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_record_id')->constrained()->onDelete('cascade');
            $table->decimal('hours_subtracted', 5, 2);
            $table->text('reason');
            $table->date('subtraction_date');
            $table->foreignId('subtracted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_subtractions');
    }
};