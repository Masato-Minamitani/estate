<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FlowManagement;
use App\Support\AdminListSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));

        Application::query()
            ->where('is_cancelled', false)
            ->where('screening_ok', true)
            ->each(fn (Application $application) => FlowManagement::syncFromApplication($application));

        $applications = Application::query()
            ->with(['flowManagement', 'customer'])
            ->where('is_cancelled', false)
            ->tap(fn ($query) => AdminListSearch::applyToApplication($query, $search))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $booleanFields = FlowManagement::booleanFields();
        $columnLabels = FlowManagement::columnLabels();

        return view('admin.applications.index', compact('applications', 'search', 'booleanFields', 'columnLabels'));
    }

    public function updateFlags(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'in:sales_action_required,screening_ok,is_cancelled'],
            'value' => ['required', 'boolean'],
        ]);

        if ($validated['value']) {
            if ($validated['field'] === 'screening_ok' && $application->is_cancelled) {
                return response()->json([
                    'message' => 'キャンセルが選択されているため、審査ＯＫは設定できません。先にキャンセルのチェックを外してください。',
                ], 422);
            }

            if ($validated['field'] === 'is_cancelled' && $application->screening_ok) {
                return response()->json([
                    'message' => '審査ＯＫが選択されているため、キャンセルは設定できません。先に審査ＯＫのチェックを外してください。',
                ], 422);
            }
        }

        $application->update([
            $validated['field'] => $validated['value'],
        ]);

        if ($validated['field'] === 'screening_ok') {
            FlowManagement::syncFromApplication($application->fresh());
        }

        $application->load('flowManagement');

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $application->{$validated['field']},
            'screening_ok' => $application->screening_ok,
            'is_cancelled' => $application->is_cancelled,
            'flow_management_id' => $application->flowManagement?->id,
        ]);
    }

    public function updateField(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'in:memo'],
            'value' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->update([
            $validated['field'] => $validated['value'] !== '' ? $validated['value'] : null,
        ]);

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $application->{$validated['field']},
        ]);
    }
}
