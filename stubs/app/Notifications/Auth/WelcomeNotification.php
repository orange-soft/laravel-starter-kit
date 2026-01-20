<?php

namespace App\Notifications\Auth;

use App\Notifications\AppNotification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends AppNotification
{
    /**
     * The temporary password.
     */
    public string $temporaryPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct(#[\SensitiveParameter] string $temporaryPassword)
    {
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Welcome to :app', ['app' => config('app.name')]))
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__('An account has been created for you.'))
            ->line(__('Your temporary password is: **:password**', ['password' => $this->temporaryPassword]))
            ->action(__('Login Now'), url(route('login', [], false)))
            ->line(__('You will be required to change your password upon first login.'))
            ->line(__('If you did not expect this account, please contact the administrator.'));
    }
}
