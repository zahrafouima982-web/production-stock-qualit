<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard)
    {
    }

    public function admin(): View
    {
        return view('dashboard.admin', $this->dashboard->adminStats());
    }

    public function production(): View
    {
        return view('dashboard.production', $this->dashboard->productionStats());
    }

    public function quality(): View
    {
        return view('dashboard.quality', $this->dashboard->qualityStats());
    }

    public function stock(): View
    {
        return view('dashboard.stock', $this->dashboard->stockStats());
    }
}
