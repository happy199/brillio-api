<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDocumentPreviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_preview_academic_document_inline(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['user_type' => 'jeune']);
        $filePath = 'documents/test_attestation.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 Fake PDF Content');

        $document = AcademicDocument::create([
            'user_id' => $user->id,
            'document_type' => 'attestation',
            'file_name' => 'test_attestation.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.documents.preview', $document));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="test_attestation.pdf"');
    }

    public function test_admin_document_preview_returns_error_if_file_not_found(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['user_type' => 'jeune']);

        $document = AcademicDocument::create([
            'user_id' => $user->id,
            'document_type' => 'attestation',
            'file_name' => 'missing.pdf',
            'file_path' => 'documents/missing.pdf',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.documents.preview', $document));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Fichier introuvable');
    }
}
