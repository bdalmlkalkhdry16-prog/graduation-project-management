<div class="row g-4 mb-4" id="admin-charts">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie text-primary me-2"></i>توزيع الطلاب حسب التخصص</div>
            <div class="card-body">
                @if (array_sum($charts['donut']['values']) > 0)
                    <canvas id="chartSpecs" height="220"></canvas>
                @else
                    <p class="text-muted text-center my-5">لا توجد ملفات طلاب بعد.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-line text-primary me-2"></i>طلبات الخدمات الإلكترونية (آخر 6 أشهر)</div>
            <div class="card-body"><canvas id="chartRequests" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <span><i class="fas fa-concierge-bell text-primary me-2"></i>أحدث الطلبات</span>
                <span class="badge bg-primary">{{ $charts['open_requests'] }} مفتوح</span>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($charts['latest_requests'] as $r)
                    <li class="list-group-item d-flex justify-content-between small">
                        <span>{{ \App\Models\StudentServiceRequest::TYPES[$r->type] ?? $r->type }} — {{ $r->studentProfile?->user?->name ?? '—' }}</span>
                        <span class="text-muted">{{ $r->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center py-4">لا توجد طلبات.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;
    Chart.defaults.font.family = 'Tajawal, sans-serif';
    const palette = ['#1f6b36', '#b8962e', '#2f7f86', '#6bbd3a', '#14492a', '#9a7b1c', '#7a8f80'];
    const d = @json($charts['donut']);
    const l = @json($charts['line']);
    const a = document.getElementById('chartSpecs');
    if (a) new Chart(a, {type: 'doughnut', data: {labels: d.labels, datasets: [{data: d.values, backgroundColor: palette}]}, options: {plugins: {legend: {position: 'bottom'}}}});
    new Chart(document.getElementById('chartRequests'), {type: 'line',
        data: {labels: l.labels, datasets: [{label: 'الطلبات', data: l.values, borderColor: '#1f6b36', backgroundColor: 'rgba(31,107,54,.12)', fill: true, tension: .3}]},
        options: {plugins: {legend: {display: false}}, scales: {y: {beginAtZero: true, ticks: {precision: 0}}}}});
})();
</script>
@endpush
