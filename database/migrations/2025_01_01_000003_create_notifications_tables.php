<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel's standard in-app notifications table.
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Remembers which reminders were already sent today, so nobody gets duplicates.
        Schema::create('task_reminder_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');   // 0 = daily team summary
            $table->unsignedBigInteger('user_id');
            $table->date('sent_on');
            $table->unique(['task_id', 'user_id', 'sent_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reminder_log');
        Schema::dropIfExists('notifications');
    }
};
