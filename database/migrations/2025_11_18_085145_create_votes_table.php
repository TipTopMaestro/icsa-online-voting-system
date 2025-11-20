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
        Schema::create('votes', function (Blueprint $table) {
        //vote id
        $table->id();

        // voter who cast the vote
       $table->foreignId('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        // election where the vote belongs
        $table->foreignId('election_id')->references('id')->on('elections')->constrained()->onDelete('cascade');

        // candidate being voted
        $table->foreignId('candidate_id')->references('id')->on('candidates')->constrained()->onDelete('cascade');

        // position of the candidate
        $table->foreignId('position_id')->references('id')->on('positions')->constrained()->onDelete('cascade');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
