<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: dejavusans, sans-serif; direction: rtl; text-align: right; font-size: 11pt; color: #1e293b; }
    h1 { text-align: center; font-size: 18pt; margin: 0; }
    h2 { text-align: center; font-size: 13pt; margin: 2pt 0 0; font-weight: normal; }
    .issued { text-align: center; font-size: 9pt; color: #64748b; margin-bottom: 8pt; border-bottom: 1px solid #94a3b8; padding-bottom: 8pt; }
    table { width: 100%; border-collapse: collapse; margin-top: 4pt; }
    th, td { border: 1px solid #94a3b8; padding: 4pt 5pt; font-size: 10pt; }
    th { background: #f1f5f9; }
    td.c, th.c { text-align: center; }
    .term { font-weight: bold; margin-top: 12pt; font-size: 12pt; }
    .info td { border: none; padding: 2pt 4pt; font-size: 10.5pt; }
    .note { margin-top: 16pt; font-size: 8.5pt; color: #64748b; }
</style>
</head>
<body>
    <h1>{{ $institution }}</h1>
    <h2>كشف الدرجات</h2>
    <div class="issued">تاريخ الإصدار: {{ $issued_at->format('Y-m-d') }}</div>

    <table class="info">
        <tr><td>الاسم: <strong>{{ $student->user->name }}</strong></td><td>الرقم الأكاديمي: <strong>{{ $student->number_student }}</strong></td></tr>
        <tr><td>التخصص: {{ $student->program->specialization->name_ar ?? '—' }}</td><td>المستوى: {{ $student->currentLevel->name ?? '—' }}</td></tr>
    </table>

    @forelse ($terms as $term)
        <div class="term">{{ $term['label'] }}</div>
        <table>
            <thead><tr><th>رمز المقرر</th><th>اسم المقرر</th><th class="c">الساعات</th><th class="c">المجموع</th><th class="c">النسبة</th><th class="c">التقدير</th><th class="c">الحالة</th></tr></thead>
            <tbody>
            @foreach ($term['courses'] as $c)
                <tr>
                    <td>{{ $c['course']->code }}</td>
                    <td>{{ $c['course']->name_ar }}</td>
                    <td class="c">{{ $c['credit_hours'] ?? '—' }}</td>
                    <td class="c">{{ $c['complete'] ? $c['total'] + 0 : '—' }}</td>
                    <td class="c">{{ $c['percentage'] !== null ? ($c['percentage'] + 0).'%' : '—' }}</td>
                    <td class="c">{{ $c['letter'] ?? '—' }}</td>
                    <td class="c">{{ $c['status_label'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($term['gpa'] !== null)<p>معدل الفصل: <strong>{{ $term['gpa'] }}</strong></p>@endif
    @empty
        <p>لا توجد مقررات.</p>
    @endforelse

    @if ($cumulative_gpa !== null)<p style="margin-top:10pt">المعدل التراكمي: <strong>{{ $cumulative_gpa }}</strong></p>@endif
    <p class="note">يتضمن هذا الكشف النتائج المنشورة والمعتمدة فقط.</p>
</body>
</html>
