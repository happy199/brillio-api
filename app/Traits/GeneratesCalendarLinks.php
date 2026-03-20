<?php

namespace App\Traits;

use App\Models\MentoringSession;

trait GeneratesCalendarLinks
{
    /**
     * Generate a Google Calendar link for a session
     */
    public function generateGoogleCalendarUrl(MentoringSession $session): string
    {
        $startAt = $session->scheduled_at->format('Ymd\THis\Z');
        $endAt = $session->scheduled_at->copy()->addMinutes($session->duration_minutes)->format('Ymd\THis\Z');

        $text = urlencode("Session de mentorat : {$session->title}");
        $details = urlencode($session->description."\n\nLien de la session : ".$session->meeting_link);
        $location = urlencode($session->meeting_link);

        return "https://www.google.com/calendar/render?action=TEMPLATE&text={$text}&dates={$startAt}/{$endAt}&details={$details}&location={$location}&sf=true&output=xml";
    }

    /**
     * Generate ICS file content for a session
     */
    public function generateIcsContent(MentoringSession $session): string
    {
        $startAt = $session->scheduled_at->format('Ymd\THis\Z');
        $endAt = $session->scheduled_at->copy()->addMinutes($session->duration_minutes)->format('Ymd\THis\Z');
        $stamp = now()->format('Ymd\THis\Z');
        $uid = 'session-'.$session->id.'@brillio.com';

        $summary = 'Session de mentorat : '.$session->title;
        $description = str_replace(["\r", "\n"], '\\n', $session->description."\n\nLien : ".$session->meeting_link);
        $location = $session->meeting_link;
        $organizer = $session->mentor->email;
        $organizerName = $session->mentor->name;

        return <<<EOT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Brillio//Mentoring//FR
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTAMP:{$stamp}
DTSTART:{$startAt}
DTEND:{$endAt}
UID:{$uid}
SUMMARY:{$summary}
DESCRIPTION:{$description}
LOCATION:{$location}
STATUS:CONFIRMED
ORGANIZER;CN="{$organizerName}":mailto:{$organizer}
SEQUENCE:0
END:VEVENT
END:VCALENDAR
EOT;
    }
}
