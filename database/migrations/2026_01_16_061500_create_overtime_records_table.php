<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('break_hours', 5, 2)->nullable();
            $table->decimal('total_hours_rendered', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->text('purpose_deliverables')->nullable()->comment('Approved Form A');
            $table->text('actual_accomplishment')->nullable()->comment('Approved Form B');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};
