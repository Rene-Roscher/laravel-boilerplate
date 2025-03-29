<?php

namespace App\Models\Traits;

use App\Enums\OrganizationRoleEnum;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @copyright Laravel (Jetstream)
 */
trait HasOrganizations
{
    // boot and create

    public static function bootHasOrganizations()
    {
        static::creating(function ($model) {
            $model->current_organization_id = $model->ownedOrganizations()->create([
                'name' => explode(' ', $model->name, 2)[0]."'s Org",
                'is_default' => true,
            ])->id;
        });
    }

    /**
     * Determine if the given organization is the current organization.
     *
     * @param Organization $organization
     * @return bool
     */
    public function isCurrentOrganization(
        Organization $organization
    ): bool
    {
        return $organization->id === $this->currentOrganization->id;
    }

    /**
     * Get the current organization of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentOrganization(): BelongsTo
    {
        if (is_null($this->current_organization_id) && $this->id)
            $this->switchOrganization($this->defaultOrganization());

        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    /**
     * Switch the user's context to the given organization.
     *
     * @param Organization $organization
     * @return bool
     */
    public function switchOrganization(
        Organization $organization
    ): bool
    {
        if (!$this->belongsToOrganization($organization))
            return false;

        $this->forceFill([
            'current_organization_id' => $organization->id,
        ])->save();

        $this->setRelation('currentOrganization', $organization);

        return true;
    }

    /**
     * Get all the organizations the user owns or belongs to.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allOrganizations(): Collection
    {
        return $this->ownedOrganizations->merge($this->organizations)->sortBy('name');
    }

    /**
     * Get all the organizations the user owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Get all the organizations the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, OrganizationUser::class)
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get the user's "default" organization.
     *
     * @return \App\Models\Organization\Organization
     */
    public function defaultOrganization(): Organization
    {
        return $this->ownedOrganizations->where('is_default', true)->first();
    }

    /**
     * Determine if the user owns the given organization.
     *
     * @param \App\Models\Organization\Organization|null $organization
     * @return bool
     */
    public function ownsOrganization(
        ?Organization $organization
    ): bool
    {
        if (is_null($organization))
            return false;

        return $this->id == $organization->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs to the given organization.
     *
     * @param \App\Models\Organization\Organization|null $organization
     * @return bool
     */
    public function belongsToOrganization(
        ?Organization $organization
    ): bool
    {
        if (is_null($organization)) {
            return false;
        }

        return $this->ownsOrganization($organization) || $this->organizations->contains(function ($t) use ($organization) {
                return $t->id === $organization->id;
            });
    }

    /**
     * Get the role that the user has on the organization.
     *
     * @param \App\Models\Organization\Organization|null $organization
     * @return array|null
     */
    public function organizationRole(
        ?Organization $organization
    ): array|null
    {
        if (is_null($organization)) {
            $organization = $this->currentOrganization;
        }

        if ($this->ownsOrganization($organization)) {
            return OrganizationRoleEnum::owner();
        }

        if (!$this->belongsToOrganization($organization)) {
            return null;
        }

        $role = $organization->users
            ->where('id', $this->id)
            ->first()
            ->membership
            ->role;

        return $role ? OrganizationRoleEnum::fromName($role)->toArray() : null;
    }

    /**
     * Determine if the user has the given role on the given organization.
     *
     * @param Organization $organization
     * @param \App\Enums\OrganizationRoleEnum|string $role
     * @return bool
     */
    public function hasOrganizationRole(
        Organization                $organization,
        OrganizationRoleEnum|string $role
    ): bool
    {
        if (is_string($role))
            $role = OrganizationRoleEnum::fromName($role);

        if ($this->ownsOrganization($organization)) {
            return true;
        }

        return $this->belongsToOrganization($organization) && optional($organization->users->where(
                'id', $this->id
            )->first()->membership)->role === $role->name;
    }

    /**
     * Get the user's permissions for the given organization.
     *
     * @param Organization $organization
     * @return array
     */
    public function organizationPermissions(Organization $organization): array
    {
        if ($this->ownsOrganization($organization)) {
            return ['*'];
        }

        if (!$this->belongsToOrganization($organization)) {
            return [];
        }

        return (array)optional($this->organizationRole($organization))->permissions;
    }

    /**
     * Determine if the user has the given permission on the given organization.
     *
     * @param Organization $organization
     * @param string $permission
     * @return bool
     */
    public function hasOrganizationPermission(
        Organization $organization,
        string       $permission
    ): bool
    {
        if ($this->ownsOrganization($organization)) {
            return true;
        }

        if (!$this->belongsToOrganization($organization)) {
            return false;
        }

//        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
//            !$this->tokenCan($permission) &&
//            $this->currentAccessToken() !== null) {
//            return false;
//        }

        $permissions = $this->organizationPermissions($organization);

        return in_array($permission, $permissions) || in_array('*', $permissions) ||
            (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
            (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }

}
