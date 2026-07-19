<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->import->user_id;
    }

    public function retry(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->import->user_id;
    }

    public function download(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->import->user_id;
    }
}
