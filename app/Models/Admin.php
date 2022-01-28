<?php

namespace App\Models;

use App\Enums\AdminRoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Admin represents system administrator account.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static hasPermission(array|string $permission)
 */
class Admin extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
    ];

    /**
     * {@inheritdoc}
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return \App\Enums\AdminPermissionEnum[] list of administration permissions.
     */
    public function getPermissions(): array
    {
        return (new AdminRoleEnum($this->role))->getAdminPermissions();
    }

    public function scopeHasPermission(Builder $query, $permission)
    {
        $roles = AdminRoleEnum::findByPermission($permission);

        return $query->whereIn('role', $roles);
    }
}
