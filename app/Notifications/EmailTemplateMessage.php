<?php

namespace App\Notifications;

use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\MyProjectsController;
use App\Project;
use App\ProjectParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

use Illuminate\Support\HtmlString;

class EmailTemplateMessage extends VerifyEmailBase
{

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
        $this->emailCtrl = new EmailTemplateController();
    }

    private function getMessage($notifiable, $extra = [])
    {
        $data = $this->data;
        $body_subject = $this->emailCtrl->transformEmailTemplateBodySubject($data, null, $notifiable);

        $body = str_replace("*buttonlink*", "", $body_subject['body']);
        $mailMessage = new MailMessage();
        $mailMessage->subject(Lang::get($body_subject['subject']));

        $lines = explode("*nl*", $body);

        if ($data['custom_message']/*custommessage*/) {
            $mailMessage->line($data['custom_message']);
        }

        foreach ($lines as $line) {
            $mailMessage->line($line);
        }
        // if (isset($this->data['link']) && $this->data['link'] !== '') {
        //     $mailMessage->action($userlink, $userlink);
        // }

        if (strpos($body_subject['body'], "*buttonlink*") && isset($data['project'])) {
            $pCtrl = new MyProjectsController();

            $proj = Project::find($data['project']->id);
            $pp = ProjectParticipant::where("participants_userid", $notifiable->id)->where("projects_projectid", $proj->id)->first();

            $userlink = $pCtrl->makeProjectLink($pp, $proj);
            $mailMessage->action(Lang::get('Start Project'), $userlink);
        }

        if (isset($data['password'])) {

            $verificationUrl = $this->verificationUrl($notifiable);

            $mailMessage->line(new HtmlString('Password: <strong>' . $data['password'] . '</strong>'));
            $mailMessage->action(Lang::get(' Please Verify Email Address'), $verificationUrl);
        }

        // if has password 




        return $mailMessage;
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
        $message = $this->getMessage($notifiable, $this->data);
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }
        if (!$message) {
            return 'no message/project';
        }
        return $this->getMessage($notifiable, $this->data);
    }


    protected function verificationUrl($notifiable)
    {
        $frontend = \config('constants.frontend');
        return $frontend . '/verify/' . $notifiable->verification_code;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
