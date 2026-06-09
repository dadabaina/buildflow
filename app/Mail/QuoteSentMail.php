<?php

namespace App\Mail;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class QuoteSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Devis ' . $this->quote->reference . ' — ' . ($this->quote->company->name ?? 'BuildFlow'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.quote-sent',
        );
    }

    public function attachments(): array
    {
        $quote   = $this->quote->load(['project', 'client', 'sections.items', 'items']);
        $company = $quote->company;

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'company'))
            ->setPaper('A4', 'portrait');

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->quote->reference . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
