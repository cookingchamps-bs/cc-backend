<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $dashboard = $dashboardService->getDashboardData();

        return view('admin.dashboard', compact('dashboard'));
    }
}
