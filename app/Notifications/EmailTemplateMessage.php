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
        $message = $this->getMessage($notifiable, $this->data);
        if (!$message) {
            return 'no message/project';
        }
        return $this->getMessage($notifiable, $this->data);
    }


    private function getMessage($notifiable, $extra = [])
    {
        $data = $this->data;

        $body_subject = $this->emailCtrl->transformEmailTemplateBodySubject($data, null, $notifiable);
      
        $body = str_replace("*buttonlink*", "", $body_subject['body']);
        $mailMessage = new MailMessage();
        $mailMessage->subject(Lang::get($body_subject['subject']));

        $lines = explode("*nl*", $body);

        foreach ($lines as $line) {
            $mailMessage->line($line);
        }




        // if (isset($this->data['link']) && $this->data['link'] !== '') {
        //     $mailMessage->action($userlink, $userlink);
        // }

     
        if (strpos($body_subject['body'], "*buttonlink*")&& isset($data['project'])) {
            $pCtrl = new MyProjectsController();

            $proj = Project::find($data['project']->id);
            $pp = ProjectParticipant::where("participants_userid", $notifiable->id)->where("projects_projectid", $proj->id)->first();

            $userlink = $pCtrl->makeProjectLink($pp, $proj);
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
