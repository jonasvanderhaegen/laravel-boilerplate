<?php

declare(strict_types=1);

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('firstname')->after('name')->nullable();
            $table->string('lastname')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
