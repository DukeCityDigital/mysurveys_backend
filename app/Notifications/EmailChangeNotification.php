<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use App\EmailChanges;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\User;

class EmailChangeNotification extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * The user Email
     *
     * @var string
     */
    protected $userId;

    /**
     * Create a new notification instance.
     *
     * @param string $userId
     */
    public function __construct(string $token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $token = User::find(Auth::user()->id)->getChangeEmailToken();
        $verificationUrl = $this->verificationUrl($token);
        return (new MailMessage)
            ->subject(Lang::get(' Email address change request'))
            ->line(Lang::get('Please click the button below to change email address.'))
            ->action(Lang::get('Change Email Address'), $verificationUrl)
            ->line(Lang::get('If you did not request this, no further action is required.'));
    }

    /**
     * Returns the Reset URl to send in the Email
     *
     * @param AnonymousNotifiable $notifiable
     * @return string
     */
    protected function verifyRoute(AnonymousNotifiable $notifiable)
    {
        return URL::temporarySignedRoute('user.email-change-verify', 60 * 60, [
            'user' => $this->userId,
            'email' => $notifiable->routes['mail']
        ]);
    }

    protected function verificationUrl($token)
    {
        $frontend = \config('constants.frontend');
        return $frontend . '/dashboard/profile?change_email=' . $token->token;
    }
}
