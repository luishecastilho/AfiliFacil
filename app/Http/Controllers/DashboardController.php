<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard/Index', [
            'summary' => [
                'total_imports' => $request->user()->imports()->count(),
                'total_invoices' => Invoice::whereHas(
                    'import',
                    fn ($query) => $query->where('user_id', $request->user()->id)
                )->count(),
            ],
        ]);
    }
}
