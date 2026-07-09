<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] ?? 'تقرير الطلاب' }}</title>
    <style>
        /* تعريف الخط مع المسار الديناميكي */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/DejaVuSans-Bold.ttf') }}") format('truetype');
        }

        /* إعادة تعيين الاتجاه لجميع العناصر */
        * {
            direction: rtl !important;
            text-align: right !important;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>{{ $data['title'] ?? 'تقرير الطلاب' }}</h3>
        <p>تاريخ التقرير: {{ now()->format('Y-m-d') }}</p>
    </div>

    <p>إجمالي الطلاب: <strong>{{ $data['total'] ?? 0 }}</strong></p>
    <p>عدد الطلاب المشاركين في مشاريع: {{ $data['with_projects'] ?? 0 }} | غير المشاركين: {{ $data['without_projects'] ?? 0 }}</p>

    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
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

    <div class="footer">
        تم إنشاء هذا التقرير بواسطة نظام إدارة مشاريع التخرج
    </div>
</body>
</html>