<?php

namespace App\Services;

use App\Models\CvAnalysis;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class CvDocxExportService
{
    /**
     * Configuration visuelle selon le template sélectionné (0 à 5).
     */
    private const TEMPLATE_CONFIG = [
        0 => [
            'font' => 'Calibri',
            'primary_color' => '111827',
            'secondary_color' => '4B5563',
            'border_color' => 'D1D5DB',
            'name_size' => 20,
        ],
        1 => [
            'font' => 'Arial',
            'primary_color' => '1E3A8A',
            'secondary_color' => '3B82F6',
            'border_color' => '93C5FD',
            'name_size' => 21,
        ],
        2 => [
            'font' => 'Calibri',
            'primary_color' => '1F2937',
            'secondary_color' => '6B7280',
            'border_color' => 'E5E7EB',
            'name_size' => 19,
        ],
        3 => [
            'font' => 'Calibri',
            'primary_color' => '0369A1',
            'secondary_color' => '0284C7',
            'border_color' => 'BAE6FD',
            'name_size' => 21,
        ],
        4 => [
            'font' => 'Arial',
            'primary_color' => '047857',
            'secondary_color' => '059669',
            'border_color' => 'A7F3D0',
            'name_size' => 21,
        ],
        5 => [
            'font' => 'Georgia',
            'primary_color' => '4C1D95',
            'secondary_color' => '6D28D9',
            'border_color' => 'DDD6FE',
            'name_size' => 21,
        ],
    ];

    /**
     * Génère un fichier DOCX temporaire et retourne son chemin absolu.
     */
    public function generateDocx(CvAnalysis $analysis, int $template = 0): string
    {
        $prevErrorReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $config = self::TEMPLATE_CONFIG[$template] ?? self::TEMPLATE_CONFIG[0];
            $phpWord = new PhpWord;
            $phpWord->setDefaultFontName($config['font']);
            $phpWord->setDefaultFontSize(10);

            $section = $phpWord->addSection([
                'marginTop' => Converter::inchToTwip(0.7),
                'marginBottom' => Converter::inchToTwip(0.7),
                'marginLeft' => Converter::inchToTwip(0.75),
                'marginRight' => Converter::inchToTwip(0.75),
            ]);

            $norm = $analysis->normalized_cv_data;
            $labels = $norm['labels'] ?? [];

            $this->addHeader($section, $analysis, $config);
            $this->addSummary($section, $norm['profile_summary'] ?? '', $labels['profile'] ?? 'RÉSUMÉ PROFESSIONNEL', $config);
            $this->addSkills($section, $norm, $labels['skills'] ?? 'COMPÉTENCES CLÉS', $config);
            $this->addExperiences($section, $norm['experiences'] ?? [], $labels['experience'] ?? 'EXPÉRIENCES PROFESSIONNELLES', $labels['recently'] ?? 'Poste actuel', $config);
            $this->addEducation($section, $norm['education'] ?? [], $labels['education'] ?? 'FORMATIONS & DIPLÔMES', $config);
            $this->addCertifications($section, $norm['certifications'] ?? [], $labels['certifications'] ?? 'CERTIFICATIONS', $config);
            $this->addLanguages($section, $norm['languages'] ?? [], $labels['languages'] ?? 'LANGUES', $config);

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

    private function addHeader(Section $section, CvAnalysis $analysis, array $config): void
    {
        $section->addText(
            mb_strtoupper($analysis->candidate_name ?: 'CANDIDAT'),
            [
                'name' => $config['font'],
                'size' => $config['name_size'],
                'bold' => true,
                'color' => $config['primary_color'],
            ],
            ['spaceAfter' => 40, 'spaceBefore' => 0]
        );

        if ($analysis->candidate_title) {
            $section->addText(
                $analysis->candidate_title,
                [
                    'name' => $config['font'],
                    'size' => 12,
                    'bold' => true,
                    'color' => $config['secondary_color'],
                ],
                ['spaceAfter' => 60]
            );
        }

        $contactParts = $this->extractContactParts($analysis);
        if (! empty($contactParts)) {
            $section->addText(
                implode(' | ', $contactParts),
                [
                    'name' => $config['font'],
                    'size' => 9.5,
                    'color' => '4B5563',
                ],
                [
                    'spaceAfter' => 140,
                    'borderBottomSize' => 6,
                    'borderBottomColor' => $config['border_color'],
                ]
            );
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

    private function addSectionHeading(Section $section, string $title, array $config): void
    {
        $section->addText(
            mb_strtoupper($title),
            [
                'name' => $config['font'],
                'size' => 10.5,
                'bold' => true,
                'color' => $config['primary_color'],
            ],
            [
                'spaceBefore' => 180,
                'spaceAfter' => 60,
                'borderBottomSize' => 6,
                'borderBottomColor' => $config['border_color'],
            ]
        );
    }

    private function addSummary(Section $section, string $summary, string $heading, array $config): void
    {
        if (trim($summary) === '') {
            return;
        }

        $this->addSectionHeading($section, $heading, $config);
        $section->addText(
            $summary,
            ['name' => $config['font'], 'size' => 10, 'color' => '1F2937'],
            ['spaceAfter' => 100, 'alignment' => Jc::BOTH]
        );
    }

    private function addSkills(Section $section, array $norm, string $heading, array $config): void
    {
        $categorized = $norm['skills_categorized'] ?? [];
        $skills = $norm['skills'] ?? [];

        if (empty($categorized) && empty($skills)) {
            return;
        }

        $this->addSectionHeading($section, $heading, $config);

        if (! empty($categorized)) {
            foreach ($categorized as $cat) {
                $categoryName = $cat['category'] ?? '';
                $catSkills = is_array($cat['skills'] ?? null) ? implode(', ', $cat['skills']) : ($cat['skills'] ?? '');
                if (! empty($catSkills)) {
                    $run = $section->addTextRun(['spaceAfter' => 40]);
                    if (! empty($categoryName)) {
                        $run->addText($categoryName.' : ', ['name' => $config['font'], 'bold' => true, 'size' => 9.5, 'color' => '111827']);
                    }
                    $run->addText($catSkills, ['name' => $config['font'], 'size' => 9.5, 'color' => '374151']);
                }
            }
        } else {
            $skillNames = array_map(function ($s) {
                return is_array($s) ? ($s['name'] ?? '') : (string) $s;
            }, $skills);
            $skillNames = array_filter($skillNames);

            $section->addText(
                implode(' • ', $skillNames),
                ['name' => $config['font'], 'size' => 9.5, 'color' => '374151'],
                ['spaceAfter' => 80]
            );
        }
    }

    private function addExperiences(Section $section, array $experiences, string $heading, string $recentlyLabel, array $config): void
    {
        if (empty($experiences)) {
            return;
        }

        $this->addSectionHeading($section, $heading, $config);

        foreach ($experiences as $exp) {
            $title = $exp['title'] ?? 'Poste';
            $company = $exp['company'] ?? '';
            $period = $exp['period'] ?: $recentlyLabel;

            $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
            $table->addRow();
            $leftText = $company ? $title.' — '.$company : $title;
            $table->addCell(6800)->addText($leftText, ['name' => $config['font'], 'bold' => true, 'size' => 10, 'color' => '111827'], ['spaceAfter' => 20]);
            $table->addCell(2400)->addText($period, ['name' => $config['font'], 'italic' => true, 'size' => 9.5, 'color' => '4B5563'], ['spaceAfter' => 20, 'alignment' => Jc::END]);

            $bullets = $exp['bullets'] ?? [];
            if (! empty($bullets)) {
                foreach ($bullets as $bullet) {
                    if (trim((string) $bullet) !== '') {
                        $section->addListItem(
                            (string) $bullet,
                            0,
                            ['name' => $config['font'], 'size' => 9.5, 'color' => '374151'],
                            ['spaceAfter' => 30]
                        );
                    }
                }
            }
            $section->addText('', [], ['spaceAfter' => 60]);
        }
    }

    private function addEducation(Section $section, array $education, string $heading, array $config): void
    {
        if (empty($education)) {
            return;
        }

        $this->addSectionHeading($section, $heading, $config);

        foreach ($education as $edu) {
            $degree = $edu['degree'] ?? 'Diplôme';
            $school = $edu['school'] ?? '';
            $period = $edu['period'] ?? '';

            $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
            $table->addRow();
            $leftText = $school ? $degree.' — '.$school : $degree;
            $table->addCell(6800)->addText($leftText, ['name' => $config['font'], 'bold' => true, 'size' => 10, 'color' => '111827'], ['spaceAfter' => 20]);
            $table->addCell(2400)->addText($period, ['name' => $config['font'], 'italic' => true, 'size' => 9.5, 'color' => '4B5563'], ['spaceAfter' => 20, 'alignment' => Jc::END]);

            $bullets = $edu['bullets'] ?? [];
            if (! empty($bullets)) {
                foreach ($bullets as $bullet) {
                    if (trim((string) $bullet) !== '') {
                        $section->addListItem(
                            (string) $bullet,
                            0,
                            ['name' => $config['font'], 'size' => 9.5, 'color' => '374151'],
                            ['spaceAfter' => 30]
                        );
                    }
                }
            }
            $section->addText('', [], ['spaceAfter' => 40]);
        }
    }

    private function addCertifications(Section $section, array $certifications, string $heading, array $config): void
    {
        if (empty($certifications)) {
            return;
        }

        $this->addSectionHeading($section, $heading, $config);

        foreach ($certifications as $cert) {
            if (trim((string) $cert) !== '') {
                $section->addListItem(
                    (string) $cert,
                    0,
                    ['name' => $config['font'], 'size' => 9.5, 'color' => '374151'],
                    ['spaceAfter' => 30]
                );
            }
        }
        $section->addText('', [], ['spaceAfter' => 40]);
    }

    private function addLanguages(Section $section, array $languages, string $heading, array $config): void
    {
        if (empty($languages)) {
            return;
        }

        $this->addSectionHeading($section, $heading, $config);

        foreach ($languages as $lang) {
            if (trim((string) $lang) !== '') {
                $section->addListItem(
                    (string) $lang,
                    0,
                    ['name' => $config['font'], 'size' => 9.5, 'color' => '374151'],
                    ['spaceAfter' => 30]
                );
            }
        }
    }
}
