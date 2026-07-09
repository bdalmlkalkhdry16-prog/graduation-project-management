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
        table { width: 100%; border-collapse: collapse; }
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
   
    <p>إجمالي المشاريع المشرف عليها: <strong>{{ $data['total_projects'] }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الرقم الوظيفي</th>
                <th>التخصص</th>
                <th>عدد المشاريع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['supervisors'] as $supervisor)
            <tr>
                <td>{{ $supervisor->name }}</td>
                <td>{{ $supervisor->email }}</td>
                <td>{{ $supervisor->employee_id ?? '-' }}</td>
                <td>{{ $supervisor->specialization->name_ar ?? '-' }}</td>
                <td>{{ $supervisor->supervisedProjects->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>