<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\Specialization;
use App\Models\ProjectMember;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $supervisors = User::where('role', 'supervisor')->get();
        $specializations = Specialization::all();
        $students = User::where('role', 'student')->take(12)->get();

        $projects = [
            [
                'title_ar' => 'نظام إدارة المستشفيات الذكي',
                'title_en' => 'Smart Hospital Management System',
                'abstract_ar' => 'نظام متكامل لإدارة المستشفيات باستخدام تقنيات الذكاء الاصطناعي وإنترنت الأشياء',
                'keywords' => 'ذكاء اصطناعي, إنترنت الأشياء, إدارة المستشفيات',
                'status' => Project::STATUS_COMPLETED,
                'academic_year' => 2024,
                'semester' => 'first',
                'success_percentage' => 92,
                'submission_date' => '2024-02-15',
                'approval_date' => '2024-03-01',
                'defense_date' => '2024-05-20',
            ],
            [
                'title_ar' => 'منصة تعليمية تفاعلية باستخدام الواقع المعزز',
                'title_en' => 'Interactive Educational Platform using Augmented Reality',
                'abstract_ar' => 'منصة تعليمية مبتكرة تستخدم تقنيات الواقع المعزز لتسهيل عملية التعلم',
                'keywords' => 'واقع معزز, تعليم تفاعلي, تطبيقات موبايل',
                'status' => Project::STATUS_COMPLETED,
                'academic_year' => 2024,
                'semester' => 'first',
                'success_percentage' => 88,
                'submission_date' => '2024-02-20',
                'approval_date' => '2024-03-05',
                'defense_date' => '2024-05-22',
            ],
            [
                'title_ar' => 'تطبيق للتجارة الإلكترونية باستخدام تقنيات الذكاء الاصطناعي',
                'title_en' => 'E-commerce Application using AI Technologies',
                'abstract_ar' => 'تطبيق للتجارة الإلكترونية يدعم التوصيات الذكية وتحليل سلوك المستخدم',
                'keywords' => 'تجارة إلكترونية, ذكاء اصطناعي, توصيات ذكية',
                'status' => Project::STATUS_APPROVED,
                'academic_year' => 2024,
                'semester' => 'second',
                'success_percentage' => null,
                'submission_date' => '2024-09-10',
                'approval_date' => '2024-09-25',
                'defense_date' => null,
            ],
            [
                'title_ar' => 'نظام إدارة المشاريع باستخدام تقنيات Blockchain',
                'title_en' => 'Project Management System using Blockchain',
                'abstract_ar' => 'نظام لإدارة المشاريع يعتمد على تقنيات البلوكشين لضمان الشفافية والأمان',
                'keywords' => 'بلوكشين, إدارة مشاريع, أمان',
                'status' => Project::STATUS_UNDER_REVIEW,
                'academic_year' => 2024,
                'semester' => 'second',
                'success_percentage' => null,
                'submission_date' => '2024-10-01',
                'approval_date' => null,
                'defense_date' => null,
            ],
            [
                'title_ar' => 'تطبيق للكشف عن الأمراض باستخدام التعلم العميق',
                'title_en' => 'Disease Detection Application using Deep Learning',
                'abstract_ar' => 'تطبيق يستخدم تقنيات التعلم العميق للكشف المبكر عن الأمراض من خلال الصور الطبية',
                'keywords' => 'تعلم عميق, تشخيص طبي, صور طبية',
                'status' => Project::STATUS_SUBMITTED,
                'academic_year' => 2024,
                'semester' => 'second',
                'success_percentage' => null,
                'submission_date' => '2024-10-15',
                'approval_date' => null,
                'defense_date' => null,
            ],
            [
                'title_ar' => 'منصة للتواصل بين الطلاب والمشرفين',
                'title_en' => 'Communication Platform for Students and Supervisors',
                'abstract_ar' => 'منصة متكاملة لتسهيل التواصل بين الطلاب والمشرفين في المشاريع',
                'keywords' => 'تواصل, مشاريع تخرج, منصة تعليمية',
                'status' => Project::STATUS_DRAFT,
                'academic_year' => 2025,
                'semester' => 'first',
                'success_percentage' => null,
                'submission_date' => null,
                'approval_date' => null,
                'defense_date' => null,
            ],
            [
                'title_ar' => 'نظام للكشف عن الاحتيال في المعاملات المالية',
                'title_en' => 'Fraud Detection System in Financial Transactions',
                'abstract_ar' => 'نظام ذكي للكشف عن عمليات الاحتيال في المعاملات المالية باستخدام التعلم الآلي',
                'keywords' => 'كشف احتيال, تعلم آلي, معاملات مالية',
                'status' => Project::STATUS_APPROVED,
                'academic_year' => 2024,
                'semester' => 'second',
                'success_percentage' => null,
                'submission_date' => '2024-09-05',
                'approval_date' => '2024-09-20',
                'defense_date' => null,
            ],
            [
                'title_ar' => 'تطبيق لتحسين كفاءة استهلاك الطاقة في المباني',
                'title_en' => 'Application for Improving Energy Efficiency in Buildings',
                'abstract_ar' => 'تطبيق يستخدم إنترنت الأشياء لتحسين كفاءة استهلاك الطاقة في المباني الذكية',
                'keywords' => 'إنترنت الأشياء, كفاءة طاقة, مباني ذكية',
                'status' => Project::STATUS_COMPLETED,
                'academic_year' => 2023,
                'semester' => 'second',
                'success_percentage' => 85,
                'submission_date' => '2023-03-10',
                'approval_date' => '2023-03-25',
                'defense_date' => '2023-06-15',
            ],
        ];

        $projectCount = 0;
        $memberCount = 0;

        foreach ($projects as $index => $projectData) {
            $project = Project::create([
                'title_ar' => $projectData['title_ar'],
                'title_en' => $projectData['title_en'],
                'abstract_ar' => $projectData['abstract_ar'],
                'abstract_en' => $projectData['abstract_ar'],
                'keywords' => $projectData['keywords'],
                'supervisor_id' => $supervisors->get($index % $supervisors->count())->id,
                'specialization_id' => $specializations->get($index % $specializations->count())->id,
                'status' => $projectData['status'],
                'academic_year' => $projectData['academic_year'],
                'semester' => $projectData['semester'],
                'success_percentage' => $projectData['success_percentage'],
                'feedback' => $projectData['status'] === Project::STATUS_REJECTED ? 'يحتاج المشروع إلى تحسينات' : null,
                'submission_date' => $projectData['submission_date'],
                'approval_date' => $projectData['approval_date'],
                'defense_date' => $projectData['defense_date'],
            ]);

            $projectCount++;

            // إضافة أعضاء للمشروع
            $membersCount = rand(2, 4);
            $assignedStudents = $students->slice(($index * $membersCount) % $students->count(), $membersCount);

            foreach ($assignedStudents as $studentIndex => $student) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'student_id' => $student->id,
                    'role' => $studentIndex === 0 ? 'leader' : 'member',
                    'joined_at' => now(),
                ]);
                $memberCount++;
            }
        }

        $this->command->info('✅ تم إضافة ' . $projectCount . ' مشروع');
        $this->command->info('✅ تم إضافة ' . $memberCount . ' عضو في المشاريع');
    }
}
