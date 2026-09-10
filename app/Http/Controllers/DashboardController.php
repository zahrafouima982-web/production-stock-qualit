<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Placeholder dashboards only — full KPI dashboards are a later phase
 * (see architecture doc, section 15 "Dashboards").
 */
class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin');
    }

    public function production(): View
    {
        return view('dashboard.production');
    }

    public function quality(): View
    {
        return view('dashboard.quality');
    }

    public function stock(): View
    {
        return view('dashboard.stock');
    }
}
