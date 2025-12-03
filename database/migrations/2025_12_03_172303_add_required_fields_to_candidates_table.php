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
        Schema::table('candidates', function (Blueprint $table) {
            // Add photo field
            $table->string('photo')->after('platform');
            
            // Add course and year from voter profile info
            $table->string('course')->after('photo');
            $table->string('year_level')->after('course');
            $table->string('section')->nullable()->after('year_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['photo', 'course', 'year_level', 'section']);
        });
    }
};
