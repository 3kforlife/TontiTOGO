<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('member_code')->unique();
            $table->string('notebook_number');    
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone')->unique();
            $table->enum('gender', ['M', 'F']);
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->foreignId('created_by_agent_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->unique(['organization_id', 'notebook_number'], 'members_org_notebook_unique');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
