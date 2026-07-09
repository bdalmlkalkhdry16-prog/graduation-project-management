<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    /* إعادة تعيين الاتجاه */
    * {
        direction: rtl !important;
        text-align: right !important;
    }
</style>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; direction: rtl; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>{{ $data['title'] }}</h3>
        <p>تاريخ التقرير: {{ now()->format('Y-m-d') }}</p>
    </div>
    <h4>إحصائيات عامة</h4>
    <table>
        <tr><th>إجمالي الطلاب</th><td>{{ $data['stats']['total_students'] }}</td></tr>
        <tr><th>إجمالي المشرفين</th><td>{{ $data['stats']['total_supervisors'] }}</td></tr>
        <tr><th>إجمالي المشاريع</th><td>{{ $data['stats']['total_projects'] }}</td></tr>
        <tr><th>إجمالي الكليات</th><td>{{ $data['stats']['total_colleges'] }}</td></tr>
        <tr><th>إجمالي الأقسام</th><td>{{ $data['stats']['total_departments'] }}</td></tr>
        <tr><th>إجمالي التخصصات</th><td>{{ $data['stats']['total_specializations'] }}</td></tr>
    </table>
    <h4>المشاريع حسب الحالة</h4>
    <tr>
        @foreach($data['stats']['projects_by_status'] as $status => $count)
        <tr><th>{{ \App\Models\Project::getStatusName($status) }}</th><td>{{ $count }}</td></tr>
        @endforeach
    </table>
    <h4>المشاريع حسب السنة</h4>
    <table>
        @foreach($data['stats']['projects_by_year'] as $year => $count)
        <tr><th>{{ $year }}</th><td>{{ $count }}</td></tr>
        @endforeach
    </table>
</body>
</html>