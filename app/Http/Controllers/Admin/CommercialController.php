<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Admin\CommercialCredentialsMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommercialController extends Controller
{
    public function index()
    {
        $commercials = User::where('is_commercial', true)->latest()->paginate(20);

        return view('admin.commercials.index', compact('commercials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $commercial = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_commercial' => true,
        ]);

        return back()->with('success', 'Commercial créé avec succès.');
    }

    public function resetPassword(User $commercial)
    {
        $password = Str::random(12);
        $commercial->update([
            'password' => Hash::make($password),
        ]);

        Mail::to($commercial->email)->send(new CommercialCredentialsMail($commercial, $password));

        return back()->with('success', "Le mot de passe de {$commercial->name} a été réinitialisé et envoyé par email.");
    }

    public function destroy(User $commercial)
    {
        if ($commercial->is_commercial) {
            $commercial->delete();

            return back()->with('success', 'Commercial supprimé avec succès.');
        }

        return back()->with('error', 'Utilisateur invalide.');
    }
}
