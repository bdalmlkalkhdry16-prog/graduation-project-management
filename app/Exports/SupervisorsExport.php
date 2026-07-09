<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupervisorsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $supervisors;

    public function __construct($supervisors)
    {
        $this->supervisors = $supervisors;
    }

    public function collection()
    {
        return $this->supervisors;
    }

    public function headings(): array
    {
        return [
            'ID', 'الاسم', 'البريد الإلكتروني', 'الرقم الوظيفي', 'التخصص', 'عدد المشاريع المشرف عليها'
        ];
    }

    public function map($supervisor): array
    {
        return [
            $supervisor->id,
            $supervisor->name,
            $supervisor->email,
            $supervisor->employee_id ?? '-',
            $supervisor->specialization->name_ar ?? '-',
            $supervisor->supervisedProjects->count(),
        ];
    }
}