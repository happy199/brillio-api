<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Modèle AcademicDocument - Documents scolaires uploadés
 */
class AcademicDocument extends Model
{
    use HasFactory;

    /**
     * Types de documents acceptés
     */
    public const TYPE_BULLETIN = 'bulletin';

    public const TYPE_RELEVE_NOTES = 'releve_notes';

    public const TYPE_DIPLOME = 'diplome';

    public const TYPE_ATTESTATION = 'attestation';

    public const TYPE_AUTRE = 'autre';

    public const DOCUMENT_TYPES = [
        self::TYPE_BULLETIN => 'Bulletin scolaire',
        self::TYPE_RELEVE_NOTES => 'Relevé de notes',
        self::TYPE_DIPLOME => 'Diplôme',
        self::TYPE_ATTESTATION => 'Attestation',
        self::TYPE_AUTRE => 'Autre document',
    ];

    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'academic_year',
        'grade_level',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retourne le label du type de document
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? 'Document';
    }

    /**
     * Retourne la taille formatée en Ko/Mo
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' Mo';
        }

        return number_format($bytes / 1024, 2).' Ko';
    }

    /**
     * Retourne l'URL de téléchargement sécurisée
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('api.academic.download', $this->id);
    }

    /**
     * Supprime le fichier physique lors de la suppression du modèle
     */
    protected static function booted(): void
    {
        static::deleting(function (AcademicDocument $document) {
            Storage::delete($document->file_path);
        });
    }
}
