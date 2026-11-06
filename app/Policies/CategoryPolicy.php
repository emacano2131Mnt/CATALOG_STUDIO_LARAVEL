<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/** Las categorías globales (user_id NULL) se pueden ver y usar, pero no editar ni borrar. */
class CategoryPolicy
{
    public function view(User $user, Category $category): bool { return $category->user_id === $user->id || $category->user_id === null; }
    public function update(User $user, Category $category): bool { return $category->user_id === $user->id; }
    public function delete(User $user, Category $category): bool { return $category->user_id === $user->id; }
}
