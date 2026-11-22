<div class="row g-2 small">
    <div class="col-sm-6"><span class="text-muted">الاسم: </span><strong>{{ $record['student']->user->name }}</strong></div>
    <div class="col-sm-6"><span class="text-muted">الرقم الأكاديمي: </span><strong>{{ $record['student']->number_student }}</strong></div>
    <div class="col-sm-6"><span class="text-muted">التخصص: </span>{{ $record['student']->program->specialization->name_ar ?? '—' }}</div>
    <div class="col-sm-6"><span class="text-muted">المستوى: </span>{{ $record['student']->currentLevel->name ?? '—' }}</div>
</div>
