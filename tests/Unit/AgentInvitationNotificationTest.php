<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\AgentInvitationNotification;
use Tests\TestCase;

class AgentInvitationNotificationTest extends TestCase
{
    public function test_it_includes_the_temporary_password_in_the_email_body(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $notification = new AgentInvitationNotification(temporaryPassword: 'AbC@1234');
        $mail = $notification->toMail($user);

        $this->assertSame('Temporary Password', $mail->subject);
        $this->assertContains('Your temporary password is: AbC@1234', $mail->introLines);
        $this->assertStringNotContainsString('Generate Password', $mail->render());
    }
}
