<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->decimal('required_hours', 5, 2)->nullable()->after('total_hours_rendered');
        });
    }

    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropColumn('required_hours');
        });
    }
};
