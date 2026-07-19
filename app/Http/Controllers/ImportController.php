<?php

namespace App\Http\Controllers;

use App\Actions\Import\CreateImportAction;
use App\Exceptions\DuplicateImportException;
use App\Http\Requests\Import\StoreImportRequest;
use App\Models\Import;
use App\Models\Marketplace;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function index(): Response
    {
        $imports = Import::with('marketplace')
            ->latest()
            ->paginate(20);

        return Inertia::render('Imports/Index', [
            'imports' => $imports,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Imports/Create', [
            'marketplaces' => Marketplace::where('active', true)->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(StoreImportRequest $request, CreateImportAction $createImportAction): RedirectResponse
    {
        try {
            $import = $createImportAction->handle(
                $request->user()->id,
                Marketplace::findOrFail($request->validated('marketplace_id')),
                $request->file('file'),
            );
        } catch (DuplicateImportException $exception) {
            return back()->withErrors(['file' => 'This file was already imported.'])
                ->with('duplicate_import_id', $exception->existingImport->id);
        }

        return redirect()->route('imports.show', $import);
    }

    public function show(Import $import): Response
    {
        $this->authorize('view', $import);

        return Inertia::render('Imports/Show', [
            'import' => $import->load('marketplace'),
        ]);
    }

    public function destroy(Import $import): RedirectResponse
    {
        $this->authorize('delete', $import);

        $import->delete();

        return redirect()->route('imports.index');
    }
}
