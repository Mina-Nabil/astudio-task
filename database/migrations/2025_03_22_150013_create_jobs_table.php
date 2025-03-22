<?php

use App\Models\Job;
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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('company_name');
            $table->decimal('salary_min', 10, 2);
            $table->decimal('salary_max', 10, 2);
            $table->boolean('is_remote');
            $table->enum('job_type', Job::JOB_TYPE_LIST);
            $table->enum('status', Job::STATUS_LIST);
            $table->timestamp('published_at');
            $table->timestamps();
        });

       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
