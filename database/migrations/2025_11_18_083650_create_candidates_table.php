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
        Schema::create('candidates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->references('id')->on('users')->constrained()->onDelete('cascade');
        $table->foreignId('election_id')->references('id')->on('elections')->constrained()->onDelete('cascade');
        $table->string('position');
        $table->string('partylist')->nullable();
        $table->integer('votes_count')->default(0);
        //platform or agenda of the candidate
        $table->text('platform')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
