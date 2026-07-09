<style>
    body {
        font-family: 'DejaVu Sans', sans-serif;
        direction: rtl;
        text-align: right;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        direction: rtl;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: right;
    }
    th {
        background-color: #f2f2f2;
    }
</style><style>
    body { font-family: DejaVu Sans, sans-serif; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
    th { background-color: #f2f2f2; }
</style>
<h3>{{ $data['title'] }}</h3>
<p>إجمالي المشاريع: <strong>{{ $data['total'] }}</strong></p>
<p>متوسط نسبة النجاح: {{ number_format($data['avg_success_rate'], 2) }}%</p>

<table>
    <thead>
        <tr>
            <th>عنوان المشروع</th>
            <th>المشرف</th>
            <th>التخصص</th>
            <th>السنة الأكاديمية</th>
            <th>الحالة</th>
            <th>نسبة النجاح</th>
        </thead>
    <tbody>
        @foreach($data['projects'] as $project)
        <tr>
            <td>{{ $project->title_ar }}</td>
            <td>{{ $project->supervisor->name ?? '-' }}</td>
            <td>{{ $project->specialization->name_ar ?? '-' }}</td>
            <td>{{ $project->academic_year }}</td>
            <td>{{ $project->status_name }}</td>
            <td>{{ $project->success_percentage ?? '-' }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div>
    <strong>ملخص حسب الحالة:</strong><br>
    @foreach($data['by_status'] as $status => $count)
        <span>{{ $status }}: {{ $count }}</span><br>
    @endforeach
</div>