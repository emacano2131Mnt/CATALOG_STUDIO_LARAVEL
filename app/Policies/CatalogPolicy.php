<?php

namespace App\Policies;

use App\Models\Catalog;
use App\Models\User;

/** Un catálogo solo lo administra su propietario. */
class CatalogPolicy
{
    public function view(User $user, Catalog $catalog): bool { return $catalog->user_id === $user->id; }
    public function update(User $user, Catalog $catalog): bool { return $catalog->user_id === $user->id; }
    public function delete(User $user, Catalog $catalog): bool { return $catalog->user_id === $user->id; }
}
