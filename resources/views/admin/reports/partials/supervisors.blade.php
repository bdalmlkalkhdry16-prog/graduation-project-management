<style>
    body { font-family: DejaVu Sans, sans-serif; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
    th { background-color: #f2f2f2; }
</style>
<h3>{{ $data['title'] }}</h3>
<p>إجمالي المشرفين: <strong>{{ $data['total'] }}</strong></p>
<p>إجمالي المشاريع المشرف عليها: <strong>{{ $data['total_projects'] }}</strong></p>

<table>
    <thead>
        <tr>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الرقم الوظيفي</th>
            <th>التخصص</th>
            <th>عدد المشاريع</th>
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