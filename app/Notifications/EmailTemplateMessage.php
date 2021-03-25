<?php

namespace App\Notifications;

use App\Http\Controllers\EmailTemplateController;
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


        $body = $this->emailCtrl->transformEmailTemplateBody($data, $data['project']->id, $notifiable);

        // var_dump($body);

        $mailMessage = new MailMessage();
        $mailMessage->subject(Lang::get($body['subject']));

        $lines = explode("*nl*", $body['body']);

        foreach ($lines as $line) {
            $mailMessage->line($line);
        }
        if (isset($this->data['link']) && $this->data['link'] !== '') {
            $mailMessage->action(Lang::get($this->data['link']), $this->data['link']);
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
