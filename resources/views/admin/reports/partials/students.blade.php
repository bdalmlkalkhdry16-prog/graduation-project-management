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
<p>إجمالي الطلاب: <strong>{{ $data['total'] }}</strong></p>
<p>عدد الطلاب المشاركين في مشاريع: {{ $data['with_projects'] }} | غير المشاركين: {{ $data['without_projects'] }}</p>

<table>
    <thead>
        <tr>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الرقم الجامعي</th>
            <th>التخصص</th>
            <th>عدد المشاريع</th>
        </thead>
    <tbody>
        @foreach($data['students'] as $student)
        <tr>
            <td>{{ $student->name }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->student_id ?? '-' }}</td>
            <td>{{ $student->specialization->name_ar ?? '-' }}</td>
            <td>{{ $student->projects->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>