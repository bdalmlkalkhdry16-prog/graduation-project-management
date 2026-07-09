<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Alignment;

trait WithRtlDirection
{
    public function setRtlDirection($sheet)
    {
        $sheet->setRightToLeft(true); // يجعل الورقة RTL
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}