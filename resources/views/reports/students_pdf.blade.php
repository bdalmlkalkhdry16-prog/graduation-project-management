<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] ?? 'تقرير الطلاب' }}</title>
    <style>
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/Amiri-Regular.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/Amiri-Regular.ttf') }}") format('truetype');
        }
        * {
            direction: rtl !important;
            text-align: right !important;
        }
        body {
            font-family: 'Amiri', 'DejaVu Sans', sans-serif;
            font-size: 12pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .header, .footer {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>{{ $data['title'] ?? 'تقرير الطلاب' }}</h3>
        <p>التاريخ: {{ now()->format('Y-m-d') }}</p>
    </div>

    <p><strong>إجمالي الطلاب:</strong> {{ $data['total'] ?? 0 }}</p>
    <p><strong>المشاركون في مشاريع:</strong> {{ $data['with_projects'] ?? 0 }} | <strong>غير المشاركين:</strong> {{ $data['without_projects'] ?? 0 }}</p>

    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد</th>
                <th>الرقم الجامعي</th>
                <th>التخصص</th>
                <th>عدد المشاريع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['students'] ?? [] as $student)
            <tr>
                <td>{{ $student->name ?? '-' }}</td>
                <td>{{ $student->email ?? '-' }}</td>
                <td>{{ $student->student_id ?? '-' }}</td>
                <td>{{ $student->specialization->name_ar ?? '-' }}</td>
                <td>{{ $student->projects->count() ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">نظام إدارة مشاريع التخرج</div>
</body>
</html>