<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreQualityDefectRequest;
use App\Models\QualityDefect;
use App\Models\QualityInspection;
use App\Services\QualityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class QualityDefectController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private QualityService $qualityService)
    {
    }

    public function create(QualityInspection $inspection): View
    {
        $this->authorize('create', QualityDefect::class);

        return view('quality.defects.create', compact('inspection'));
    }

    public function store(StoreQualityDefectRequest $request, QualityInspection $inspection): RedirectResponse
    {
        $this->authorize('create', QualityDefect::class);

        try {
            $defect = $this->qualityService->createDefect($request->validated(), $inspection, $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['inspection' => $e->getMessage()]);
        }

        return redirect()->route('quality.defects.show', $defect)->with('status', 'Defect recorded.');
    }

    public function show(QualityDefect $defect): View
    {
        $this->authorize('view', $defect);

        $defect->load(['qualityInspection.productionRecord.productionOrder.product', 'detectedBy', 'correctiveActions.responsibleUser', 'correctiveActions.validatedBy']);

        return view('quality.defects.show', compact('defect'));
    }
}
