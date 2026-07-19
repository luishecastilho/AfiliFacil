<?php

namespace App\Http\Controllers;

use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ImportRowController extends Controller
{
    public function index(Request $request, Import $import): Response
    {
        $this->authorize('view', $import);

        $search = $request->string('search');
        $isMysql = Schema::getConnection()->getDriverName() === 'mysql';

        $rows = $import->importRows()
            ->when($request->string('status')->isNotEmpty(), fn ($query) => $query->where('status', $request->string('status')))
            ->when($search->isNotEmpty(), function ($query) use ($search, $isMysql) {
                $query->where(function ($q) use ($search, $isMysql) {
                    $isMysql
                        ? $q->whereFullText('seller_name', $search)
                        : $q->where('seller_name', 'like', "%{$search}%");

                    $q->orWhere('seller_document', 'like', "%{$search}%");
                });
            })
            ->paginate(50);

        return Inertia::render('Imports/Show', [
            'import' => $import,
            'rows' => $rows,
        ]);
    }
}
