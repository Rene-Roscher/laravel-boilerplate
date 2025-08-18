<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

abstract class Policy
{
    use HandlesAuthorization;

    /**
     * Authorization modes:
     * 'strict' = Permission AND Ownership required
     * 'permissive' = Permission OR Ownership sufficient
     * 'ownership' = Only Ownership check
     * 'permission' = Only Permission check
     */
    protected string $mode = 'permissive';

    /**
     * Resource name for permissions (auto-generated if null)
     */
    protected ?string $resource = null;

    /**
     * The field that contains the owner's user ID
     */
    protected string $ownerField = 'user_id';

    /**
     * Default: Everyone can view lists (filtered in controller)
     * Override in child classes if needed
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return $this->check($user, 'view', $model);
    }

    public function create(User $user): bool
    {
        return $user->can($this->permission('create'));
    }

    public function update(User $user, Model $model): bool
    {
        return $this->check($user, 'update', $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->check($user, 'delete', $model);
    }

    /**
     * Restore soft deleted models
     */
    public function restore(User $user, Model $model): bool
    {
        return $this->check($user, 'restore', $model);
    }

    /**
     * Permanently delete soft deleted models
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $this->check($user, 'forceDelete', $model);
    }

    /**
     * Core authorization logic
     */
    protected function check(User $user, string $ability, ?Model $model = null): bool
    {
        $hasPermission = $this->hasPermission($user, $ability, $model);
        $hasOwnership = $model && $this->owns($user, $model);

        return match ($this->mode) {
            'strict' => $hasPermission && (! $model || $hasOwnership),
            'permissive' => $hasPermission || $hasOwnership,
            'ownership' => $hasOwnership,
            'permission' => $hasPermission,
            default => false
        };
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission(User $user, string $ability, ?Model $model = null): bool
    {
        return $user->can($this->permission($ability));
    }

    /**
     * Check ownership - override in child classes for complex logic
     */
    protected function owns(User $user, Model $model): bool
    {
        $field = $this->getOwnerField($model);

        // Check if the attribute exists in the model's attributes array
        try {
            $value = $model->getAttribute($field);

            return $value === $user->id;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the owner field for a specific model
     * Can be overridden for dynamic field selection
     */
    protected function getOwnerField(Model $model): string
    {
        return $this->ownerField;
    }

    /**
     * Generate permission name
     */
    protected function permission(string $ability): string
    {
        $resource = $this->resource ?? str(class_basename(static::class))
            ->replace('Policy', '')
            ->snake()
            ->toString();

        return "{$resource}.{$ability}";
    }
}
