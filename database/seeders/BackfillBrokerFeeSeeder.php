<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use Illuminate\Database\Seeder;

class BackfillBrokerFeeSeeder extends Seeder
{
    public function run(): void
    {
        Application::query()->each(function (Application $application) {
            $hasBrokerFee = (bool) random_int(0, 1);

            $application->update([
                'has_broker_fee' => $hasBrokerFee,
                'broker_fee' => $hasBrokerFee
                    ? random_int(1, 50) * 10000
                    : null,
            ]);
        });

        FlowManagement::query()
            ->where('settlement_transition', true)
            ->each(fn (FlowManagement $flowManagement) => SettlementManagement::syncFromFlowManagement($flowManagement));
    }
}
