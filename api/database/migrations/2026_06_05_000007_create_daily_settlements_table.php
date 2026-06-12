<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('validated_by_responsible_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->date('date_settled');
            $table->decimal('expected_amount', 12, 2);
            $table->decimal('received_amount', 12, 2);
            $table->enum('status', ['validated', 'discrepancy'])->default('validated');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['agent_id', 'date_settled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_settlements');
    }
};
