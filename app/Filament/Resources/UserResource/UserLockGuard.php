<?php

namespace App\Filament\Resources\UserResource;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

/**
 * Guard rails so an admin account cannot lock everyone out of the panel.
 *
 * Two rules are enforced:
 *  - An admin cannot deactivate or delete their own account.
 *  - The last remaining active admin (role = admin AND status = active) cannot
 *    be deactivated, deleted, or demoted to kasir.
 */
class UserLockGuard
{
    public const SELF = 'self';

    public const LAST_ADMIN = 'last_admin';

    public const OPERATION_DEACTIVATE = 'deactivate';

    public const OPERATION_DEMOTE = 'demote';

    public const OPERATION_DELETE = 'delete';

    public static function activeAdminCount(): int
    {
        return User::query()
            ->where('role', UserRole::Admin->value)
            ->where('status', UserStatus::Active->value)
            ->count();
    }

    public static function isActiveAdmin(User $user): bool
    {
        return $user->role === UserRole::Admin && $user->status === UserStatus::Active;
    }

    /**
     * Validate an edit (status change and/or role change).
     *
     * @return string|null one of the violation constants, or null when allowed
     */
    public static function updateViolation(
        User $actor,
        User $target,
        ?UserRole $newRole,
        ?UserStatus $newStatus,
    ): ?string {
        $newRole ??= $target->role;
        $newStatus ??= $target->status;

        $stillActiveAdmin = $newRole === UserRole::Admin && $newStatus === UserStatus::Active;

        // Last-admin guard first so a sole admin acting on their own account
        // reports the lockout risk instead of the (also true) self guard.
        if (self::isActiveAdmin($target) && ! $stillActiveAdmin && self::activeAdminCount() <= 1) {
            return self::LAST_ADMIN;
        }

        if ($actor->getKey() === $target->getKey() && $newStatus === UserStatus::Inactive) {
            return self::SELF;
        }

        return null;
    }

    /**
     * Validate a deletion.
     *
     * @return string|null one of the violation constants, or null when allowed
     */
    public static function deleteViolation(User $actor, User $target): ?string
    {
        if (self::isActiveAdmin($target) && self::activeAdminCount() <= 1) {
            return self::LAST_ADMIN;
        }

        if ($actor->getKey() === $target->getKey()) {
            return self::SELF;
        }

        return null;
    }

    public static function title(string $violation, string $operation): string
    {
        if ($violation === self::SELF) {
            return match ($operation) {
                self::OPERATION_DELETE => 'Tidak Dapat Menghapus Akun Sendiri',
                self::OPERATION_DEACTIVATE => 'Tidak Dapat Menonaktifkan Akun Sendiri',
                default => 'Tindakan Tidak Diizinkan',
            };
        }

        return match ($operation) {
            self::OPERATION_DELETE => 'Tidak Dapat Menghapus Admin Terakhir',
            self::OPERATION_DEACTIVATE => 'Tidak Dapat Menonaktifkan Admin Terakhir',
            self::OPERATION_DEMOTE => 'Tidak Dapat Menurunkan Admin Terakhir',
            default => 'Tindakan Tidak Diizinkan',
        };
    }

    public static function message(string $violation, string $operation): string
    {
        if ($violation === self::SELF) {
            return match ($operation) {
                self::OPERATION_DELETE => 'Anda tidak dapat menghapus akun Anda sendiri.',
                self::OPERATION_DEACTIVATE => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
                default => 'Anda tidak dapat melakukan tindakan ini pada akun Anda sendiri.',
            };
        }

        return match ($operation) {
            self::OPERATION_DELETE => 'Akun ini adalah satu-satunya admin aktif dan tidak dapat dihapus. Tambahkan admin aktif lain terlebih dahulu.',
            self::OPERATION_DEACTIVATE => 'Akun ini adalah satu-satunya admin aktif dan tidak dapat dinonaktifkan. Tambahkan admin aktif lain terlebih dahulu.',
            self::OPERATION_DEMOTE => 'Akun ini adalah satu-satunya admin aktif dan tidak dapat diturunkan menjadi kasir. Tambahkan admin aktif lain terlebih dahulu.',
            default => 'Tindakan ini akan menghilangkan admin aktif terakhir.',
        };
    }
}
