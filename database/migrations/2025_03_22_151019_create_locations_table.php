<?php

use App\Models\Job;
use App\Models\Location;
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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('state');
            $table->string('country');
        });

        Schema::create('job_location', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Job::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_location');
        Schema::dropIfExists('locations');
    }
};
