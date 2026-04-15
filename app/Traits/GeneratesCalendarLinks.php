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
        $endAt = $session->scheduled_at->copy()->addMinutes((int) $session->duration_minutes)->format('Ymd\THis\Z');

        $meetingUrl = route('meeting.show', $session->meeting_id);
        $text = urlencode("Session de mentorat : {$session->title}");
        $details = urlencode($session->description."\n\nLien de la session : ".$meetingUrl);
        $location = urlencode($meetingUrl);

        return "https://www.google.com/calendar/render?action=TEMPLATE&text={$text}&dates={$startAt}/{$endAt}&details={$details}&location={$location}&sf=true&output=xml";
    }

    /**
     * Generate ICS file content for a session
     */
    public function generateIcsContent(MentoringSession $session, \App\Models\User $recipient): string
    {
        $timezone = $session->timezone ?: 'UTC';

        // Times in UTC for compatibility
        $startAt = $session->scheduled_at->clone()->setTimezone('UTC')->format('Ymd\THis\Z');
        $endAt = $session->scheduled_at->clone()->addMinutes((int) $session->duration_minutes)->setTimezone('UTC')->format('Ymd\THis\Z');
        $stamp = now()->setTimezone('UTC')->format('Ymd\THis\Z');
        $uid = 'session-'.$session->id.'@brillio.com';

        $meetingUrl = route('meeting.show', $session->meeting_id);
        $summary = 'Session de mentorat : '.$session->title;
        $description = str_replace(["\r", "\n"], '\\n', $session->description."\n\nLien : ".$meetingUrl);
        $location = $meetingUrl;

        $mentor = $session->mentor;
        $organizerEmail = $mentor->email;
        $organizerName = $mentor->name;

        // Escape special characters
        $summary = str_replace([',', ';'], ['\\,', '\\;'], $summary);
        $location = str_replace([',', ';'], ['\\,', '\\;'], $location);

        $isMentor = ($recipient->id === $mentor->id);
        $attendeeLines = '';

        // Add mentees as attendees
        foreach ($session->mentees as $mentee) {
            $role = 'REQ-PARTICIPANT';
            $attendeeLines .= "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE={$role};PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN=\"{$mentee->name}\":mailto:{$mentee->email}\n";
        }

        // Add mentor as attendee too (if mentee is recipient) or just as organizer
        // Some clients need the organizer to also be an attendee to show the invite properly
        $attendeeLines .= "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=CHAIR;PARTSTAT=ACCEPTED;CN=\"{$organizerName}\":mailto:{$organizerEmail}\n";

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
ORGANIZER;CN="{$organizerName}":mailto:{$organizerEmail}
{$attendeeLines}SEQUENCE:0
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:Rappel : {$summary} dans 30 minutes
TRIGGER:-PT30M
END:VALARM
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:Rappel : {$summary} dans 5 minutes
TRIGGER:-PT5M
END:VALARM
END:VEVENT
END:VCALENDAR
EOT;
    }
}
