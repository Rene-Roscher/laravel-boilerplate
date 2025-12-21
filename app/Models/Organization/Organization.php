<?php

namespace App\Models\Organization;

use App\Models\BaseModel;
use App\Models\Traits\HasMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperOrganization
 */
class Organization extends BaseModel
{
    use HasMedia;

    protected $fillable = [
        'name',
        'user_id',
        'is_default',
        'avatar',
    ];

    protected array $mediaFields = [
        'avatar' => [
            'path' => 'avatars',
        ],
    ];

    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMediaUrl('avatar', 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF')
        );
    }

    /**
     * Get the owner of the organization.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all the organization's users including its owner.
     */
    public function allUsers(): Collection
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Get all the users that belong to the organization.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, OrganizationUser::class)
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Determine if the given user belongs to the organization.
     */
    public function hasUser(
        User $user
    ): bool {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }

    /**
     * Determine if the given email address belongs to a user on the organization.
     */
    public function hasUserWithEmail(
        string $email
    ): bool {
        return $this->allUsers()->contains(
            fn ($user) => $user->email === $email
        );
    }

    /**
     * Determine if the given user has the given permission on the organization.
     */
    public function userHasPermission(
        User $user,
        string $permission
    ): bool {
        return $user->hasOrganizationPermission($this, $permission);
    }

    /**
     * Get all the pending user invitations for the organization.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    /**
     * Remove the given user from the organization.
     */
    public function removeUser(
        User $user
    ): void {
        if ($user->current_organization_id === $this->id) {
            $user->forceFill([
                'current_organization_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all the organization's resources.
     */
    public function purge(): void
    {
        $this->owner()->where('current_organization_id', $this->id)
            ->update(['current_organization_id' => null]);

        $this->users()->where('current_organization_id', $this->id)
            ->update(['current_organization_id' => null]);

        $this->users()->detach();

        $this->delete();
    }
}
