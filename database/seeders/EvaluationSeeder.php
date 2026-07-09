<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\Project;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // الحصول على المشاريع المكتملة فقط
        $completedProjects = Project::where('status', Project::STATUS_COMPLETED)->get();

        $evaluations = [];
        $count = 0;

        foreach ($completedProjects as $project) {
            // تقييم لكل مشروع مكتمل
            $creativity = rand(75, 95);
            $implementation = rand(70, 90);
            $documentation = rand(80, 95);
            $presentation = rand(70, 90);

            $totalPercentage = ($creativity * 0.40) + ($implementation * 0.30) + ($documentation * 0.20) + ($presentation * 0.10);

            $evaluation = Evaluation::create([
                'project_id' => $project->id,
                'supervisor_id' => $project->supervisor_id,
                'creativity_score' => $creativity,
                'implementation_score' => $implementation,
                'documentation_score' => $documentation,
                'presentation_score' => $presentation,
                'total_percentage' => round($totalPercentage, 2),
                'strengths' => 'تميز المشروع في الجانب التقني والإبداعي، وتم تنفيذه بدقة عالية',
                'weaknesses' => 'يمكن تحسين التوثيق وتقديم عرض أكثر احترافية',
                'recommendations' => 'يوصى بنشر البحث في مجلة علمية وتطوير المشروع ليشمل ميزات إضافية',
                'status' => 'finalized',
                'evaluated_at' => $project->defense_date ?? now(),
            ]);

            $count++;
        }

        $this->command->info('✅ تم إضافة ' . $count . ' تقييم للمشاريع المكتملة');
    }
}
