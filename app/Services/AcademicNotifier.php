<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * إشعارات الوحدات الأكاديمية والمالية عبر نظام الإشعارات القائم (جرس الإشعارات في النظام القديم)،
 * فلا يوجد نظام إشعارات منفصل. لا تحمل الرسائل درجات أو مبالغ حساسة تتجاوز ما يراه المستلم أصلًا.
 * فشل الإشعار لا يُفشل العملية الأساسية.
 */
class AcademicNotifier
{
    public function toUser(User|int|null $user, string $title, string $message, string $type = Notification::TYPE_INFO, ?string $link = null): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        if (! $userId) {
            return;
        }

        try {
            Notification::create(['user_id' => $userId, 'title' => $title, 'message' => $message, 'type' => $type, 'link' => $link]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** @param iterable<User|int|null> $users */
    public function toMany(iterable $users, string $title, string $message, string $type = Notification::TYPE_INFO, ?string $link = null): void
    {
        collect($users)->map(fn ($u) => $u instanceof User ? $u->id : $u)->filter()->unique()
            ->each(fn ($id) => $this->toUser($id, $title, $message, $type, $link));
    }

    /** يُرسل لمن تحمل أدوارهم الصلاحية فعليًا (دون المديرين لتفادي الازدحام)، ويستثني المنفِّذ. */
    public function toPermissionHolders(string $permission, string $title, string $message, string $type = Notification::TYPE_INFO, ?string $link = null, ?int $exceptUserId = null): void
    {
        $this->holders($permission)->reject(fn ($id) => $id === $exceptUserId)
            ->each(fn ($id) => $this->toUser($id, $title, $message, $type, $link));
    }

    /** @return Collection<int, int> */
    public function holders(string $permission): Collection
    {
        return User::whereHas('newRoles.permissions', fn ($q) => $q->where('slug', $permission))->pluck('id');
    }
}
