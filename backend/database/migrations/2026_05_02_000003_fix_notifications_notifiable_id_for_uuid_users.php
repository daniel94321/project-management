<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE `notifications` DROP INDEX `notifications_notifiable_type_notifiable_id_index`');
        } catch (\Throwable) {
            // Index may not exist depending on previous schema state.
        }

        DB::statement('ALTER TABLE `notifications` MODIFY `notifiable_id` CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE `notifications` ADD INDEX `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE `notifications` DROP INDEX `notifications_notifiable_type_notifiable_id_index`');
        } catch (\Throwable) {
            // Index may not exist depending on schema state.
        }

        DB::statement('ALTER TABLE `notifications` MODIFY `notifiable_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `notifications` ADD INDEX `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)');
    }
};
