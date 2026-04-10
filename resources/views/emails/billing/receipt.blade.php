@extends('emails.layouts.base')

@section('content')
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <!-- Logo Branding -->
        <h1 style="color: #6B46C1; font-size: 24px; font-weight: bold; margin: 0;">Brillio Africa</h1>
    </div>

    <h2 style="color: #1F2937; font-size: 20px; font-weight: 600; margin-bottom: 20px;">
        Salut {{ $entity->name ?? 'Cher Membre' }},
    </h2>

    <p style="color: #4B5563; line-height: 1.6; margin-bottom: 15px;">
        Nous vous remercions chaudement pour votre achat sur la plateforme Brillio !
    </p>

    <div style="background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #374151; font-size: 16px; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #E5E7EB; padding-bottom: 10px;">
            Récapitulatif de votre achat
        </h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500; width: 40%;">Description :</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 600;">
                    {{ $transaction->metadata['description'] ?? 'Achats Brillio' }}
                    @if($transaction->credits_amount > 0)
                        ({{ $transaction->credits_amount }} crédits)
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Montant total :</td>
                <td style="padding: 8px 0; color: #374151; font-weight: bold;">
                    {{ number_format($transaction->amount, 0, ',', ' ') }} {{ $transaction->currency }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Date de transaction :</td>
                <td style="padding: 8px 0; color: #111827;">{{ $transaction->completed_at ? $transaction->completed_at->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Numéro de facture :</td>
                <td style="padding: 8px 0; color: #111827; font-family: monospace;">{{ $transaction->moneroo_transaction_id }}</td>
            </tr>
        </table>
    </div>

    <p style="color: #4B5563; line-height: 1.6; margin-bottom: 25px;">
        Vous trouverez <strong>votre facture officielle détaillée en pièce jointe (PDF)</strong> de cet email. 
        Vos services et crédits ont été automatiquement ajoutés à votre compte.
    </p>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}" style="background-color: #6B46C1; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">
            Accéder à mon compte
        </a>
    </div>

    <p style="color: #6B7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #E5E7EB; padding-top: 20px;">
        Si vous avez des questions concernant cet achat, veuillez nous contacter à l'adresse <a href="mailto:contact@brillio.africa" style="color: #6B46C1;">contact@brillio.africa</a>.
    </p>

    <p style="color: #9CA3AF; font-size: 12px; margin-top: 10px; text-align: center;">
        Merci,<br>
        L'équipe Brillio Africa
    </p>
</div>
@endsection
