<?php

$content = file_get_contents('/Users/macbookpro/Documents/brillio/brillio-api/app/Http/Controllers/Auth/WebAuthController.php');

$method = "
    /**
     * Process organization invitation linking for a user
     */
    protected function processOrganizationInvitation(User \$user): void
    {
        \$referralCode = session('referral_code');
        if (!\$referralCode) {
            return;
        }

        \$invitation = OrganizationInvitation::where('referral_code', \$referralCode)
            ->where('status', 'pending')
            ->whereDate('expires_at', '>=', now())
            ->first();

        if (\$invitation) {
            \$user->organizations()->syncWithoutDetaching([
                \$invitation->organization_id => [
                    'referral_code_used' => \$referralCode,
                    'role' => \$invitation->role ?? 'jeune',
                ],
            ]);

            if (in_array(\$invitation->role, ['admin', 'viewer'])) {
                \$user->update([
                    'organization_id' => \$invitation->organization_id,
                    'organization_role' => \$invitation->role,
                ]);
            }

            \$invitation->markAsAccepted();
            session()->forget(['referral_code', 'organization_name']);

            Log::info('User auto-linked to organization via invitation', [
                'user_id' => \$user->id,
                'organization_id' => \$invitation->organization_id,
                'referral_code' => \$referralCode,
            ]);
        }
    }
";

// We'll just replace the duplicated blocks with $this->processOrganizationInvitation($user);

// Wait, this is better done manually or using a patch. Let's just create a quick patch file.
