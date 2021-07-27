<?php

namespace App\Notifications;

use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\MyProjectsController;
use App\Project;
use App\ProjectParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class EmailTemplateMessage extends Notification
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
        return $this->getMessage($notifiable, $this->data);
    }


    private function getMessage($notifiable, $extra = [])
    {
        $data = $this->data;

        $body1 = $this->emailCtrl->transformEmailTemplateBody($data, $data['project']->id, $notifiable);
        $body = str_replace("*buttonlink*", "", $body1);

        $mailMessage = new MailMessage();
        $mailMessage->subject(Lang::get($body['subject']));

        $lines = explode("*nl*", $body['body']);

        foreach ($lines as $line) {
            $mailMessage->line($line);
        }

        $pCtrl = new MyProjectsController();
        $proj = Project::find($data['project']->id);
        $pp = ProjectParticipant::where("participants_userid", $notifiable->id)->where("projects_projectid", $proj->id)->first();
        var_dump($pp);
        var_dump($proj);

        $userlink = $pCtrl->makeProjectLink($pp, $proj);


        if (isset($this->data['link']) && $this->data['link'] !== '') {
            $mailMessage->action($userlink, $userlink);
        }

        if (strpos($body1['body'], "*buttonlink*")) {
            $mailMessage->action(Lang::get('Start Project'), $userlink);
        }



        return $mailMessage;
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
