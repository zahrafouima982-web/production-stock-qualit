<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreQualityInspectionRequest;
use App\Models\ProductionRecord;
use App\Models\QualityInspection;
use App\Services\QualityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QualityInspectionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private QualityService $qualityService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', QualityInspection::class);

        $inspections = QualityInspection::with(['productionRecord.productionOrder.product', 'inspector'])
            ->latest('inspected_at')
            ->paginate(15);

        return view('quality.inspections.index', compact('inspections'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', QualityInspection::class);

        $preselectedRecordId = $request->query('production_record_id');

        $productionRecords = ProductionRecord::with('productionOrder.product')
            ->latest('recorded_at')
            ->limit(50)
            ->get();

        return view('quality.inspections.create', compact('productionRecords', 'preselectedRecordId'));
    }

    public function store(StoreQualityInspectionRequest $request): RedirectResponse
    {
        $this->authorize('create', QualityInspection::class);

        $inspection = $this->qualityService->createInspection($request->validated(), $request->user());

        return redirect()->route('quality.inspections.show', $inspection)
            ->with('status', "Inspection recorded: {$inspection->result}.");
    }

    public function show(QualityInspection $inspection): View
    {
        $this->authorize('view', $inspection);

        $inspection->load([
            'productionRecord.productionOrder.product',
            'inspector',
            'inspectionItems',
            'qualityDefects.correctiveActions',
        ]);

        return view('quality.inspections.show', compact('inspection'));
    }

    /**
     * Convenience redirect into the same create form, pre-selecting the
     * production record so the Quality Controller doesn't have to look it
     * up again. The actual new row is created by store() above — this
     * action never touches the original (FAILED) inspection.
     */
    public function reinspect(QualityInspection $inspection): RedirectResponse
    {
        $this->authorize('reinspect', $inspection);

        return redirect()->route('quality.inspections.create', [
            'production_record_id' => $inspection->production_record_id,
        ]);
    }
}
