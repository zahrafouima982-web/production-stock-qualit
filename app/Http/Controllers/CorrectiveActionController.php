<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreCorrectiveActionRequest;
use App\Http\Requests\Quality\UpdateCorrectiveActionRequest;
use App\Models\CorrectiveAction;
use App\Models\QualityDefect;
use App\Services\QualityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CorrectiveActionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private QualityService $qualityService)
    {
    }

    public function create(QualityDefect $defect): View
    {
        $this->authorize('create', CorrectiveAction::class);

        return view('quality.corrective-actions.create', compact('defect'));
    }

    public function store(StoreCorrectiveActionRequest $request, QualityDefect $defect): RedirectResponse
    {
        $this->authorize('create', CorrectiveAction::class);

        $action = $this->qualityService->createCorrectiveAction($request->validated(), $defect, $request->user());

        return redirect()->route('quality.corrective-actions.show', $action)->with('status', 'Corrective action created.');
    }

    public function show(CorrectiveAction $correctiveAction): View
    {
        $this->authorize('view', $correctiveAction);

        $correctiveAction->load([
            'qualityDefect.qualityInspection.productionRecord.productionOrder.product',
            'responsibleUser',
            'validatedBy',
        ]);

        return view('quality.corrective-actions.show', ['action' => $correctiveAction]);
    }

    public function update(UpdateCorrectiveActionRequest $request, CorrectiveAction $correctiveAction): RedirectResponse
    {
        $this->authorize('update', $correctiveAction);

        try {
            $this->qualityService->updateCorrectiveAction($correctiveAction, $request->validated(), $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('quality.corrective-actions.show', $correctiveAction)->with('status', 'Corrective action updated.');
    }

    public function validate(Request $request, CorrectiveAction $correctiveAction): RedirectResponse
    {
        $this->authorize('validate', $correctiveAction);

        try {
            $this->qualityService->validateCorrectiveAction($correctiveAction, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['validation' => $e->getMessage()]);
        }

        return redirect()->route('quality.corrective-actions.show', $correctiveAction)->with('status', 'Corrective action validated.');
    }
}
