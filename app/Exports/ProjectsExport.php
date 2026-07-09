<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    public function collection()
    {
        return $this->projects;
    }

    public function headings(): array
    {
        return [
            'ID', 'عنوان المشروع', 'المشرف', 'التخصص', 'السنة الأكاديمية', 'الحالة', 'نسبة النجاح', 'تاريخ التسليم', 'تاريخ القبول'
        ];
    }

    public function map($project): array
    {
        return [
            $project->id,
            $project->title_ar,
            $project->supervisor->name ?? '-',
            $project->specialization->name_ar ?? '-',
            $project->academic_year,
            $project->status_name,
            $project->success_percentage ?? '-',
            $project->submission_date ?? '-',
            $project->approval_date ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                // جعل الورقة RTL
                $sheet->setRightToLeft(true);
                // محاذاة النص لليمين
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}