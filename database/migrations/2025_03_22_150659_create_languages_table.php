<?php

use App\Models\Job;
use App\Models\Language;
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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('job_language', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Job::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Language::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_language');
        Schema::dropIfExists('languages');
    }
};
