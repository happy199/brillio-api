<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Affiche le formulaire de vérification 2FA
     */
    public function index()
    {
        return view('admin.auth.two_factor');
    }

    /**
     * Vérifie le code 2FA saisi
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA;

        if ($google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            $request->session()->put('admin_2fa_verified', true);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['code' => 'Code de vérification invalide.']);
    }

    /**
     * Affiche la page de configuration 2FA (QR Code)
     */
    public function setup()
    {
        $user = auth()->user();
        $google2fa = new Google2FA;

        // Générer une clé secrète si elle n'existe pas
        if (! $user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            'Brillio',
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('admin.profile.two_factor', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->two_factor_secret,
        ]);
    }

    /**
     * Active le 2FA après vérification d'un premier code
     */
    public function activate(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA;

        if ($google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            $user->two_factor_confirmed_at = now();
            $user->save();

            $request->session()->put('admin_2fa_verified', true);

            return redirect()->route('admin.dashboard')->with('success', 'Double authentification activée avec succès.');
        }

        return back()->withErrors(['code' => 'Code de vérification invalide.']);
    }

    /**
     * Désactive le 2FA
     */
    public function deactivate(Request $request)
    {
        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $request->session()->forget('admin_2fa_verified');

        return back()->with('success', 'Double authentification désactivée.');
    }
}
