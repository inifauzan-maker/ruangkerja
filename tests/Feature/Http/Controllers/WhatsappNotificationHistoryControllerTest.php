<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\WhatsappConnection;
use App\Models\WhatsappNotificationLog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WhatsappNotificationHistoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_notification_history(): void
    {
        $this->get(route('profile.whatsapp.history'))->assertRedirect(route('login'));
    }

    public function test_user_only_sees_their_own_escaped_notification_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create();
        $otherConnection = WhatsappConnection::factory()->for($otherUser)->create();
        WhatsappNotificationLog::factory()->for($connection, 'connection')->create([
            'subject' => 'Own alert <script>alert(1)</script>',
        ]);
        WhatsappNotificationLog::factory()->for($otherConnection, 'connection')->create([
            'subject' => 'Private notification of another user',
        ]);

        $response = $this->actingAs($user)->get(route('profile.whatsapp.history'));

        $response->assertSee('Own alert');
        $response->assertDontSee('Private notification of another user');
        $response->assertDontSee('<script>alert(1)</script>', false);
    }
}
