<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Support\ManagementCompanySuggestions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationFormController extends Controller
{
    public function create(): View
    {
        return view('applications.create');
    }

    public function managementCompanySuggestions(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');

        return response()->json(
            ManagementCompanySuggestions::search($query)
        );
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $hasBrokerFee = match ($validated['has_broker_fee']) {
            '1' => true,
            '0' => false,
            'undecided' => null,
        };

        $application = Application::create([
            ...collect($validated)->except(['has_broker_fee', 'broker_fee', 'customer_id'])->all(),
            'customer_id' => null,
            'has_broker_fee' => $hasBrokerFee,
            'broker_fee' => $hasBrokerFee === true ? $validated['broker_fee'] : null,
            'sales_action_required' => false,
            'screening_ok' => false,
            'is_cancelled' => false,
        ]);

        return redirect()
            ->route('applications.complete', $application)
            ->with('success', '申込情報を登録しました。');
    }

    public function complete(Application $application): View
    {
        return view('applications.complete', compact('application'));
    }
}
