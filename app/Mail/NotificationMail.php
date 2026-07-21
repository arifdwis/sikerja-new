<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectTitle;
    public string $rawMessage;
    public string $formattedContent;
    public ?string $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subjectTitle, string $rawMessage, ?string $actionUrl = null)
    {
        $this->subjectTitle = $subjectTitle;
        $this->rawMessage = $rawMessage;
        $this->actionUrl = $actionUrl ?? config('app.url', 'https://sikerja-v2.samarindakota.go.id');
        $this->formattedContent = self::formatWhatsAppToHtml($rawMessage);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIKERJA] ' . $this->subjectTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $logoPath = public_path('foto/logo-sikerja.svg');
        $hasLogo = file_exists($logoPath);

        return new Content(
            view: 'emails.notification',
            with: [
                'subjectTitle' => $this->subjectTitle,
                'formattedContent' => $this->formattedContent,
                'actionUrl' => $this->actionUrl,
                'logoPath' => $hasLogo ? $logoPath : null,
            ],
        );
    }

    /**
     * Convert WhatsApp markdown format (*text*, _text_, newlines) to clean HTML for email rendering
     */
    public static function formatWhatsAppToHtml(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Convert literal '\n' string representations into real newlines
        $text = str_replace(["\r\n", '\r\n', '\n'], "\n", $text);

        // 2. Decode html entities in case string was pre-escaped
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 3. Remove redundant formal footers (since email template has its own styled footer)
        $footersToRemove = [
            "Hormat kami,\nTim Kerja Sama Daerah\nPemerintah Kota Samarinda",
            "Hormat kami,\nTim Kerja Sama Daerah",
            "_Sistem Kerja Sama Daerah Samarinda_",
            "Hormat kami,",
        ];
        foreach ($footersToRemove as $footer) {
            $text = str_replace($footer, '', $text);
        }

        // 4. Convert WhatsApp bold *text* -> <strong>text</strong>
        $text = preg_replace('/\*(.*?)\*/s', '<strong>$1</strong>', $text);

        // 5. Convert WhatsApp italic _text_ -> <em>text</em>
        $text = preg_replace('/_(.*?)_/s', '<em>$1</em>', $text);

        // 6. Split lines & clean up trailing/leading blank lines
        $lines = array_map('trim', explode("\n", trim($text)));
        $cleanLines = array_values(array_filter($lines, fn($l) => $l !== ''));

        // Join clean lines with <br /><br /> or single <br /> depending on flow
        $htmlResult = implode("<br />", $lines);

        // Clean any triple <br /> artifacts
        $htmlResult = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br /><br />', $htmlResult);

        return trim($htmlResult);
    }
}
