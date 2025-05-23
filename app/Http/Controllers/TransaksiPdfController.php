<?php
// app/Http/Controllers/TransaksiPdfController.php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;

class TransaksiPdfController extends Controller
{
    public function generate()
    {
        $transaksis = Transaksi::with('penyewaan')->get();
        $pdf = Pdf::loadView('pdf.transaksi', compact('transaksis'));
        return $pdf->stream('transaksi.pdf');
    }
}
