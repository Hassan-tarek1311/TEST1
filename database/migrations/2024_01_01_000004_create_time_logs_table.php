<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // هذا الجدول هو قلب النظام - بيسجل كل ساعة اشتغلها الموظف
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('project_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->dateTime('started_at');               // وقت بداية الشغل
            $table->dateTime('ended_at')->nullable();     // وقت نهاية الشغل (null = لسه شغّال)
            $table->integer('duration_minutes')
                  ->nullable();                           // المدة بالدقائق (بتتحسب تلقائياً)
            $table->text('notes')->nullable();            // ملاحظات الموظف
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
