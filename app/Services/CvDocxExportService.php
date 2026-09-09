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
                1 => $this->buildTemplate1($phpWord, $section, $analysis, $norm, $labels),
                2 => $this->buildTemplate2($phpWord, $section, $analysis, $norm, $labels),
                3 => $this->buildTemplate3($phpWord, $section, $analysis, $norm, $labels),
                4 => $this->buildTemplate4($phpWord, $section, $analysis, $norm, $labels),
                5 => $this->buildTemplate5($phpWord, $section, $analysis, $norm, $labels),
                default => $this->buildTemplate0($phpWord, $section, $analysis, $norm, $labels),
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
    private function buildTemplate0(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        // En-tête sobre aligné à gauche
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => '111827'],
            ['spaceAfter' => 20]
        );

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => '4B5563'],
                ['spaceAfter' => 30]
            );
        }

        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $section->addText(
                implode(' | ', $contactParts),
                ['name' => 'Calibri', 'size' => 9.5, 'color' => '6B7280'],
                ['spaceAfter' => 120, 'borderBottomSize' => 6, 'borderBottomColor' => 'D1D5DB']
            );
        }

        $this->addSectionHeadingBasic($section, $labels['profile'] ?? 'RÉSUMÉ PROFESSIONNEL', 'Calibri', '111827', 'D1D5DB');
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Calibri');

        if (! empty($norm['experiences'])) {
            $this->addSectionHeadingBasic($section, $labels['experience'] ?? 'EXPÉRIENCES PROFESSIONNELLES', 'Calibri', '111827', 'D1D5DB');
            $this->addExperiencesBasic($section, $norm['experiences'], $labels['recently'] ?? 'Poste actuel', 'Calibri');
        }

        if (! empty($norm['education'])) {
            $this->addSectionHeadingBasic($section, $labels['education'] ?? 'FORMATIONS & DIPLÔMES', 'Calibri', '111827', 'D1D5DB');
            $this->addEducationBasic($section, $norm['education'], 'Calibri');
        }

        if (! empty($norm['skills'])) {
            $this->addSectionHeadingBasic($section, $labels['skills'] ?? 'COMPÉTENCES CLÉS', 'Calibri', '111827', 'D1D5DB');
            $section->addText(
                implode(' • ', $norm['skills']),
                ['name' => 'Calibri', 'size' => 9.5, 'color' => '374151'],
                ['spaceAfter' => 80]
            );
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Calibri', '111827', 'D1D5DB');
    }

    /**
     * TEMPLATE 1 : Standard Classique (Centré, accents Marine)
     */
    private function buildTemplate1(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Arial', 'size' => 22, 'bold' => true, 'color' => '111827'],
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
                implode(' • ', $contactParts),
                ['name' => 'Arial', 'size' => 9.5, 'color' => '4B5563'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 140, 'borderBottomSize' => 14, 'borderBottomColor' => '1E3A8A']
            );
        }

        $this->addSectionHeadingBasic($section, $labels['profile'] ?? 'RÉSUMÉ PROFESSIONNEL', 'Arial', '111827', '93C5FD');
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Arial');

        if (! empty($norm['experiences'])) {
            $this->addSectionHeadingBasic($section, $labels['experience'] ?? 'EXPÉRIENCES PROFESSIONNELLES', 'Arial', '111827', '93C5FD');
            $this->addExperiencesWithCompanyColor($section, $norm['experiences'], $labels['recently'] ?? 'Poste actuel', 'Arial', '1E3A8A');
        }

        if (! empty($norm['education'])) {
            $this->addSectionHeadingBasic($section, $labels['education'] ?? 'FORMATIONS & DIPLÔMES', 'Arial', '111827', '93C5FD');
            $this->addEducationBasic($section, $norm['education'], 'Arial');
        }

        if (! empty($norm['skills'])) {
            $this->addSectionHeadingBasic($section, $labels['skills'] ?? 'COMPÉTENCES CLÉS', 'Arial', '111827', '93C5FD');
            $this->addPillSkills($section, $norm['skills'], 'Arial', 'F3F4F6', '1F2937');
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Arial', '111827', '93C5FD');
    }

    /**
     * TEMPLATE 2 : Standard Minimal (Bordures d'accent Indigo)
     */
    private function buildTemplate2(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        // En-tête avec nom gauche et contact droite
        $run = $section->addTextRun([
            'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
            'spaceAfter' => 20,
        ]);
        $run->addText(mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'), ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => '111827']);
        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $run->addText("\t".implode(' • ', array_slice($contactParts, 0, 2)), ['name' => 'Calibri', 'size' => 9, 'color' => '6B7280']);
        }

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => '4F46E5'],
                ['spaceAfter' => 120, 'borderBottomSize' => 6, 'borderBottomColor' => 'E5E7EB']
            );
        }

        $this->addSectionHeadingLeftBorder($section, $labels['profile'] ?? 'RÉSUMÉ PROFESSIONNEL', 'Calibri', '4338CA', '4F46E5');
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Calibri');

        if (! empty($norm['experiences'])) {
            $this->addSectionHeadingLeftBorder($section, $labels['experience'] ?? 'EXPÉRIENCES PROFESSIONNELLES', 'Calibri', '4338CA', '4F46E5');
            $this->addExperiencesWithBulletIcon($section, $norm['experiences'], $labels['recently'] ?? 'Poste actuel', 'Calibri', '›  ', '4F46E5', '4F46E5');
        }

        if (! empty($norm['education'])) {
            $this->addSectionHeadingLeftBorder($section, $labels['education'] ?? 'FORMATIONS & DIPLÔMES', 'Calibri', '4338CA', '4F46E5');
            $this->addEducationBasic($section, $norm['education'], 'Calibri');
        }

        if (! empty($norm['skills'])) {
            $this->addSectionHeadingLeftBorder($section, $labels['skills'] ?? 'COMPÉTENCES CLÉS', 'Calibri', '4338CA', '4F46E5');
            $this->addPillSkills($section, $norm['skills'], 'Calibri', 'EEF2FF', '312E81');
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Calibri', '4338CA', 'E5E7EB');
    }

    /**
     * TEMPLATE 3 : Professionnel Élite (2 Colonnes, Avatar Initiales, Best-Seller)
     */
    private function buildTemplate3(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        // En-tête : Bloc coordonnées à gauche, Initiales avatar à droite
        $phpWord->addTableStyle('EliteHeaderTable', [
            'borderSize' => 0,
            'cellMarginTop' => 0,
            'cellMarginBottom' => 60,
        ]);
        $headerTable = $section->addTable('EliteHeaderTable');
        $headerTable->addRow();

        $leftCell = $headerTable->addCell(Converter::inchToTwip(5.6));
        $leftCell->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Calibri', 'size' => 20, 'bold' => true, 'color' => '111827'],
            ['spaceAfter' => 20]
        );
        if ($analysis->candidate_title) {
            $leftCell->addText(
                $analysis->candidate_title,
                ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => '2563EB'],
                ['spaceAfter' => 30]
            );
        }
        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $leftCell->addText(
                implode('  |  ', $contactParts),
                ['name' => 'Calibri', 'size' => 9, 'color' => '4B5563'],
                ['spaceAfter' => 40]
            );
        }

        $rightCell = $headerTable->addCell(Converter::inchToTwip(1.2), ['bgColor' => '2563EB']);
        $rightCell->addText(
            $analysis->initials ?: 'CV',
            ['name' => 'Calibri', 'size' => 18, 'bold' => true, 'color' => 'FFFFFF'],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 100, 'spaceAfter' => 100]
        );

        $section->addText('', [], ['spaceAfter' => 40, 'borderBottomSize' => 8, 'borderBottomColor' => 'E5E7EB']);

        // Structure 2 colonnes
        $phpWord->addTableStyle('EliteTwoColTable', [
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);
        $bodyTable = $section->addTable('EliteTwoColTable');
        $bodyTable->addRow();

        // Colonne gauche (~60% / 4.1 in)
        $colLeft = $bodyTable->addCell(Converter::inchToTwip(4.1));
        $this->addSectionHeadingBasicToContainer($colLeft, $labels['profile'] ?? 'RÉSUMÉ PROFESSIONNEL', 'Calibri', '111827', '111827');
        $colLeft->addText($norm['profile_summary'] ?? '', ['name' => 'Calibri', 'size' => 9.5, 'color' => '374151'], ['spaceAfter' => 60, 'alignment' => Jc::BOTH]);

        if (! empty($norm['experiences'])) {
            $this->addSectionHeadingBasicToContainer($colLeft, $labels['experience'] ?? 'EXPÉRIENCES PROFESSIONNELLES', 'Calibri', '111827', '111827');
            foreach ($norm['experiences'] as $exp) {
                $colLeft->addText($exp['title'] ?? 'Poste', ['name' => 'Calibri', 'bold' => true, 'size' => 9.5, 'color' => '111827'], ['spaceAfter' => 10]);
                if (! empty($exp['company'])) {
                    $colLeft->addText($exp['company'].' — '.($exp['period'] ?: ($labels['recently'] ?? 'Poste actuel')), ['name' => 'Calibri', 'bold' => true, 'size' => 9, 'color' => '2563EB'], ['spaceAfter' => 20]);
                }
                foreach ($exp['bullets'] ?? [] as $b) {
                    $runB = $colLeft->addTextRun(['spaceAfter' => 15]);
                    $runB->addText('•  ', ['name' => 'Calibri', 'bold' => true, 'size' => 9, 'color' => '2563EB']);
                    $runB->addText((string) $b, ['name' => 'Calibri', 'size' => 9, 'color' => '374151']);
                }
                $colLeft->addText('', [], ['spaceAfter' => 30]);
            }
        }

        if (! empty($norm['education'])) {
            $this->addSectionHeadingBasicToContainer($colLeft, $labels['education'] ?? 'FORMATIONS & DIPLÔMES', 'Calibri', '111827', '111827');
            foreach ($norm['education'] as $edu) {
                $colLeft->addText($edu['degree'] ?? 'Diplôme', ['name' => 'Calibri', 'bold' => true, 'size' => 9.5, 'color' => '111827'], ['spaceAfter' => 10]);
                $colLeft->addText(($edu['school'] ?? '').' — '.($edu['year'] ?? ''), ['name' => 'Calibri', 'size' => 9, 'color' => '2563EB'], ['spaceAfter' => 25]);
            }
        }

        // Colonne droite (~40% / 2.7 in)
        $colRight = $bodyTable->addCell(Converter::inchToTwip(2.7));
        if (! empty($norm['skills'])) {
            $this->addSectionHeadingBasicToContainer($colRight, $labels['skills_simple'] ?? 'COMPÉTENCES', 'Calibri', '111827', '111827');
            $this->addPillSkillsToContainer($colRight, $norm['skills'], 'Calibri', 'F3F4F6', '1F2937');
        }

        if (! empty($norm['certifications'])) {
            $this->addSectionHeadingBasicToContainer($colRight, $labels['certifications'] ?? 'CERTIFICATIONS', 'Calibri', '111827', '111827');
            foreach ($norm['certifications'] as $cert) {
                $colRight->addText('📜 '.(is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : $cert), ['name' => 'Calibri', 'size' => 9, 'color' => '1F2937'], ['spaceAfter' => 20]);
            }
        }

        if (! empty($norm['languages'])) {
            $this->addSectionHeadingBasicToContainer($colRight, $labels['languages'] ?? 'LANGUES', 'Calibri', '111827', '111827');
            foreach ($norm['languages'] as $lang) {
                $langName = is_array($lang) ? ($lang['language'] ?? implode(', ', $lang)) : $lang;
                $colRight->addText($langName.'  ●●●●○', ['name' => 'Calibri', 'size' => 9, 'color' => '2563EB'], ['spaceAfter' => 20]);
            }
        }
    }

    /**
     * TEMPLATE 4 : EXPERT MODERNE (Bandeau Sombre, Badges, Validations Émeraude)
     * Reproduit fidèlement la maquette haut de gamme affichée à l'écran.
     */
    private function buildTemplate4(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        // 1. Bandeau Sombre Haut de Page
        $phpWord->addTableStyle('ModernDarkBanner', [
            'borderColor' => '111827',
            'borderSize' => 6,
            'cellMarginTop' => Converter::inchToTwip(0.12),
            'cellMarginBottom' => Converter::inchToTwip(0.12),
            'cellMarginLeft' => Converter::inchToTwip(0.15),
            'cellMarginRight' => Converter::inchToTwip(0.15),
        ]);
        $banner = $section->addTable('ModernDarkBanner');
        $banner->addRow();

        $cLeft = $banner->addCell(Converter::inchToTwip(4.6), ['bgColor' => '111827']);
        $cLeft->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => 'FFFFFF'],
            ['spaceAfter' => 20]
        );
        if ($analysis->candidate_title) {
            $cLeft->addText(
                $analysis->candidate_title,
                ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '34D399'],
                ['spaceAfter' => 0]
            );
        }

        $cRight = $banner->addCell(Converter::inchToTwip(2.2), ['bgColor' => '111827']);
        $contact = $analysis->candidate_contact ?? [];
        if (! empty($contact['email'])) {
            $cRight->addText($contact['email'], ['name' => 'Arial', 'size' => 9, 'color' => 'E5E7EB'], ['spaceAfter' => 15, 'alignment' => Jc::END]);
        }
        if (! empty($contact['phone'])) {
            $cRight->addText($contact['phone'], ['name' => 'Arial', 'size' => 9, 'color' => 'E5E7EB'], ['spaceAfter' => 15, 'alignment' => Jc::END]);
        }
        if (! empty($contact['location'])) {
            $cRight->addText($contact['location'], ['name' => 'Arial', 'size' => 9, 'color' => 'E5E7EB'], ['spaceAfter' => 0, 'alignment' => Jc::END]);
        }

        $section->addText('', [], ['spaceAfter' => 80]);

        // 2. Synthèse Exécutive
        $this->addSectionHeadingModern($section, $labels['executive_summary'] ?? 'SYNTHÈSE EXÉCUTIVE');
        $section->addText(
            $norm['profile_summary'] ?? '',
            ['name' => 'Arial', 'size' => 9.5, 'color' => '374151'],
            ['spaceAfter' => 60, 'alignment' => Jc::BOTH]
        );

        // 3. Réalisations & Postes Occupés
        if (! empty($norm['experiences'])) {
            $this->addSectionHeadingModern($section, $labels['achievements'] ?? 'RÉALISATIONS & POSTES OCCUPÉS');
            foreach ($norm['experiences'] as $exp) {
                $run = $section->addTextRun([
                    'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                    'spaceBefore' => 40,
                    'spaceAfter' => 20,
                ]);
                $run->addText($exp['title'] ?? 'Poste', ['name' => 'Arial', 'bold' => true, 'size' => 10, 'color' => '111827']);
                if (! empty($exp['company'])) {
                    $run->addText(' — ', ['name' => 'Arial', 'size' => 10, 'color' => '6B7280']);
                    $run->addText($exp['company'], ['name' => 'Arial', 'bold' => true, 'size' => 10, 'color' => '047857']);
                }
                $run->addText("\t".($exp['period'] ?: ($labels['recently'] ?? 'Poste actuel')), ['name' => 'Arial', 'size' => 9, 'color' => '6B7280']);

                foreach ($exp['bullets'] ?? [] as $b) {
                    $bRun = $section->addTextRun(['spaceAfter' => 25]);
                    $bRun->addText('✔  ', ['name' => 'Arial', 'bold' => true, 'size' => 9.5, 'color' => '10B981']);
                    $bRun->addText((string) $b, ['name' => 'Arial', 'size' => 9.5, 'color' => '374151']);
                }
                $section->addText('', [], ['spaceAfter' => 30]);
            }
        }

        // 4. Formations & Diplômes
        if (! empty($norm['education'])) {
            $this->addSectionHeadingModern($section, $labels['education'] ?? 'FORMATIONS & DIPLÔMES');
            foreach ($norm['education'] as $edu) {
                $run = $section->addTextRun([
                    'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                    'spaceBefore' => 30,
                    'spaceAfter' => 20,
                ]);
                $run->addText($edu['degree'] ?? 'Diplôme', ['name' => 'Arial', 'bold' => true, 'size' => 9.5, 'color' => '111827']);
                if (! empty($edu['school'])) {
                    $run->addText(' — ', ['name' => 'Arial', 'size' => 9.5, 'color' => '6B7280']);
                    $run->addText($edu['school'], ['name' => 'Arial', 'bold' => true, 'size' => 9.5, 'color' => '047857']);
                }
                $run->addText("\t".($edu['year'] ?? ''), ['name' => 'Arial', 'size' => 9, 'color' => '6B7280']);
            }
        }

        // 5. Compétences clés & Outils (en pilules sous formations comme dans la maquette)
        if (! empty($norm['skills'])) {
            $this->addSectionHeadingModern($section, $labels['skills'] ?? 'COMPÉTENCES CLÉS & OUTILS');
            $this->addPillSkills($section, $norm['skills'], 'Arial', 'F3F4F6', '1F2937');
        }

        // 6. Certifications & Langues si présentes
        if (! empty($norm['certifications'])) {
            $this->addSectionHeadingModern($section, $labels['certifications'] ?? 'CERTIFICATIONS');
            foreach ($norm['certifications'] as $cert) {
                $cRun = $section->addTextRun(['spaceAfter' => 20]);
                $cRun->addText('✔  ', ['name' => 'Arial', 'bold' => true, 'size' => 9.5, 'color' => '10B981']);
                $cRun->addText(is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : (string) $cert, ['name' => 'Arial', 'size' => 9.5, 'color' => '374151']);
            }
        }

        if (! empty($norm['languages'])) {
            $this->addSectionHeadingModern($section, $labels['languages'] ?? 'LANGUES');
            $langNames = array_map(fn ($l) => is_array($l) ? ($l['language'] ?? implode(', ', $l)) : (string) $l, $norm['languages']);
            $section->addText(implode('   •   ', $langNames), ['name' => 'Arial', 'size' => 9.5, 'color' => '374151'], ['spaceAfter' => 40]);
        }
    }

    /**
     * TEMPLATE 5 : Avancé Cadre & International (Georgia, Style Exécutif)
     */
    private function buildTemplate5(PhpWord $phpWord, Section $section, CvAnalysis $analysis, array $norm, array $labels): void
    {
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            ['name' => 'Georgia', 'size' => 22, 'bold' => true, 'color' => '111827'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 20]
        );

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                ['name' => 'Georgia', 'size' => 11, 'italic' => true, 'color' => '374151'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 30]
            );
        }

        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $section->addText(
                implode('  •  ', $contactParts),
                ['name' => 'Georgia', 'size' => 9.5, 'color' => '4B5563'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 140, 'borderBottomSize' => 18, 'borderBottomStyle' => 'double', 'borderBottomColor' => '111827']
            );
        }

        $this->addSectionHeadingBasic($section, $labels['leadership'] ?? 'PROFIL DE LEADERSHIP', 'Georgia', '111827', '9CA3AF');
        $this->addSummaryParagraph($section, $norm['profile_summary'] ?? '', 'Georgia');

        if (! empty($norm['experiences'])) {
            $this->addSectionHeadingBasic($section, $labels['experience'] ?? 'EXPÉRIENCES PROFESSIONNELLES', 'Georgia', '111827', '9CA3AF');
            $this->addExperiencesWithBulletIcon($section, $norm['experiences'], $labels['recently'] ?? 'Poste actuel', 'Georgia', '—  ', '6B7280', '111827');
        }

        if (! empty($norm['education'])) {
            $this->addSectionHeadingBasic($section, $labels['education'] ?? 'FORMATIONS & DIPLÔMES', 'Georgia', '111827', '9CA3AF');
            $this->addEducationBasic($section, $norm['education'], 'Georgia');
        }

        if (! empty($norm['skills'])) {
            $this->addSectionHeadingBasic($section, $labels['skills'] ?? 'COMPÉTENCES CLÉS', 'Georgia', '111827', '9CA3AF');
            $section->addText(
                implode('   •   ', $norm['skills']),
                ['name' => 'Georgia', 'size' => 9.5, 'color' => '374151'],
                ['spaceAfter' => 80]
            );
        }

        $this->addCertificationsAndLanguages($section, $norm, $labels, 'Georgia', '111827', '9CA3AF');
    }

    // =========================================================================
    // HELPERS DE STYLISATION
    // =========================================================================

    private function addSectionHeadingModern(Section $section, string $title): void
    {
        $section->addText(
            mb_strtoupper($title),
            ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '111827'],
            [
                'spaceBefore' => 140,
                'spaceAfter' => 60,
                'borderBottomSize' => 14,
                'borderBottomColor' => '10B981',
            ]
        );
    }

    private function addSectionHeadingBasic(Section $section, string $title, string $font, string $textColor, string $borderColor): void
    {
        $section->addText(
            mb_strtoupper($title),
            ['name' => $font, 'size' => 10, 'bold' => true, 'color' => $textColor],
            [
                'spaceBefore' => 140,
                'spaceAfter' => 50,
                'borderBottomSize' => 8,
                'borderBottomColor' => $borderColor,
            ]
        );
    }

    private function addSectionHeadingBasicToContainer($container, string $title, string $font, string $textColor, string $borderColor): void
    {
        $container->addText(
            mb_strtoupper($title),
            ['name' => $font, 'size' => 9.5, 'bold' => true, 'color' => $textColor],
            [
                'spaceBefore' => 120,
                'spaceAfter' => 40,
                'borderBottomSize' => 8,
                'borderBottomColor' => $borderColor,
            ]
        );
    }

    private function addSectionHeadingLeftBorder(Section $section, string $title, string $font, string $textColor, string $borderColor): void
    {
        $section->addText(
            mb_strtoupper($title),
            ['name' => $font, 'size' => 10, 'bold' => true, 'color' => $textColor],
            [
                'spaceBefore' => 140,
                'spaceAfter' => 50,
                'borderLeftSize' => 24,
                'borderLeftColor' => $borderColor,
            ]
        );
    }

    private function addSummaryParagraph(Section $section, string $summary, string $font): void
    {
        if (trim($summary) === '') {
            return;
        }

        $section->addText(
            $summary,
            ['name' => $font, 'size' => 9.5, 'color' => '374151'],
            ['spaceAfter' => 60, 'alignment' => Jc::BOTH]
        );
    }

    private function addExperiencesBasic(Section $section, array $experiences, string $recentlyLabel, string $font): void
    {
        foreach ($experiences as $exp) {
            $run = $section->addTextRun([
                'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                'spaceBefore' => 30,
                'spaceAfter' => 15,
            ]);
            $title = $exp['title'] ?? 'Poste';
            $company = $exp['company'] ?? '';
            $run->addText($company ? $title.' — '.$company : $title, ['name' => $font, 'bold' => true, 'size' => 10, 'color' => '111827']);
            $run->addText("\t".($exp['period'] ?: $recentlyLabel), ['name' => $font, 'size' => 9, 'color' => '6B7280']);

            foreach ($exp['bullets'] ?? [] as $bullet) {
                if (trim((string) $bullet) !== '') {
                    $bRun = $section->addTextRun(['spaceAfter' => 20]);
                    $bRun->addText('•  ', ['name' => $font, 'bold' => true, 'size' => 9, 'color' => '6B7280']);
                    $bRun->addText((string) $bullet, ['name' => $font, 'size' => 9.5, 'color' => '374151']);
                }
            }
            $section->addText('', [], ['spaceAfter' => 20]);
        }
    }

    private function addExperiencesWithCompanyColor(Section $section, array $experiences, string $recentlyLabel, string $font, string $companyColor): void
    {
        foreach ($experiences as $exp) {
            $run = $section->addTextRun([
                'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                'spaceBefore' => 30,
                'spaceAfter' => 15,
            ]);
            $run->addText($exp['title'] ?? 'Poste', ['name' => $font, 'bold' => true, 'size' => 10, 'color' => '111827']);
            if (! empty($exp['company'])) {
                $run->addText(' — ', ['name' => $font, 'size' => 10, 'color' => '6B7280']);
                $run->addText($exp['company'], ['name' => $font, 'bold' => true, 'size' => 10, 'color' => $companyColor]);
            }
            $run->addText("\t".($exp['period'] ?: $recentlyLabel), ['name' => $font, 'size' => 9, 'color' => '6B7280']);

            foreach ($exp['bullets'] ?? [] as $bullet) {
                if (trim((string) $bullet) !== '') {
                    $bRun = $section->addTextRun(['spaceAfter' => 20]);
                    $bRun->addText('•  ', ['name' => $font, 'bold' => true, 'size' => 9, 'color' => '6B7280']);
                    $bRun->addText((string) $bullet, ['name' => $font, 'size' => 9.5, 'color' => '374151']);
                }
            }
            $section->addText('', [], ['spaceAfter' => 20]);
        }
    }

    private function addExperiencesWithBulletIcon(Section $section, array $experiences, string $recentlyLabel, string $font, string $icon, string $iconColor, string $companyColor): void
    {
        foreach ($experiences as $exp) {
            $run = $section->addTextRun([
                'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                'spaceBefore' => 30,
                'spaceAfter' => 15,
            ]);
            $run->addText($exp['title'] ?? 'Poste', ['name' => $font, 'bold' => true, 'size' => 10, 'color' => '111827']);
            if (! empty($exp['company'])) {
                $run->addText(' — ', ['name' => $font, 'size' => 10, 'color' => '6B7280']);
                $run->addText($exp['company'], ['name' => $font, 'bold' => true, 'size' => 10, 'color' => $companyColor]);
            }
            $run->addText("\t".($exp['period'] ?: $recentlyLabel), ['name' => $font, 'size' => 9, 'color' => '6B7280']);

            foreach ($exp['bullets'] ?? [] as $bullet) {
                if (trim((string) $bullet) !== '') {
                    $bRun = $section->addTextRun(['spaceAfter' => 20]);
                    $bRun->addText($icon, ['name' => $font, 'bold' => true, 'size' => 9, 'color' => $iconColor]);
                    $bRun->addText((string) $bullet, ['name' => $font, 'size' => 9.5, 'color' => '374151']);
                }
            }
            $section->addText('', [], ['spaceAfter' => 20]);
        }
    }

    private function addEducationBasic(Section $section, array $education, string $font): void
    {
        foreach ($education as $edu) {
            $run = $section->addTextRun([
                'tabs' => [new Tab('right', Converter::inchToTwip(6.8))],
                'spaceBefore' => 20,
                'spaceAfter' => 15,
            ]);
            $degree = $edu['degree'] ?? 'Diplôme';
            $school = $edu['school'] ?? '';
            $run->addText($school ? $degree.' — '.$school : $degree, ['name' => $font, 'bold' => true, 'size' => 9.5, 'color' => '111827']);
            $run->addText("\t".($edu['year'] ?? ''), ['name' => $font, 'size' => 9, 'color' => '6B7280']);
        }
        $section->addText('', [], ['spaceAfter' => 20]);
    }

    private function addPillSkills(Section $section, array $skills, string $font, string $bgColor, string $textColor): void
    {
        $run = $section->addTextRun(['spaceAfter' => 60]);
        foreach ($skills as $sk) {
            $skillName = is_array($sk) ? ($sk['name'] ?? '') : (string) $sk;
            if (trim($skillName) !== '') {
                $run->addText(' '.$skillName.' ', [
                    'name' => $font,
                    'size' => 9,
                    'bold' => true,
                    'color' => $textColor,
                    'bgColor' => $bgColor,
                ]);
                $run->addText('   ');
            }
        }
    }

    private function addPillSkillsToContainer($container, array $skills, string $font, string $bgColor, string $textColor): void
    {
        $run = $container->addTextRun(['spaceAfter' => 40]);
        foreach ($skills as $sk) {
            $skillName = is_array($sk) ? ($sk['name'] ?? '') : (string) $sk;
            if (trim($skillName) !== '') {
                $run->addText(' '.$skillName.' ', [
                    'name' => $font,
                    'size' => 8.5,
                    'bold' => true,
                    'color' => $textColor,
                    'bgColor' => $bgColor,
                ]);
                $run->addText('  ');
            }
        }
    }

    private function addCertificationsAndLanguages(Section $section, array $norm, array $labels, string $font, string $headingColor, string $borderColor): void
    {
        if (! empty($norm['certifications'])) {
            $this->addSectionHeadingBasic($section, $labels['certifications'] ?? 'CERTIFICATIONS', $font, $headingColor, $borderColor);
            foreach ($norm['certifications'] as $cert) {
                $certText = is_array($cert) ? ($cert['name'] ?? implode(', ', $cert)) : (string) $cert;
                $section->addText('•  '.$certText, ['name' => $font, 'size' => 9.5, 'color' => '374151'], ['spaceAfter' => 15]);
            }
        }

        if (! empty($norm['languages'])) {
            $this->addSectionHeadingBasic($section, $labels['languages'] ?? 'LANGUES', $font, $headingColor, $borderColor);
            $langNames = array_map(fn ($l) => is_array($l) ? ($l['language'] ?? implode(', ', $l)) : (string) $l, $norm['languages']);
            $section->addText(implode('   •   ', $langNames), ['name' => $font, 'size' => 9.5, 'color' => '374151'], ['spaceAfter' => 30]);
        }
    }

    private function extractContactParts(CvAnalysis $analysis): array
    {
        $parts = [];
        $contact = $analysis->candidate_contact ?? [];

        if (! empty($contact['email'])) {
            $parts[] = $contact['email'];
        }
        if (! empty($contact['phone'])) {
            $parts[] = $contact['phone'];
        }
        if (! empty($contact['location'])) {
            $parts[] = $contact['location'];
        }
        if (! empty($contact['linkedin'])) {
            $parts[] = $contact['linkedin'];
        }
        if (! empty($contact['github'])) {
            $parts[] = $contact['github'];
        }

        return $parts;
    }
}
