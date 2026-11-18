<dl class="grid gap-2 text-sm sm:grid-cols-2">
    <div><dt class="inline text-slate-500">الاسم: </dt><dd class="inline font-semibold">{{ $record['student']->user->name }}</dd></div>
    <div><dt class="inline text-slate-500">الرقم الأكاديمي: </dt><dd class="inline font-semibold">{{ $record['student']->number_student }}</dd></div>
    <div><dt class="inline text-slate-500">التخصص: </dt><dd class="inline">{{ $record['student']->program->specialization->name_ar ?? '—' }}</dd></div>
    <div><dt class="inline text-slate-500">المستوى: </dt><dd class="inline">{{ $record['student']->currentLevel->name ?? '—' }}</dd></div>
</dl>
