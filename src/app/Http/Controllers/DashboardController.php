<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $user    = $request->user();
        $isStaff = $user->isAdmin() || $user->isAgent();

        return Inertia::render('Dashboard', [
            ...$this->dashboardService->getData($user, $isStaff),
        ]);
    }
}
