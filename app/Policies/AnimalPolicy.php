<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnimalPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        // 利用User Model定義的isAdmin方法判斷
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Animal  $animal
     * @return mixed
     */
    public function view(User $user, Animal $animal)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Animal  $animal
     * @return mixed
     */
    public function update(User $user, Animal $animal)
    {
        return $user->id === $animal->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Animal  $animal
     * @return mixed
     */
    public function delete(User $user, Animal $animal)
    {
        return $user->id === $animal->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Animal  $animal
     * @return mixed
     */
    public function restore(User $user, Animal $animal)
    {
        return $user->id === $animal->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Animal  $animal
     * @return mixed
     */
    public function forceDelete(User $user, Animal $animal)
    {
        return $user->id === $animal->user_id;
    }
}
