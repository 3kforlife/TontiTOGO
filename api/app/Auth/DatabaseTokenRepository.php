<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatabaseTokenRepository extends BaseDatabaseTokenRepository
{
    /**
     * Create a new token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {
        $email = $user->getEmailForPasswordReset();
        $this->currentPhone = $user->getPhoneForPasswordReset() ?? null;

        $this->deleteExisting($user);

        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($email, $token));

        $this->currentPhone = null;

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return void
     */
    protected function deleteExisting(CanResetPasswordContract $user)
    {
        $query = $this->getTable();
        if ($email = $user->getEmailForPasswordReset()) {
            $query->where('email', $email);
        } elseif ($phone = $user->getPhoneForPasswordReset()) {
            $query->where('phone', $phone);
        } else {
            return;
        }
        $query->delete();
    }

    /**
     * Build the record payload for the table.
     * Signature compatible avec le parent Laravel — $phone passé via propriété.
     */
    protected function getPayload($email, $token): array
    {
        // $phone est récupéré via une propriété temporaire définie dans create()
        return [
            'email'      => $email,
            'phone'      => $this->currentPhone ?? null,
            'token'      => $this->hasher->make($token),
            'created_at' => new Carbon(),
        ];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        $record = $this->getTable()
            ->when($user->getEmailForPasswordReset(), fn($q, $email) => $q->where('email', $email))
            ->when($user->getPhoneForPasswordReset(), fn($q, $phone) => $q->where('phone', $phone))
            ->first();

        return $record && ! $this->tokenExpired($record->created_at) &&
               $this->hasher->check($token, $record->token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        return Carbon::parse($createdAt)->addMinutes($this->expires)->isPast();
    }

    /**
     * Delete a token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return void
     */
    public function delete(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);
    }
}
