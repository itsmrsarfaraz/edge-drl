<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table) {
            $table->json('training_curve')->nullable()->after('error_log');
            $table->float('train_mean_reward')->nullable()->after('training_curve');
            $table->float('eval_mean_reward')->nullable()->after('train_mean_reward');
            $table->float('eval_std_reward')->nullable()->after('eval_mean_reward');
            $table->float('eval_min_reward')->nullable()->after('eval_std_reward');
            $table->float('eval_max_reward')->nullable()->after('eval_min_reward');
            $table->float('eval_success_rate')->nullable()->after('eval_max_reward');
            $table->integer('eval_episodes')->nullable()->after('eval_success_rate');
            $table->json('eval_all_rewards')->nullable()->after('eval_episodes');
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table) {
            $table->dropColumn([
                'training_curve', 'train_mean_reward',
                'eval_mean_reward', 'eval_std_reward',
                'eval_min_reward', 'eval_max_reward',
                'eval_success_rate', 'eval_episodes', 'eval_all_rewards',
            ]);
        });
    }
};