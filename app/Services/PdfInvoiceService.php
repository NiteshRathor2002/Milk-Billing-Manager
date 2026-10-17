<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfInvoiceService
{
    public function generatePdf(Bill $bill)
    {
        $customer = $bill->customer;
        $settings = AppSetting::getSettings();
        $snapshot = $bill->itemized_snapshot;

        $pdf = Pdf::loadView('billing.pdf_invoice', [
            'bill' => $bill,
            'customer' => $customer,
            'settings' => $settings,
            'snapshot' => $snapshot,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function downloadPdf(Bill $bill): Response
    {
        $pdf = $this->generatePdf($bill);
        $fileName = 'Bill_' . $bill->bill_number . '_' . $bill->billing_month . '.pdf';
        return $pdf->download($fileName);
    }

    public function streamPdf(Bill $bill): Response
    {
        $pdf = $this->generatePdf($bill);
        $fileName = 'Bill_' . $bill->bill_number . '_' . $bill->billing_month . '.pdf';
        return $pdf->stream($fileName);
    }
}
