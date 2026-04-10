@extends('emails.layouts.base')

@section('content')
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #6B46C1; font-size: 24px; font-weight: bold; margin: 0;">Nouvelle Transaction ! 💰</h1>
    </div>

    <h2 style="color: #1F2937; font-size: 20px; font-weight: 600; margin-bottom: 20px;">
        Salut l'équipe,
    </h2>

    <p style="color: #4B5563; line-height: 1.6; margin-bottom: 15px;">
        Une nouvelle transaction vient d'être complétée sur la plateforme Brillio.
    </p>

    <div style="background-color: #F3F4F6; border-left: 4px solid #6B46C1; border-radius: 4px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #374151; font-size: 16px; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #E5E7EB; padding-bottom: 10px;">
            Détails de l'Achat
        </h3>
        
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500; width: 40%;">Client :</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 600;">
                    {{ $entity->name ?? 'Anonyme' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Email :</td>
                <td style="padding: 8px 0; color: #2563EB;">
                    <a href="mailto:{{ $entity->contact_email ?? $entity->email ?? 'N/A' }}">{{ $entity->contact_email ?? $entity->email ?? 'N/A' }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Type d'utilisateur :</td>
                <td style="padding: 8px 0; color: #111827;">
                    @php
                        $displayType = 'Inconnu';
                        if (get_class($entity) === 'App\Models\Organization' || ($transaction->metadata['user_type'] ?? '') === 'organization') {
                            $displayType = 'Organisation';
                        } elseif (isset($entity->user_type)) {
                            $displayType = match($entity->user_type) {
                                'jeune' => 'Jeune',
                                'mentor' => 'Mentor',
                                default => ucfirst($entity->user_type),
                            };
                        }
                    @endphp
                    {{ $displayType }}
                </td>
            </tr>
            <tr><td colspan="2" style="height: 10px;"></td></tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Article acheté :</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 600;">
                    {{ $transaction->metadata['description'] ?? 'Achat' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Crédits :</td>
                <td style="padding: 8px 0; color: #111827;">
                    {{ $transaction->credits_amount > 0 ? '+'.$transaction->credits_amount : 'Aucun' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Montant Encaissé :</td>
                <td style="padding: 8px 0; color: #6B46C1; font-weight: bold; font-size: 18px;">
                    {{ number_format($transaction->amount, 0, ',', ' ') }} {{ $transaction->currency }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6B7280; font-weight: 500;">Transaction ID (Gateway) :</td>
                <td style="padding: 8px 0; color: #111827; font-family: monospace;">{{ $transaction->moneroo_transaction_id }}</td>
            </tr>
        </table>
    </div>

    <p style="color: #6B7280; font-size: 14px; margin-top: 30px; text-align: center;">
        Ce message est généré automatiquement par le système de facturation Brillio Africa.
    </p>

</div>
@endsection
