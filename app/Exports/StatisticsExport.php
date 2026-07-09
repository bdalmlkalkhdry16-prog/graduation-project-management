<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StatisticsExport implements FromArray, WithHeadings
{
    protected $stats;

    public function __construct($stats)
    {
        $this->stats = $stats;
    }

    public function array(): array
    {
        $data = [];
        $data[] = ['إجمالي الطلاب', $this->stats['total_students']];
        $data[] = ['إجمالي المشرفين', $this->stats['total_supervisors']];
        $data[] = ['إجمالي المشاريع', $this->stats['total_projects']];
        $data[] = ['إجمالي الكليات', $this->stats['total_colleges']];
        $data[] = ['إجمالي الأقسام', $this->stats['total_departments']];
        $data[] = ['إجمالي التخصصات', $this->stats['total_specializations']];
        $data[] = [];
        $data[] = ['المشاريع حسب الحالة'];
        foreach ($this->stats['projects_by_status'] as $status => $count) {
            $data[] = [Project::getStatusName($status), $count];
        }
        $data[] = [];
        $data[] = ['المشاريع حسب السنة'];
        foreach ($this->stats['projects_by_year'] as $year => $count) {
            $data[] = [$year, $count];
        }
        return $data;
    }

    public function headings(): array
    {
        return ['البيان', 'القيمة'];
    }
}