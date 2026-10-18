<?php

namespace App\Models;

use App\Exceptions\CronAutomationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CronAutomation extends Model
{
    use HasFactory;

    public const FREQ_HOURLY = 'Toutes les heures';

    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
        'frequency_label',
        'target',
        'action_type',
        'action_class',
        'is_enabled',
        'last_run_at',
        'last_run_status',
        'last_run_duration',
        'last_run_output',
        'total_runs',
        'total_failures',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_run_at' => 'datetime',
        'last_run_duration' => 'float',
        'total_runs' => 'integer',
        'total_failures' => 'integer',
    ];

    /**
     * Dictionnaire canonique des automatisations système.
     */
    public static function definedAutomations(): array
    {
        $raw = [
            'inactivity_reminders' => ['Relance des jeunes inactifs', 'emails', 'Envoie un email personnalisé de relance encourageant les jeunes inactifs à revenir sur la plateforme pour explorer les métiers et les opportunités.', 'Toutes les heures (max 500/lot)', 'Jeunes inscrits sans connexion depuis au moins 7 jours n\'ayant pas reçu de rappel cette semaine', 'job', 'App\Jobs\SendInactivityReminders'],
            'session_reminders' => ['Rappels des séances de mentorat', 'mentorship', 'Envoie des alertes par email 24 heures et 1 heure avant le début de chaque séance planifiée afin d\'éviter les oublis et les absences.', self::FREQ_HOURLY, 'Mentors et mentorés ayant une séance confirmée prévue dans les 24h ou dans l\'heure qui suit', 'command', 'sessions:send-reminders'],
            'mentor_report_reminders' => ['Rappel du compte-rendu post-séance (Escrow)', 'mentorship', 'Relance les mentors ayant achevé une séance sans avoir complété leur rapport d\'évaluation, étape nécessaire pour débloquer le paiement sous séquestre.', 'Tous les jours à 10:00', 'Mentors avec séance terminée en attente de compte-rendu d\'évaluation', 'job', 'App\Jobs\SendMentorReportReminders'],
            'profile_completion_reminders' => ['Rappel de complétion du profil', 'emails', 'Analyse les sections non remplies (biographie, compétences, avatar) et envoie des conseils ciblés pour inciter l\'utilisateur à finaliser son profil.', 'Tous les lundis à 09:00', 'Jeunes et mentors inscrits dont le profil est incomplet ou non publié', 'job', 'App\Jobs\SendProfileCompletionReminders'],
            'missing_phone_reminders' => ['Rappel pour numéro de téléphone manquant', 'emails', 'Invite par email les utilisateurs à renseigner leur numéro WhatsApp ou mobile pour faciliter les contacts directs et les notifications urgentes.', 'Tous les dimanches à 09:00', 'Utilisateurs actifs sans numéro de téléphone renseigné dans leur profil', 'job', 'App\Jobs\SendMissingPhoneReminders'],
            'new_mentors_digest' => ['Récapitulatif des nouveaux mentors inscrits', 'emails', 'Compile et transmet chaque semaine une sélection inspirante des derniers mentors ayant rejoint Brillio dans les domaines d\'intérêt du jeune.', 'Tous les vendredis à 16:00', 'Jeunes actifs inscrits ayant confirmé leur adresse e-mail', 'job', 'App\Jobs\SendNewMentorsDigest'],
            'unread_messages_reminders' => ['Rappel des messages non lus dans le chat', 'emails', 'Alerte les utilisateurs par email lorsqu\'un message leur a été adressé dans la messagerie de mentorat sans avoir été consulté depuis plus d\'une heure.', 'Toutes les 4 heures', 'Mentors ou jeunes ayant des messages en attente de lecture', 'command', 'messages:send-unread-reminders'],
            'recurring_campaigns' => ['Traitement des campagnes d\'e-mails programmées', 'emails', 'Prend en charge l\'envoi progressif et par lots des newsletters et campagnes de communication créées par les administrateurs.', self::FREQ_HOURLY, 'Campagnes e-mails en cours avec abonnés à délivrer', 'command', 'campaigns:process-recurring'],
            'process_bounces' => ['Surveillance & Exclusion des rejets d\'e-mails (Bounces)', 'emails', 'Détecte les erreurs SMTP (comptes inexistants, boîtes saturées, 550, 452) et exclut immédiatement les adresses en échec pour protéger Namecheap.', self::FREQ_HOURLY, 'Boîtes mails rejetées ou domaines invalides sans enregistrement MX', 'command', 'emails:process-bounces'],
            'archive_unverified' => ['Nettoyage des inscriptions non confirmées', 'security', 'Archive automatiquement les comptes créés depuis plus de 7 jours qui n\'ont jamais cliqué sur le lien de confirmation d\'e-mail.', 'Tous les jours à 02:00', 'Inscriptions incomplètes de plus de 7 jours sans adresse e-mail vérifiée', 'command', 'app:archive-unverified'],
            'personality_sync' => ['Synchronisation du test de personnalité', 'system', 'Met à jour les coefficients de pondération et la grille d\'évaluation des métiers dans le test d\'orientation et de personnalité.', 'Tous les trimestres à 02:00', 'Questions, traits RIASEC et coefficients de scoring du test', 'command', 'personality:sync-questions'],
            'organizations_downgrade' => ['Expiration des abonnements entreprises/écoles', 'organizations', 'Vérifie les dates de validité des abonnements souscrits par les organisations partenaires et bascule en compte gratuit les contrats échus.', 'Tous les jours à 01:00', 'Organisations dont la date d\'expiration d\'abonnement est dépassée', 'command', 'organizations:downgrade-expired'],
            'enterprise_credits' => ['Recharge mensuelle des crédits entreprises', 'organizations', 'Attribue le quota contractuel de crédits offerts aux organisations sous licence Entreprise active le premier jour de chaque mois.', 'Le 1er de chaque mois à 00:01', 'Organisations disposant d\'un forfait Entreprise valide', 'command', 'organizations:grant-enterprise-credits'],
        ];

        $keys = ['name', 'category', 'description', 'frequency_label', 'target', 'action_type', 'action_class'];
        $formatted = [];

        foreach ($raw as $identifier => $values) {
            $formatted[$identifier] = array_combine($keys, $values);
        }

        return $formatted;
    }

    /**
     * Synchronise le dictionnaire dans la base de données.
     */
    public static function syncAutomations(): Collection
    {
        if (! Schema::hasTable('cron_automations')) {
            return collect(self::definedAutomations())->map(function ($def, $key) {
                return (object) array_merge($def, [
                    'id' => 0,
                    'key' => $key,
                    'is_enabled' => true,
                    'last_run_at' => null,
                    'last_run_status' => null,
                    'last_run_duration' => null,
                    'last_run_output' => null,
                    'total_runs' => 0,
                    'total_failures' => 0,
                ]);
            });
        }

        $defined = self::definedAutomations();

        foreach ($defined as $key => $data) {
            self::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'frequency_label' => $data['frequency_label'],
                    'target' => $data['target'],
                    'action_type' => $data['action_type'],
                    'action_class' => $data['action_class'],
                ]
            );
        }

        return self::orderBy('category')->orderBy('name')->get();
    }

    /**
     * Vérifie si une automatisation est active et autorisée à s'exécuter.
     */
    public static function isEnabled(string $key): bool
    {
        if (! Schema::hasTable('cron_automations')) {
            return true;
        }

        $record = self::where('key', $key)->first();

        return $record ? (bool) $record->is_enabled : true;
    }

    /**
     * Bascule l'état actif/inactif d'une tâche.
     */
    public static function toggle(string $key): ?self
    {
        self::syncAutomations();

        $record = self::where('key', $key)->first();
        if (! $record) {
            return null;
        }

        $record->is_enabled = ! $record->is_enabled;
        $record->save();

        return $record;
    }

    /**
     * Exécute manuellement une automatisation à la demande de l'administrateur.
     */
    public static function runNow(string $key): array
    {
        self::syncAutomations();

        $record = self::where('key', $key)->first();
        if (! $record) {
            return [
                'success' => false,
                'message' => "Automatisation introuvable : {$key}",
                'output' => '',
                'duration' => 0,
            ];
        }

        $startTime = microtime(true);
        $output = '';
        $success = true;

        try {
            if ($record->action_type === 'command') {
                Artisan::call($record->action_class);
                $output = trim(Artisan::output());
            } elseif ($record->action_type === 'job') {
                $jobClass = $record->action_class;
                if (class_exists($jobClass)) {
                    dispatch_sync(new $jobClass);
                    $output = "Le job {$record->name} a été exécuté avec succès.";
                } else {
                    throw new CronAutomationException("La classe Job {$jobClass} n'existe pas.");
                }
            }
        } catch (\Throwable $e) {
            $success = false;
            $output = $e->getMessage();
            Log::error("Erreur lors de l'exécution manuelle du CRON {$key}: ".$e->getMessage());
        }

        $duration = round(microtime(true) - $startTime, 2);

        $record->update([
            'last_run_at' => now(),
            'last_run_status' => $success ? 'success' : 'failed',
            'last_run_duration' => $duration,
            'last_run_output' => mb_strimwidth($output, 0, 5000, '...'),
            'total_runs' => $record->total_runs + 1,
            'total_failures' => $success ? $record->total_failures : $record->total_failures + 1,
        ]);

        return [
            'success' => $success,
            'message' => $success ? "L'automatisation {$record->name} s'est exécutée avec succès." : "Échec de l'automatisation {$record->name} : {$output}",
            'output' => $output,
            'duration' => $duration,
        ];
    }
}
