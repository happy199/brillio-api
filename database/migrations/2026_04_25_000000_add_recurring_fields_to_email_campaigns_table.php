<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->boolean('is_recurring')->default(false)->after('parent_id');
            $table->string('frequency')->nullable()->after('is_recurring'); // daily, weekly, monthly
            $table->date('start_date')->nullable()->after('frequency');
            $table->date('end_date')->nullable()->after('start_date');
            $table->datetime('next_run_at')->nullable()->after('end_date');
            $table->datetime('last_run_at')->nullable()->after('next_run_at');
            $table->json('recipient_filters')->nullable()->after('last_run_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'is_recurring',
                'frequency',
                'start_date',
                'end_date',
                'next_run_at',
                'last_run_at',
                'recipient_filters',
            ]);
        });
    }
};
