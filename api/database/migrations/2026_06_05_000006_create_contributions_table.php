<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tontine_participant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('reference')->unique();       
            $table->decimal('amount', 12, 2);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('settlement_status', ['pending', 'settled'])->default('pending');
            $table->unique(['tontine_participant_id', 'created_at'], 'contributions_participant_date_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
