<?php

namespace App\Notifications\Organization;

use App\Models\Organization\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class OrganizationInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public OrganizationInvitation $invitation
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = URL::signedRoute('invitations.accept', [
            'invitation' => $this->invitation,
        ]);
        return (new MailMessage)
            ->subject('You have been invited to join an organization')
            ->markdown('mails.organization.invitation', [
                'acceptUrl' => $acceptUrl,
                'invitation' => $this->invitation,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
