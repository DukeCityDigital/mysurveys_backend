<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;
use App\ProjectParticipant;
use App\Participant;

class FriendVerifyApiEmail extends VerifyEmailBase
{

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Verify API Email Get Message - send actual
     */
    private function getMessage($notifiable, $extra = [])
    {

        $recipient_seed = Participant::where("user_id", $notifiable->id)->first();

        $sender_nickname = Participant::find($recipient_seed->seed_id)->nickname;


        $role = $notifiable->getRoleNames()[0];
        $verificationUrl = $this->verificationUrl($notifiable);
        $subjectIntro = '[' . config('app.name') . ']';
        $resend = isset($this->data['resend']) || false;
        $salutation = 'Hello!';

        $mailMessage = new MailMessage();
        $mailMessage
            ->subject(Lang::get($subjectIntro . ' Verify Email Address'));

        if (isset($this->data['custom_message'])) {
            $mailMessage
                ->line($this->data['custom_message'])
                ->line('');
        }
        // $mailMessage->line(Lang::get($salutation));
        $mailMessage->line("Your friend " . $sender_nickname . " is inviting you to join a study that investigates people’s beliefs about different issues, including vaccination.");
        $mailMessage->line('');
        $mailMessage->line('The study is completely confidential and you will be paid $1 for signing up and $10 for completing the study.');




        if (!$resend && !isset($this->data['qualificationForm'])) {
            $mailMessage
                ->line('Your temporary password is on the next line, please change it after logging in.')
                ->line(new HtmlString('<strong>' . $this->data['password'] . '</strong>'));
        }
        $mailMessage
            ->line('Please click the button below to verify your email address.')
            ->action(Lang::get(' Please Verify Email Address'), $verificationUrl)
            ->line('')
            ->line(new HtmlString('<bold>For additional project support, contact mysurveysteam@gmail.com</bold>'));



        return $mailMessage;
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return $this->getMessage($notifiable);
    }

    protected function verificationUrl($notifiable)
    {
        $frontend = \config('constants.frontend');
        return $frontend . '/verify/' . $notifiable->verification_code;
    }
}
