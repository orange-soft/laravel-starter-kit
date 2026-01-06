<?php

namespace App\Notifications\Auth;

use App\Notifications\AppNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

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
            ->subject(Lang::get('Welcome to :app', ['app' => config('app.name')]))
            ->greeting(Lang::get('Hello :name!', ['name' => $notifiable->name]))
            ->line(Lang::get('An account has been created for you.'))
            ->line(Lang::get('Your temporary password is: **:password**', ['password' => $this->temporaryPassword]))
            ->action(Lang::get('Login Now'), url(route('login', [], false)))
            ->line(Lang::get('You will be required to change your password upon first login.'))
            ->line(Lang::get('If you did not expect this account, please contact the administrator.'));
    }
}
