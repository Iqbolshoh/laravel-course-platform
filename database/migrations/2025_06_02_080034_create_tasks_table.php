<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->integer('duration')->default(60);
            $table->timestamps();
        });

        Schema::create('task_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['single_choice', 'multiple_choice'])->default('single_choice');
            $table->integer('score')->default(1);
            $table->timestamps();
        });

        Schema::create('task_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('task_questions')->onDelete('cascade');
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('task_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('score')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_options');
        Schema::dropIfExists('task_questions');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_results');
    }
};
