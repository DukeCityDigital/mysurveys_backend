<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\MyProjectsController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;


use App\Log;
use App\User;

class ProjectInvitation extends Notification implements ShouldQueue
{

    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
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

        $notifiable->load('participant');

        return $this->getMessage($notifiable, $this->data);
    }

    /*
     * Build invitation
     *
     * TODO:  contact physical address?
     */

    private function getMessage($notifiable, $extra = [])
    {

        $project = $this->data;
        $participant = $project['participant'];


        $projController = new MyProjectsController();
        $link = $projController->makeProjectLink($participant, $project);
        $mailMessage = new MailMessage();

        $reminder = $project['remind'] ? 'Reminder: ' : '';

        $mailMessage
            ->from("mysurveys@santafe.edu", "MySurveys Support")
            ->replyTo("mysurveys@santafe.edu", "MySurveys Support")
            ->subject(Lang::get($reminder . 'Invitation to a ' . config('app.name') . ' Study'))
            ->greeting("Greetings " . $notifiable->participant['first_name'] . "!");



        $mailMessage
            ->line('You are invited to join the following study:')
            ->line("Project title: " . $project['project_title'])
            ->line("Project description: " . $project['description'])
            ->line("The payment for participation is $" . $project['max_payout'] . " USD.  The study will be open until " . $project['defaultend'] . " or until enough people participate.")
            ->line("If you have any questions, please contact " . $project['responsible_person'])

            ->line('Please click on the link below to start the project.')
            ->action('Start Project', $link)
            ->line('')
            ->line(new HtmlString('<bold>For additional project support, contact mysurveys@santafe.edu</bold>'))
            ->line('')
            // ->action(Lang::get($this->projectInvitationUrl()), $this->projectInvitationUrl())
        ;

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

    protected function projectInvitationUrl()
    {
        $frontend = \config('constants.frontend');
        return $frontend . '/dashboard/my-projects';
    }
}
