<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tontine_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tontine_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->decimal('chosen_amount', 12, 2);
            $table->date('joined_at');
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->timestamps();
            $table->unique(['tontine_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tontine_participants');
    }
};
