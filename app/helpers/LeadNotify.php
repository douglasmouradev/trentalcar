<?php

declare(strict_types=1);

final class LeadNotify
{
    public static function sendConfirmation(string $email, string $name, string $pickup, string $return, ?string $carLabel = null): void
    {
        $subject = Lang::get('mail.lead_received_subject');
        $body = Lang::get('mail.lead_received_body', [
            'name' => $name,
            'pickup' => $pickup,
            'return' => $return,
            'car' => $carLabel ?? Lang::get('mail.lead_no_car'),
        ]);
        Mail::queue($email, $subject, $body);
    }

    public static function notifyStaff(int $leadId, string $name, string $email, string $phone, string $pickup, string $return, ?string $carLabel, string $local): void
    {
        $panelUrl = Router::url('/leads/' . $leadId);
        $subject = Lang::get('mail.lead_staff_subject', ['name' => $name]);
        $body = Lang::get('mail.lead_staff_body', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'pickup' => $pickup,
            'return' => $return,
            'car' => $carLabel ?? Lang::get('mail.lead_no_car'),
            'local' => $local,
            'url' => $panelUrl,
        ]);
        foreach (User::staffNotificationEmails() as $staffEmail) {
            Mail::queue($staffEmail, $subject, $body);
        }
    }

    public static function whatsappMessage(string $name, string $pickup, string $return, string $local, ?string $carLabel = null): string
    {
        return Lang::get('lead.whatsapp_message', [
            'name' => $name,
            'pickup' => $pickup,
            'return' => $return,
            'local' => $local,
            'car' => $carLabel ?? Lang::get('mail.lead_no_car'),
        ]);
    }
}
