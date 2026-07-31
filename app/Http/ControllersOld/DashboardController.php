<?php

namespace App\Http\Controllers;

use App\Models\CashbookEntry;
use App\Models\SaleInvoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $stats = $this->computeStats($from, $to);

        if ($request->wantsJson()) {
            return response()->json($stats + ['from' => $from, 'to' => $to]);
        }

        return view('admin.dashboard', [
            'stats' => $stats,
            'from'  => $from,
            'to'    => $to,
        ]);
    }

    private function computeStats(string $from, string $to): array
    {
        $totalPayments = (float) CashbookEntry::where('type', 'in')
            ->whereBetween('entry_date', [$from, $to])
            ->sum('amount');

        $totalLoading = (float) SaleInvoice::whereBetween('bill_date', [$from, $to])
            ->sum('loading');

        $totalLabour = (float) SaleInvoice::whereBetween('bill_date', [$from, $to])
            ->sum('labour_cost');

        $totalSale = (float) SaleInvoice::whereBetween('bill_date', [$from, $to])
            ->sum('total');

        return [
            'total_payments_received' => round($totalPayments, 2),
            'total_loading'           => round($totalLoading, 2),
            'total_labour_cost'       => round($totalLabour, 2),
            'total_sale'              => round($totalSale, 2),
        ];
    }
}
