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
        Schema::create('visitor_interests', function (Blueprint $table) {
            $table->id();
            $table->string('interest_type', 32);
            $table->string('status', 32)->default('new');
            $table->string('source', 32)->default('web');
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('company')->nullable();
            $table->string('role', 120)->nullable();
            $table->string('location', 120)->nullable();
            $table->string('investment_range', 120)->nullable();
            $table->string('partnership_area', 120)->nullable();
            $table->text('message');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_interests');
    }
};
