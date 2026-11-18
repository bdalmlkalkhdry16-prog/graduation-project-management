<?php

namespace App\Services;

use Mpdf\Mpdf;

/**
 * توليد كشف الدرجات PDF بتنسيق عربي RTL عبر mPDF (يدعم وصل الحروف العربية).
 */
class TranscriptPdf
{
    public function render(array $record): string
    {
        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle('كشف الدرجات');
        $mpdf->WriteHTML(view('student.records.transcript-pdf', $record + ['institution' => config('academic.institution_name')])->render());

        return $mpdf->Output('', 'S');
    }
}
