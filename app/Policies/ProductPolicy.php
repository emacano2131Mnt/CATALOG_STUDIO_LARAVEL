<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/** Un producto pertenece a quien posee su catálogo. */
class ProductPolicy
{
    private function owns(User $user, Product $product): bool
    {
        return $product->catalog()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Product $product): bool { return $this->owns($user, $product); }
    public function update(User $user, Product $product): bool { return $this->owns($user, $product); }
    public function delete(User $user, Product $product): bool { return $this->owns($user, $product); }
}
