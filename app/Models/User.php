<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasShortableUuidTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasUuids, HasShortableUuidTrait;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasMedia;

    protected $mediaFields = [
        'avatar' => [
            'path' => 'avatars',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $appends = [
        'two_factor_enabled', 'two_factor_pending', 'avatar_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function twoFactorEnabled(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->two_factor_secret)
        );
    }

    public function twoFactorPending(): Attribute
    {
        return Attribute::make(
            get: fn () => Fortify::confirmsTwoFactorAuthentication() && ! is_null($this->two_factor_secret) && is_null($this->two_factor_confirmed_at)
        );
    }

    public function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMediaUrl('avatar', 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF')
        );
    }
}
