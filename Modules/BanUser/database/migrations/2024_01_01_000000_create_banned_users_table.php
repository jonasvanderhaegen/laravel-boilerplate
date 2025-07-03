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
        Schema::create('banned_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('ip_address')->nullable()->index();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('banned_by')->nullable(); // Could be system, admin username, etc.
            $table->timestamp('banned_at');
            $table->timestamp('expires_at')->nullable(); // NULL means permanent ban
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Composite indexes for common queries
            $table->index(['email', 'is_active']);
            $table->index(['ip_address', 'is_active']);
            $table->index(['expires_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banned_users');
    }
};
