<?php

namespace Tests\Feature\Admin;

use App\Models\EmailSuppression;
use App\Services\BounceProcessingService;
use App\Services\BrillioIAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BounceProcessingTest extends TestCase
{
    use CreatesAdminUser;
    use RefreshDatabase;

    public function test_parses_google_550_hard_bounce_and_suppresses_immediately(): void
    {
        $service = app(BounceProcessingService::class);

        $googleBounceText = <<<'TXT'
Delivery Status Notification (Failure)
Mail Delivery Subsystem <mailer-daemon@bounces.jellyfish.systems>
To: <contact@brillio.africa>

Delivery to the following recipient failed permanently:
rachid.bello.bj@gmail.com

Technical details of permanent failure:
550-5.1.1 The email account that you tried to reach does not exist. Please try double-checking the recipient's email address for typos or unnecessary spaces.
For more information, go to https://support.google.com/mail/?p=NoSuchUser 5a478bee46e88-36084480d8asi20728476eec.80 - gsmtp
TXT;

        $result = $service->parseBounceText($googleBounceText);

        $this->assertEquals(1, $result['total_found']);
        $this->assertEquals(1, $result['hard_bounced']);
        $this->assertContains('rachid.bello.bj@gmail.com', $result['suppressed']);

        $suppression = EmailSuppression::where('email', 'rachid.bello.bj@gmail.com')->first();
        $this->assertNotNull($suppression);
        $this->assertEquals('hard', $suppression->bounce_type);
        $this->assertEquals('system_auto', $suppression->source);
    }

    public function test_parses_google_452_soft_bounce_for_full_mailbox(): void
    {
        $service = app(BounceProcessingService::class);

        $softBounceText = <<<'TXT'
Delivery Status Notification (Delay)
Mail Delivery Subsystem <mailer-daemon@bounces.jellyfish.systems>
To: <contact@brillio.africa>

Delivery incomplete
There was a temporary problem delivering your message to kimberleyshennel@gmail.com.
Delivery will be retried. You'll be notified if the delivery fails permanently.

Technical details of the failure:
452-4.2.2 The recipient's inbox is out of storage space. Please direct the recipient to https://support.google.com/mail/?p=OverQuotaTemp a92af1059eb24-185b2c94c66si17320678c88.34 - gsmtp
TXT;

        $result = $service->parseBounceText($softBounceText);

        $this->assertEquals(1, $result['total_found']);
        $this->assertEquals(1, $result['soft_bounced']);
        $this->assertContains('kimberleyshennel@gmail.com', $result['suppressed']);

        $suppression = EmailSuppression::where('email', 'kimberleyshennel@gmail.com')->first();
        $this->assertNotNull($suppression);
        $this->assertEquals('soft', $suppression->bounce_type);
        $this->assertEquals(1, $suppression->bounce_count);
    }

    public function test_admin_can_import_bounces_via_web_interface(): void
    {
        $admin = $this->createAdminUser();

        $bounceInput = 'Delivery to the following recipient failed permanently: user.test.bounce@gmail.com 550-5.1.1 NoSuchUser';

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.audits.suppressions.import-bounces'), [
                'bounce_text' => $bounceInput,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'user.test.bounce@gmail.com',
            'bounce_type' => 'hard',
        ]);
    }

    public function test_admin_can_trigger_domain_scan(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.audits.suppressions.scan-domains'));

        $response->assertRedirect();
    }

    public function test_artisan_emails_process_bounces_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('emails:process-bounces');
        $this->assertEquals(0, $exitCode);
    }

    public function test_ai_bounce_analysis_extracts_and_suppresses_complex_bounce(): void
    {
        $mockIa = $this->createMock(BrillioIAService::class);
        $mockIa->method('isApiKeyConfigured')->willReturn(true);
        $mockIa->method('analyzeText')->willReturn('{"recipients":["complex.bounce@domain.org"],"diagnostic":"Serveur distant injoignable après 5 tentatives","bounce_type":"hard","smtp_code":"554","recommended_action":"exclude_immediately","action_explanation":"Exclusion immédiate recommandée"}');
        $mockIa->method('cleanJson')->willReturn('{"recipients":["complex.bounce@domain.org"],"diagnostic":"Serveur distant injoignable après 5 tentatives","bounce_type":"hard","smtp_code":"554","recommended_action":"exclude_immediately","action_explanation":"Exclusion immédiate recommandée"}');

        $this->app->instance(BrillioIAService::class, $mockIa);

        $service = app(BounceProcessingService::class);
        $result = $service->analyzeBounceWithAi('Exotic bounce log from relay');

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['suppressed_count']);
        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'complex.bounce@domain.org',
            'bounce_type' => 'hard',
        ]);
    }
}
