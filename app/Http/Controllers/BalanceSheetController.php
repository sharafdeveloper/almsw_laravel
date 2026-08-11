<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BalanceSheetController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        [$all, $totalDebit, $totalCredit] = $this->buildRows($q);

        $page = max(1, (int) $request->input('page', 1));
        $paged = new LengthAwarePaginator(
            $all->forPage($page, self::PER_PAGE)->values(),
            $all->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.balance-sheet', [
            'rows'        => $paged,
            'totalDebit'  => $totalDebit,
            'totalCredit' => $totalCredit,
            'q'           => $q,
            'startIndex'  => ($page - 1) * self::PER_PAGE,
        ]);
    }

    public function print(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        [$all, $totalDebit, $totalCredit] = $this->buildRows($q);

        $data = [
            'rows'        => $all,
            'totalDebit'  => $totalDebit,
            'totalCredit' => $totalCredit,
        ];

        // If DomPDF is installed, stream a real PDF download (no page view).
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $filename = 'balance-sheet-' . now()->format('Y-m-d_His') . '.pdf';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.balance-sheet-print', $data)
                ->setPaper('a4', 'portrait');
            return $pdf->stream($filename);
        }

        // Fallback: HTML view that auto-opens the browser print dialog.
        return view('admin.balance-sheet-print', $data + ['browserPrint' => true]);
    }
    public function printLocal(Request $request)
{
    $q = trim((string) $request->input('q', ''));

    [$all, $totalDebit, $totalCredit] = $this->buildRows($q);

    $data = [
        'rows'        => $all,
        'totalDebit'  => $totalDebit,
        'totalCredit' => $totalCredit,
    ];

    /*
     * Generate the same Balance Sheet PDF
     * as the normal Print button.
     */
    if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {

        $filename =
            'balance-sheet-' .
            now()->format('Y-m-d_His') .
            '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.balance-sheet-print',
            $data
        )->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }

    /*
     * Fallback if DomPDF is not available.
     */
    return view(
        'admin.balance-sheet-print',
        $data + ['browserPrint' => true]
    );
}

    /**
     * Build the closing balance per customer.
     * balance > 0  => Debit  (customer owes us)
     * balance < 0  => Credit (we owe customer)
     *
     * @return array{0: Collection, 1: float, 2: float}
     */
    private function buildRows(string $q = ''): array
    {
        $query = Customer::active()->orderBy('name');
        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        $rows = $query->get()->map(function (Customer $c) {
            $ledger  = $c->buildLedger();
            $balance = empty($ledger) ? (float) $c->opening_balance : (float) end($ledger)['balance'];

            return [
                'id'     => $c->id,
                'name'   => $c->name,
                'debit'  => $balance > 0 ? round($balance, 2) : 0.0,
                'credit' => $balance < 0 ? round(abs($balance), 2) : 0.0,
            ];
        });

        $totalDebit  = round((float) $rows->sum('debit'), 2);
        $totalCredit = round((float) $rows->sum('credit'), 2);

        return [$rows, $totalDebit, $totalCredit];
    }
}
