<?php

use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_managements', function (Blueprint $table) {
            $table->string('fee_type', 20)->nullable()->after('flow_management_id')->comment('手数料種別（advertising / broker_fee）');
        });

        FlowManagement::query()
            ->where('settlement_transition', true)
            ->each(fn (FlowManagement $flowManagement) => SettlementManagement::syncFromFlowManagement($flowManagement));

        Schema::table('settlement_managements', function (Blueprint $table) {
            $table->unique(['flow_management_id', 'fee_type']);
        });
    }

    public function down(): void
    {
        Schema::table('settlement_managements', function (Blueprint $table) {
            $table->dropUnique(['flow_management_id', 'fee_type']);
            $table->dropColumn('fee_type');
        });
    }
};
