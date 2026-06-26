<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * is_admin and role are intentionally guarded on the User model, so create with
     * forceCreate to let this admin-only screen set them (the 'hashed' cast hashes
     * the password on save).
     */
    protected function handleRecordCreation(array $data): Model
    {
        return User::forceCreate($data);
    }
}
