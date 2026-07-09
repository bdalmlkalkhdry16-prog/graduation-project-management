@if(isset($project))

@php
    $user = auth()->user();
    $isLeader = optional($project->members()->where('student_id', $user->id)->first())->role === 'leader';
    $canManage = (
        (in_array($project->status, ['draft', 'submitted']) && $user->isStudent() && $project->students->contains($user->id) && $isLeader)
        || $user->isSupervisor()
        || $user->isAdmin()
    );
@endphp

<div class="card mt-4 shadow-sm border-0">

    <!-- Header -->
    <div class="card-header bg-gradient d-flex justify-content-between align-items-center"
         style="background: linear-gradient(135deg, #4e73df, #6f42c1); color:white;">

        <h5 class="mb-0">👥 أعضاء المشروع</h5>

        @if($canManage)
            <div class="d-flex gap-2">

                <a href="{{ route('projects.members.create', $project) }}"
                   class="btn btn-sm btn-light">
                    <i class="fas fa-user-plus me-1"></i> إضافة عضو
                </a>

                <a href="{{ route('projects.members.invite', $project) }}"
                   class="btn btn-sm btn-outline-light">
                    <i class="fas fa-envelope me-1"></i> دعوة
                </a>

            </div>
        @endif
    </div>

    <!-- Body -->
    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle text-center mb-0">

                <thead class="table-light">
                <tr>
                    <th>👤 الطالب</th>
                    <th>🎓 الرقم الجامعي</th>
                    <th>🎭 الدور</th>
                    <th>📅 تاريخ الانضمام</th>
                    <th>⚙️ الإجراءات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($members as $member)

                    <tr>

                        <!-- الاسم -->
                        <td class="fw-bold">
                            {{ $member->student->name }}
                        </td>

                        <!-- الرقم -->
                        <td>
                            <span class="text-muted">
                                {{ $member->student->student_id }}
                            </span>
                        </td>

                        <!-- الدور -->
                        <td>
                            @if($member->role == 'leader')
                                <span class="badge bg-primary px-3 py-2">
                                    ⭐ قائد الفريق
                                </span>
                            @else
                                <span class="badge bg-secondary px-3 py-2">
                                    عضو
                                </span>
                            @endif
                        </td>

                        <!-- التاريخ -->
                        <td>
                            <small class="text-muted">
                                {{ optional($member->joined_at)->format('Y-m-d') }}
                            </small>
                        </td>

                        <!-- الإجراءات -->
                        <td>

                            @if(($user->isSupervisor() || $user->isAdmin()) && $member->student_id != $user->id)

                                <div class="d-flex justify-content-center gap-2">

                                    <!-- تغيير الدور -->
                                    <form action="{{ route('projects.members.update-role', [$project, $member]) }}"
                                          method="POST">
                                        @csrf
                                        @method('PUT')

                                        <select name="role"
                                                onchange="this.form.submit()"
                                                class="form-select form-select-sm">

                                            <option value="member" {{ $member->role == 'member' ? 'selected' : '' }}>
                                                عضو
                                            </option>

                                            <option value="leader" {{ $member->role == 'leader' ? 'selected' : '' }}>
                                                قائد
                                            </option>

                                        </select>
                                    </form>

                                    <!-- حذف -->
                                    <form action="{{ route('projects.members.destroy', [$project, $member]) }}"
                                          method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('هل أنت متأكد من إزالة هذا العضو؟')">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>

                                </div>

                            @else
                                <span class="text-muted small">—</span>
                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            🚫 لا يوجد أعضاء حالياً
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endif