<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function index(Request $request): Response
    {
        $sellers = $request->user()->sellers()->paginate(20);

        return Inertia::render('Sellers/Index', [
            'sellers' => $sellers,
        ]);
    }

    public function edit(Seller $seller): Response
    {
        $this->authorize('update', $seller);

        return Inertia::render('Sellers/Edit', [
            'seller' => $seller,
        ]);
    }

    public function update(Request $request, Seller $seller): RedirectResponse
    {
        $this->authorize('update', $seller);

        $seller->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:100'],
            'address_district' => ['nullable', 'string', 'max:100'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_state' => ['nullable', 'string', 'size:2'],
            'address_zip' => ['nullable', 'string', 'max:10'],
        ]));

        return redirect()->route('sellers.index');
    }
}
