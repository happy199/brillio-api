@extends('layouts.auth')

@section('title', 'Connexion Mentor')
@section('heading', 'Espace Mentor')
@section('subheading', 'Partagez votre experience et inspirez la jeunesse africaine')

@section('content')
    <div class="space-y-6">
        <!-- LinkedIn Info -->
        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-[#0A66C2] rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-900">Pourquoi LinkedIn ?</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        Pour garantir la qualite de notre communaute de mentors, nous utilisons LinkedIn pour verifier votre
                        identite professionnelle et recuperer votre photo de profil.
                    </p>
                </div>
            </div>
        </div>

        <!-- LinkedIn Button -->
        <a href="{{ route('auth.mentor.linkedin') }}"
            class="btn-oauth w-full flex items-center justify-center gap-3 px-4 py-4 bg-[#0A66C2] rounded-xl font-semibold text-white hover:bg-[#004182] transition-all shadow-lg">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
            </svg>
            Se connecter avec LinkedIn
        </a>

        <!-- What you get -->
        <div class="pt-4 border-t border-gray-100">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">En tant que mentor, vous pourrez :</h4>
            <ul class="space-y-2">
                <li class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Creer votre profil professionnel inspirant
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Partager votre parcours etape par etape
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Inspirer des milliers de jeunes africains
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Rejoindre une communaute de professionnels engages
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('footer')
    <p class="text-white/80 text-sm">
        Vous etes un jeune ?
        <a href="{{ route('auth.jeune.login') }}" class="text-white font-semibold hover:underline">Connexion jeune</a>
    </p>
@endsection