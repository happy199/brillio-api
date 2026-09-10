<?php

namespace App\Services;

use App\Models\CvAnalysis;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Tab;

class CvDocxExportService
{
    private const COLOR_DARK = '111827';

    private const COLOR_TEXT_PRIMARY = '1F2937';

    private const COLOR_TEXT_BODY = '374151';

    private const COLOR_TEXT_MUTED = '6B7280';

    private const COLOR_TEXT_SECONDARY = '4B5563';

    private const COLOR_INDIGO = '4338CA';

    private const COLOR_INDIGO_ACCENT = '4F46E5';

    private const COLOR_BORDER_DEFAULT = 'D1D5DB';

    private const COLOR_BG_PILL = 'F3F4F6';

    private const SEPARATOR_BULLET_SPACED = ' • ';

    private const LABEL_DEFAULT_PROFILE = 'RÉSUMÉ PROFESSIONNEL';

    private const LABEL_DEFAULT_EXPERIENCE = 'EXPÉRIENCES PROFESSIONNELLES';

    private const LABEL_DEFAULT_EDUCATION = 'FORMATIONS & DIPLÔMES';

    private const LABEL_DEFAULT_SKILLS = 'COMPÉTENCES CLÉS';

    private const LABEL_DEFAULT_RECENTLY = 'Poste actuel';

    /**
     * Génère un fichier DOCX temporaire et retourne son chemin absolu.
     */
    public function generateDocx(CvAnalysis $analysis, int $template = 0): string
    {
        $prevErrorReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $phpWord = new PhpWord;
            $norm = $analysis->normalized_cv_data;
            $labels = $norm['labels'] ?? [];

            $section = $this->createStyledSection($phpWord, $template);

            match ($template) {
                1 => $this->buildTemplate1($section, $analysis, $norm, $labels),
                2 => $this->buildTemplate2($section, $analysis, $norm, $labels),
                3 => $this->buildTemplate3($phpWord, $section, $analysis, $norm, $labels),
                4 => $this->buildTemplate4($phpWord, $section, $analysis, $norm, $labels),
                5 => $this->buildTemplate5($section, $analysis, $norm, $labels),
                default => $this->buildTemplate0($section, $analysis, $norm, $labels),
            };

            $tempFile = tempnam(sys_get_temp_dir(), 'brillio_cv_').'.docx';
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($tempFile);

            return $tempFile;
        } finally {
            error_reporting($prevErrorReporting);
        }
    }

    /**
     * Retourne un nom de fichier propre pour le téléchargement.
     */
    public function generateFilename(CvAnalysis $analysis): string
    {
        $candidateName = $analysis->candidate_name ?: 'Candidat';
        $slug = Str::slug($candidateName, '_');

        return 'CV_'.$slug.'_ATS.docx';
    }

    /**
     * Initialise la section avec les marges adéquates selon le template.
     */
    private function createStyledSection(PhpWord $phpWord, int $template): Section
    {
        $font = match ($template) {
            1, 4 => 'Arial',
            5 => 'Georgia',
            default => 'Calibri',
        };

        $phpWord->setDefaultFontName($font);
        $phpWord->setDefaultFontSize(10);

        return $phpWord->addSection([
            'marginTop' => Converter::inchToTwip(0.6),
            'marginBottom' => Converter::inchToTwip(0.6),
            'marginLeft' => Converter::inchToTwip(0.7),
            'marginRight' => Converter::inchToTwip(0.7),
        ]);
    }

    /**
     * TEMPLATE 0 : Basic ATS (Standard monochrome épuré)
     */
    private function buildTemplate0(Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => self::COLOR_DARK],
            ['spaceAfter' => 20]
        );

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => self::COLOR_TEXT_SECONDARY],
                ['spaceAfter' => 30]
            );
        }

        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $section->addText(
                implode(' | ', $contactParts),
                ['name' => 'Calibri', 'size' => 9.5, 'color' => self::COLOR_TEXT_MUTED],
                ['spaceAfter' => 120, 'borderBottomSize' => 6, 'borderBottomColor' => self::COLOR_BORDER_DEFAULT]
            );
        }

        $this->addHeading($section, $labels['profile'] ?? self::LABEL_DEFAULT_PROFILE, 'Calibri', self::COLOR_DARK, self::COLOR_BORDER_DEFAULT);
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Calibri');

        if (! empty($norm['experiences'])) {
            $this->addHeading($section, $labels['experience'] ?? self::LABEL_DEFAULT_EXPERIENCE, 'Calibri', self::COLOR_DARK, self::COLOR_BORDER_DEFAULT);
            $this->addExperiencesList($section, $norm['experiences'], $labels['recently'] ?? self::LABEL_DEFAULT_RECENTLY, 'Calibri');
        }

        if (! empty($norm['education'])) {
            $this->addHeading($section, $labels['education'] ?? self::LABEL_DEFAULT_EDUCATION, 'Calibri', self::COLOR_DARK, self::COLOR_BORDER_DEFAULT);
            $this->addEducationList($section, $norm['education'], 'Calibri');
        }

        if (! empty($norm['skills'])) {
            $this->addHeading($section, $labels['skills'] ?? self::LABEL_DEFAULT_SKILLS, 'Calibri', self::COLOR_DARK, self::COLOR_BORDER_DEFAULT);
            $section->addText(
                implode(self::SEPARATOR_BULLET_SPACED, $norm['skills']),
                ['name' => 'Calibri', 'size' => 9.5, 'color' => self::COLOR_TEXT_BODY],
                ['spaceAfter' => 80]
            );
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Calibri', self::COLOR_DARK, self::COLOR_BORDER_DEFAULT);
    }

    /**
     * TEMPLATE 1 : Standard Classique (Centré, accents Marine)
     */
    private function buildTemplate1(Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Arial', 'size' => 22, 'bold' => true, 'color' => self::COLOR_DARK],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 20]
        );

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '1E3A8A'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 30]
            );
        }

        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $section->addText(
                implode(self::SEPARATOR_BULLET_SPACED, $contactParts),
                ['name' => 'Arial', 'size' => 9.5, 'color' => self::COLOR_TEXT_SECONDARY],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 140, 'borderBottomSize' => 14, 'borderBottomColor' => '1E3A8A']
            );
        }

        $this->addHeading($section, $labels['profile'] ?? self::LABEL_DEFAULT_PROFILE, 'Arial', self::COLOR_DARK, '93C5FD');
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Arial');

        if (! empty($norm['experiences'])) {
            $this->addHeading($section, $labels['experience'] ?? self::LABEL_DEFAULT_EXPERIENCE, 'Arial', self::COLOR_DARK, '93C5FD');
            $this->addExperiencesList($section, $norm['experiences'], $labels['recently'] ?? self::LABEL_DEFAULT_RECENTLY, 'Arial', '•  ', self::COLOR_TEXT_MUTED, '1E3A8A');
        }

        if (! empty($norm['education'])) {
            $this->addHeading($section, $labels['education'] ?? self::LABEL_DEFAULT_EDUCATION, 'Arial', self::COLOR_DARK, '93C5FD');
            $this->addEducationList($section, $norm['education'], 'Arial');
        }

        if (! empty($norm['skills'])) {
            $this->addHeading($section, $labels['skills'] ?? self::LABEL_DEFAULT_SKILLS, 'Arial', self::COLOR_DARK, '93C5FD');
            $this->addPills($section, $norm['skills'], 'Arial');
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Arial', self::COLOR_DARK, '93C5FD');
    }

    /**
     * TEMPLATE 2 : Standard Minimal (Bordures d'accent Indigo)
     */
    private function buildTemplate2(Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $run = $section->addTextRun([
            'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
            'spaceAfter' => 20,
        ]);
        $run->addText(mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'), ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => self::COLOR_DARK]);
        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $run->addText("\t".implode(self::SEPARATOR_BULLET_SPACED, array_slice($contactParts, 0, 2)), ['name' => 'Calibri', 'size' => 9, 'color' => self::COLOR_TEXT_MUTED]);
        }

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => self::COLOR_INDIGO_ACCENT],
                ['spaceAfter' => 120, 'borderBottomSize' => 6, 'borderBottomColor' => 'E5E7EB']
            );
        }

        $this->addHeading($section, $labels['profile'] ?? self::LABEL_DEFAULT_PROFILE, 'Calibri', self::COLOR_INDIGO, self::COLOR_INDIGO_ACCENT, 'left', 24);
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Calibri');

        if (! empty($norm['experiences'])) {
            $this->addHeading($section, $labels['experience'] ?? self::LABEL_DEFAULT_EXPERIENCE, 'Calibri', self::COLOR_INDIGO, self::COLOR_INDIGO_ACCENT, 'left', 24);
            $this->addExperiencesList($section, $norm['experiences'], $labels['recently'] ?? self::LABEL_DEFAULT_RECENTLY, 'Calibri', '›  ', self::COLOR_INDIGO_ACCENT, self::COLOR_INDIGO_ACCENT);
        }

        if (! empty($norm['education'])) {
            $this->addHeading($section, $labels['education'] ?? self::LABEL_DEFAULT_EDUCATION, 'Calibri', self::COLOR_INDIGO, self::COLOR_INDIGO_ACCENT, 'left', 24);
            $this->addEducationList($section, $norm['education'], 'Calibri');
        }

        if (! empty($norm['skills'])) {
            $this->addHeading($section, $labels['skills'] ?? self::LABEL_DEFAULT_SKILLS, 'Calibri', self::COLOR_INDIGO, self::COLOR_INDIGO_ACCENT, 'left', 24);
            $this->addPills($section, $norm['skills'], 'Calibri', 'EEF2FF', '312E81');
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Calibri', self::COLOR_INDIGO, 'E5E7EB');
    }

    /**
     * TEMPLATE 3 : Professionnel Élite (2 Colonnes, Avatar Initiales)
     */
    private function buildTemplate3(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $phpWord->addTableStyle('EliteHeaderTable', ['borderSize' => 0, 'cellMarginTop' => 0, 'cellMarginBottom' => 60]);
        $headerTable = $section->addTable('EliteHeaderTable');
        $headerTable->addRow();

        $leftCell = $headerTable->addCell(Converter::inchToTwip(5.6));
        $leftCell->addText(mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'), ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => self::COLOR_DARK], ['spaceAfter' => 20]);
        if ($analysis->candidate_title) {
            $leftCell->addText($analysis->candidate_title, ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => '2563EB'], ['spaceAfter' => 30]);
        }
        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $leftCell->addText(implode('  |  ', $contactParts), ['name' => 'Calibri', 'size' => 9, 'color' => self::COLOR_TEXT_SECONDARY], ['spaceAfter' => 40]);
        }

        $rightCell = $headerTable->addCell(Converter::inchToTwip(1.2), ['bgColor' => '2563EB']);
        $rightCell->addText($analysis->initials ?: 'CV', ['name' => 'Calibri', 'size' => 18, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER, 'spaceBefore' => 100, 'spaceAfter' => 100]);

        $section->addText('', [], ['spaceAfter' => 40, 'borderBottomSize' => 8, 'borderBottomColor' => 'E5E7EB']);

        $phpWord->addTableStyle('EliteTwoColTable', ['borderSize' => 0, 'cellMargin' => 0]);
        $bodyTable = $section->addTable('EliteTwoColTable');
        $bodyTable->addRow();

        $colLeft = $bodyTable->addCell(Converter::inchToTwip(4.1));
        $this->addHeading($colLeft, $labels['profile'] ?? self::LABEL_DEFAULT_PROFILE, 'Calibri', self::COLOR_DARK, self::COLOR_DARK);
        $colLeft->addText($norm['profile_summary'] ?? '', ['name' => 'Calibri', 'size' => 9.5, 'color' => self::COLOR_TEXT_BODY], ['spaceAfter' => 60, 'alignment' => Jc::BOTH]);

        if (! empty($norm['experiences'])) {
            $this->addHeading($colLeft, $labels['experience'] ?? self::LABEL_DEFAULT_EXPERIENCE, 'Calibri', self::COLOR_DARK, self::COLOR_DARK);
            $this->addExperiencesList($colLeft, $norm['experiences'], $labels['recently'] ?? self::LABEL_DEFAULT_RECENTLY, 'Calibri', '•  ', '2563EB', '2563EB');
        }

        if (! empty($norm['education'])) {
            $this->addHeading($colLeft, $labels['education'] ?? self::LABEL_DEFAULT_EDUCATION, 'Calibri', self::COLOR_DARK, self::COLOR_DARK);
            $this->addEducationList($colLeft, $norm['education'], 'Calibri', '2563EB');
        }

        $colRight = $bodyTable->addCell(Converter::inchToTwip(2.7));
        if (! empty($norm['skills'])) {
            $this->addHeading($colRight, $labels['skills_simple'] ?? 'COMPÉTENCES', 'Calibri', self::COLOR_DARK, self::COLOR_DARK);
            $this->addPills($colRight, $norm['skills'], 'Calibri', self::COLOR_BG_PILL, self::COLOR_TEXT_PRIMARY, 8.5);
        }

        if (! empty($norm['certifications'])) {
            $this->addHeading($colRight, $labels['certifications'] ?? 'CERTIFICATIONS', 'Calibri', self::COLOR_DARK, self::COLOR_DARK);
            foreach ($norm['certifications'] as $cert) {
                $colRight->addText('📜 '.(is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : $cert), ['name' => 'Calibri', 'size' => 9, 'color' => self::COLOR_TEXT_PRIMARY], ['spaceAfter' => 20]);
            }
        }

        if (! empty($norm['languages'])) {
            $this->addHeading($colRight, $labels['languages'] ?? 'LANGUES', 'Calibri', self::COLOR_DARK, self::COLOR_DARK);
            foreach ($norm['languages'] as $lang) {
                $langName = is_array($lang) ? ($lang['language'] ?? implode(', ', $lang)) : $lang;
                $colRight->addText($langName.'  ●●●●○', ['name' => 'Calibri', 'size' => 9, 'color' => '2563EB'], ['spaceAfter' => 20]);
            }
        }
    }

    /**
     * TEMPLATE 4 : EXPERT MODERNE (Bandeau Sombre, Badges, Validations Émeraude)
     */
    private function buildTemplate4(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $this->addModernBanner($phpWord, $section, $analysis);

        $this->addHeading($section, $labels['executive_summary'] ?? 'SYNTHÈSE EXÉCUTIVE', 'Arial', self::COLOR_DARK, '10B981', 'bottom', 14);
        $section->addText($norm['profile_summary'] ?? '', ['name' => 'Arial', 'size' => 9.5, 'color' => self::COLOR_TEXT_BODY], ['spaceAfter' => 60, 'alignment' => Jc::BOTH]);

        if (! empty($norm['experiences'])) {
            $this->addHeading($section, $labels['achievements'] ?? 'RÉALISATIONS & POSTES OCCUPÉS', 'Arial', self::COLOR_DARK, '10B981', 'bottom', 14);
            $this->addExperiencesList($section, $norm['experiences'], $labels['recently'] ?? self::LABEL_DEFAULT_RECENTLY, 'Arial', '✔  ', '10B981', '047857');
        }

        if (! empty($norm['education'])) {
            $this->addHeading($section, $labels['education'] ?? self::LABEL_DEFAULT_EDUCATION, 'Arial', self::COLOR_DARK, '10B981', 'bottom', 14);
            $this->addEducationList($section, $norm['education'], 'Arial', '047857');
        }

        if (! empty($norm['skills'])) {
            $this->addHeading($section, $labels['skills'] ?? 'COMPÉTENCES CLÉS & OUTILS', 'Arial', self::COLOR_DARK, '10B981', 'bottom', 14);
            $this->addPills($section, $norm['skills'], 'Arial');
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Arial', self::COLOR_DARK, '10B981');
    }

    /**
     * TEMPLATE 5 : Avancé Cadre & International (Georgia, Style Exécutif)
     */
    private function buildTemplate5(Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Georgia', 'size' => 22, 'bold' => true, 'color' => self::COLOR_DARK],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 20]
        );

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Georgia', 'size' => 11, 'italic' => true, 'color' => self::COLOR_TEXT_BODY],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 30]
            );
        }

        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $section->addText(
                implode('  •  ', $contactParts),
                ['name' => 'Georgia', 'size' => 9.5, 'color' => self::COLOR_TEXT_SECONDARY],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 140, 'borderBottomSize' => 18, 'borderBottomStyle' => 'double', 'borderBottomColor' => self::COLOR_DARK]
            );
        }

        $this->addHeading($section, $labels['leadership'] ?? 'PROFIL DE LEADERSHIP', 'Georgia', self::COLOR_DARK, '9CA3AF');
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Georgia');

        if (! empty($norm['experiences'])) {
            $this->addHeading($section, $labels['experience'] ?? self::LABEL_DEFAULT_EXPERIENCE, 'Georgia', self::COLOR_DARK, '9CA3AF');
            $this->addExperiencesList($section, $norm['experiences'], $labels['recently'] ?? self::LABEL_DEFAULT_RECENTLY, 'Georgia', '—  ', self::COLOR_TEXT_MUTED, self::COLOR_DARK);
        }

        if (! empty($norm['education'])) {
            $this->addHeading($section, $labels['education'] ?? self::LABEL_DEFAULT_EDUCATION, 'Georgia', self::COLOR_DARK, '9CA3AF');
            $this->addEducationList($section, $norm['education'], 'Georgia');
        }

        if (! empty($norm['skills'])) {
            $this->addHeading($section, $labels['skills'] ?? self::LABEL_DEFAULT_SKILLS, 'Georgia', self::COLOR_DARK, '9CA3AF');
            $section->addText(
                implode('   •   ', $norm['skills']),
                ['name' => 'Georgia', 'size' => 9.5, 'color' => self::COLOR_TEXT_BODY],
                ['spaceAfter' => 80]
            );
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Georgia', self::COLOR_DARK, '9CA3AF');
    }

    private function addModernBanner(PhpWord $phpWord, Section $section, CvAnalysis $analysis): void
    {
        $phpWord->addTableStyle('ModernDarkBanner', [
            'borderColor' => self::COLOR_DARK,
            'borderSize' => 6,
            'cellMarginTop' => Converter::inchToTwip(0.12),
            'cellMarginBottom' => Converter::inchToTwip(0.12),
            'cellMarginLeft' => Converter::inchToTwip(0.15),
            'cellMarginRight' => Converter::inchToTwip(0.15),
        ]);
        $banner = $section->addTable('ModernDarkBanner');
        $banner->addRow();

        $cLeft = $banner->addCell(Converter::inchToTwip(4.6), ['bgColor' => self::COLOR_DARK]);
        $cLeft->addText(mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'), ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => 'FFFFFF'], ['spaceAfter' => 20]);
        if ($analysis->candidate_title) {
            $cLeft->addText($analysis->candidate_title, ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '34D399'], ['spaceAfter' => 0]);
        }

        $cRight = $banner->addCell(Converter::inchToTwip(2.2), ['bgColor' => self::COLOR_DARK]);
        $contact = $analysis->candidate_contact ?? [];
        foreach (['email', 'phone', 'location'] as $field) {
            if (! empty($contact[$field])) {
                $cRight->addText($contact[$field], ['name' => 'Arial', 'size' => 9, 'color' => 'E5E7EB'], ['spaceAfter' => $field === 'location' ? 0 : 15, 'alignment' => Jc::END]);
            }
        }

        $section->addText('', [], ['spaceAfter' => 80]);
    }

    private function addHeading($container, string $title, string $font, string $textColor, string $borderColor, string $borderSide = 'bottom', int $borderSize = 8): void
    {
        $paragraphStyle = [
            'spaceBefore' => 140,
            'spaceAfter' => 50,
        ];

        if ($borderSide === 'left') {
            $paragraphStyle['borderLeftSize'] = $borderSize;
            $paragraphStyle['borderLeftColor'] = $borderColor;
        } else {
            $paragraphStyle['borderBottomSize'] = $borderSize;
            $paragraphStyle['borderBottomColor'] = $borderColor;
        }

        $container->addText(
            mb_strtoupper($title),
            ['name' => $font, 'size' => 10, 'bold' => true, 'color' => $textColor],
            $paragraphStyle
        );
    }

    private function addSummaryParagraph(Section $section, string $summary, string $font): void
    {
        if (trim($summary) === '') {
            return;
        }

        $run = $section->addTextRun(['spaceAfter' => 60, 'alignment' => Jc::BOTH]);
        $this->addFormattedTextWithPlaceholders($run, $summary, $font, self::COLOR_TEXT_BODY);
    }

    private function addExperiencesList($container, array $experiences, string $recentlyLabel, string $font, string $bulletIcon = '•  ', string $bulletColor = self::COLOR_TEXT_MUTED, ?string $companyColor = null): void
    {
        foreach ($experiences as $exp) {
            $run = $container->addTextRun([
                'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                'spaceBefore' => 30,
                'spaceAfter' => 15,
            ]);
            $title = $exp['title'] ?? 'Poste';
            $company = $exp['company'] ?? '';
            $run->addText($exp['title'] ?? 'Poste', ['name' => $font, 'bold' => true, 'size' => 10, 'color' => self::COLOR_DARK]);
            if (! empty($company)) {
                $run->addText(' — ', ['name' => $font, 'size' => 10, 'color' => self::COLOR_TEXT_MUTED]);
                $run->addText($company, ['name' => $font, 'bold' => true, 'size' => 10, 'color' => $companyColor ?: self::COLOR_DARK]);
            }
            $run->addText("\t".($exp['period'] ?: $recentlyLabel), ['name' => $font, 'size' => 9, 'color' => self::COLOR_TEXT_MUTED]);

            foreach ($exp['bullets'] ?? [] as $bullet) {
                if (trim((string) $bullet) !== '') {
                    $bRun = $container->addTextRun(['spaceAfter' => 20]);
                    $bRun->addText($bulletIcon, ['name' => $font, 'bold' => true, 'size' => 9, 'color' => $bulletColor]);
                    $this->addFormattedTextWithPlaceholders($bRun, (string) $bullet, $font, self::COLOR_TEXT_BODY);
                }
            }
            $container->addText('', [], ['spaceAfter' => 20]);
        }
    }

    private function addFormattedTextWithPlaceholders($container, string $text, string $font, string $defaultColor): void
    {
        $parts = preg_split('/(\[(?:À compléter|Compléter|Insérer|A completer|A renseigner)[^\]]*\]|\{[^\}]+\}|\[rempli:[^|\]]+\|guide:[^\]]+\])/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (! $parts) {
            $container->addText($text, ['name' => $font, 'size' => 9.5, 'color' => $defaultColor]);

            return;
        }

        foreach ($parts as $part) {
            if (preg_match('/^\[rempli:([^|\]]+)\|guide:[^\]]+\]$/u', $part, $matches)) {
                // Balise complétée par le candidat : rendue proprement sans crochets dans Word
                $container->addText($matches[1], [
                    'name' => $font,
                    'size' => 9.5,
                    'color' => self::COLOR_DARK,
                    'bold' => true,
                ]);
            } elseif (preg_match('/^(\[(?:À compléter|Compléter|Insérer|A completer|A renseigner)[^\]]*\]|\{[^\}]+\})$/iu', $part)) {
                $container->addText($part, [
                    'name' => $font,
                    'size' => 9.5,
                    'bold' => true,
                    'italic' => true,
                    'color' => 'B45309',
                ]);
            } else {
                $container->addText($part, [
                    'name' => $font,
                    'size' => 9.5,
                    'color' => $defaultColor,
                ]);
            }
        }
    }

    private function addEducationList($container, array $education, string $font, ?string $schoolColor = null): void
    {
        foreach ($education as $edu) {
            $run = $container->addTextRun([
                'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                'spaceBefore' => 20,
                'spaceAfter' => 15,
            ]);
            $degree = $edu['degree'] ?? 'Diplôme';
            $school = $edu['school'] ?? '';
            $run->addText($degree, ['name' => $font, 'bold' => true, 'size' => 9.5, 'color' => self::COLOR_DARK]);
            if (! empty($school)) {
                $run->addText(' — ', ['name' => $font, 'size' => 9.5, 'color' => self::COLOR_TEXT_MUTED]);
                $run->addText($school, ['name' => $font, 'bold' => true, 'size' => 9.5, 'color' => $schoolColor ?: self::COLOR_TEXT_MUTED]);
            }
            $run->addText("\t".($edu['year'] ?? ''), ['name' => $font, 'size' => 9, 'color' => self::COLOR_TEXT_MUTED]);
        }
        $container->addText('', [], ['spaceAfter' => 20]);
    }

    private function addPills($container, array $skills, string $font, string $bgColor = self::COLOR_BG_PILL, string $textColor = self::COLOR_TEXT_PRIMARY, float $fontSize = 9.0): void
    {
        $run = $container->addTextRun(['spaceAfter' => 50]);
        foreach ($skills as $sk) {
            $skillName = is_array($sk) ? ($sk['name'] ?? '') : (string) $sk;
            if (trim($skillName) !== '') {
                $run->addText(' '.$skillName.' ', [
                    'name' => $font,
                    'size' => $fontSize,
                    'bold' => true,
                    'color' => $textColor,
                    'bgColor' => $bgColor,
                ]);
                $run->addText('   ');
            }
        }
    }

    private function addCertificationsAndLanguages(Section $section, array $norm, array $labels, string $font, string $headingColor, string $borderColor): void
    {
        if (! empty($norm['certifications'])) {
            $this->addHeading($section, $labels['certifications'] ?? 'CERTIFICATIONS', $font, $headingColor, $borderColor);
            foreach ($norm['certifications'] as $cert) {
                $certText = is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : (string) $cert;
                $section->addText('•  '.$certText, ['name' => $font, 'size' => 9.5, 'color' => self::COLOR_TEXT_BODY], ['spaceAfter' => 15]);
            }
        }

        if (! empty($norm['languages'])) {
            $this->addHeading($section, $labels['languages'] ?? 'LANGUES', $font, $headingColor, $borderColor);
            $langNames = array_map(fn ($l) => is_array($l) ? ($l['language'] ?? implode(', ', $l)) : (string) $l, $norm['languages']);
            $section->addText(implode('   •   ', $langNames), ['name' => $font, 'size' => 9.5, 'color' => self::COLOR_TEXT_BODY], ['spaceAfter' => 30]);
        }
    }

    private function extractContactParts(CvAnalysis $analysis): array
    {
        $parts = [];
        $contact = $analysis->candidate_contact ?? [];

        foreach (['email', 'phone', 'location', 'linkedin', 'github'] as $field) {
            if (! empty($contact[$field])) {
                $parts[] = $contact[$field];
            }
        }

        return $parts;
    }
}
