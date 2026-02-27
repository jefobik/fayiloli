<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TrackFailedLoginAttempt
{
    /**
     * Maximum consecutive wrong-password attempts before account auto-lock.
     */
    protected const MAX_ATTEMPTS_BEFORE_LOCK = 5;

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Failed  $event
     * @return void
     */
    public function handle(Failed $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        if (!$user) {
            return;
        }

        $attempts = ($user->failed_login_attempts ?? 0) + 1;

        $updates = ['failed_login_attempts' => $attempts];

        if ($attempts >= self::MAX_ATTEMPTS_BEFORE_LOCK && !$user->is_locked) {
            $updates['is_locked'] = true;

            // Only set locked_at if the column exists in the current DB context.
            if (array_key_exists('locked_at', $user->getAttributes())) {
                $updates['locked_at'] = now();
            }
        }

        $user->forceFill($updates)->saveQuietly();
    }
}
