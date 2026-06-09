<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->company;
        $query = $company->payments()->with(['invoices.client', 'invoices.project']);

        if ($search = $request->search) {
            $query->whereHas('invoices', function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->orderByDesc('payment_date')->paginate(20)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $company  = Auth::user()->company;
        $invoices = $company->invoices()
            ->whereIn('status', ['envoye', 'partiellement_payee'])
            ->orderByDesc('invoice_date')->get();

        $selectedInvoice = $request->invoice_id
            ? $company->invoices()->find($request->invoice_id)
            : null;

        return view('payments.form', compact('invoices', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'   => 'required|exists:invoices,id',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method'       => 'required|string|max:100',
            'reference'    => 'nullable|string|max:255',
        ]);

        $company = Auth::user()->company;
        $invoice = $company->invoices()->findOrFail($request->invoice_id);

        // Create the Payment record with all required fields
        $payment = $company->payments()->create([
            'project_id'   => $invoice->project_id,
            'client_id'    => $invoice->client_id,
            'created_by'   => Auth::id(),
            'amount'       => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_mode' => $request->method,
            'reference'    => $request->reference,
            'notes'        => $request->notes,
        ]);

        // Attach to invoice via pivot with allocated amount
        $payment->invoices()->attach($invoice->id, ['amount' => $request->amount]);

        // Recalculate invoice payment status
        $invoice->updatePaymentStatus();

        // Notify company admins
        try {
            $admins = User::role('admin')
                ->where('company_id', $company->id)
                ->where('id', '!=', Auth::id())
                ->get();
            
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new PaymentReceived($payment, $invoice));
            }
        } catch (\Throwable $e) {
            // Notification failure is non-blocking
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Paiement enregistré.');
    }

    public function destroy(Payment $payment)
    {
        abort_if($payment->company_id !== Auth::user()->company_id, 403);
        $invoice = $payment->invoices()->first();
        $payment->delete();

        if ($invoice) {
            $invoice->updatePaymentStatus();
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Paiement supprimé.');
    }
}
