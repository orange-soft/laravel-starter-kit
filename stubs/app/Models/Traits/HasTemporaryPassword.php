<?php

namespace App\Models\Traits;

trait HasTemporaryPassword
{
    public function initializeHasTemporaryPassword(): void
    {
        $this->mergeFillable(['must_change_password']);

        $this->mergeCasts(['must_change_password' => 'boolean']);
    }

    public function mustChangePassword(): bool
    {
        return $this->must_change_password ?? false;
    }
}
