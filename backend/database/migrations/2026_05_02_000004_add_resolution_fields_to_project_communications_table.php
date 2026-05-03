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
        Schema::table('project_communications', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('message');
            $table->text('response')->nullable()->after('status');
            $table->foreignUuid('resolved_by')->nullable()->after('response')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_communications', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn('resolved_by');
            $table->dropColumn(['status', 'response', 'resolved_at']);
        });
    }
};