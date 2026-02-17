@extends('layouts.public')

{{-- SEO Meta Tags --}}
<x-seo-meta page="contact" />


@section('content')
<!-- Hero Section -->
<section class="gradient-hero pt-32 pb-20 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 -left-40 w-96 h-96 bg-secondary-500/20 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h1 class="text-4xl sm:text-5xl font-bold mb-6" data-aos="fade-up">
                Contactez-nous
            </h1>
            <p class="text-xl text-white/90" data-aos="fade-up" data-aos-delay="100">
                Une question, une suggestion ou une demande de partenariat ?
                Nous sommes là pour vous.
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16">
            <!-- Contact Info -->
            <div data-aos="fade-right">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">
                    Restons en contact
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Que vous soyez un jeune en quête d'orientation, un professionnel souhaitant devenir mentor,
                    ou une organisation intéressée par un partenariat, n'hésitez pas à nous écrire.
                </p>

                <!-- Contact Cards -->
                <div class="space-y-6">
                    <!-- Email -->
                    <div class="flex items-start space-x-4 p-6 bg-gray-50 rounded-2xl">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                            <p class="text-gray-600">Pour toute demande générale</p>
                            <!-- TODO: Mettre la vraie adresse email -->
                            <a href="mailto:contact@brillio.africa"
                                class="text-primary-600 hover:underline font-medium">
                                contact@brillio.africa
                            </a>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start space-x-4 p-6 bg-gray-50 rounded-2xl">
                        <div
                            class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Téléphone</h3>
                            <p class="text-gray-600">Du lundi au vendredi, 9h-18h</p>
                            <a href="tel:+2290166301736" class="text-primary-600 hover:underline font-medium">
                                +229 01 66 30 17 36
                            </a>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex items-start space-x-4 p-6 bg-gray-50 rounded-2xl">
                        <div class="w-12 h-12 bg-accent-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Adresse</h3>
                            <p class="text-gray-600">Notre siège</p>
                            <!-- TODO: Mettre la vraie adresse -->
                            <p class="text-gray-800">
                                Cotonou, Bénin
                            </p>
                        </div>
                    </div>
                </div>


                <!-- Social Links -->
                <div class="mt-8">
                    <h3 class="font-semibold text-gray-900 mb-4">Suivez-nous</h3>
                    <div class="flex flex-wrap gap-4">
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/share/1E5k4UqPqB" target="_blank" rel="noopener noreferrer"
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-primary-100 hover:text-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                            </svg>
                        </a>
                        <!-- Instagram -->
                        <a href="https://www.instagram.com/brillioafrica/" target="_blank" rel="noopener noreferrer"
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-primary-100 hover:text-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>
                        <!-- LinkedIn -->
                        <a href="https://www.linkedin.com/company/brillio-africa" target="_blank"
                            rel="noopener noreferrer"
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-primary-100 hover:text-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                        </a>
                        <!-- Threads -->
                        <a href="https://www.threads.com/@brillioafrica" target="_blank" rel="noopener noreferrer"
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-primary-100 hover:text-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.186 3.995c-5.569 0-9.214 3.645-9.214 9.213 0 5.569 3.645 9.214 9.214 9.214 5.568 0 9.213-3.645 9.213-9.214 0-5.568-3.645-9.213-9.213-9.213zm3.439 12.417c-.448.448-1.048.672-1.797.672-.598 0-1.122-.149-1.572-.448a3.432 3.432 0 01-1.123-1.272 3.432 3.432 0 01-1.123 1.272c-.449.299-.973.448-1.572.448-.748 0-1.348-.224-1.797-.672-.448-.449-.672-1.048-.672-1.797 0-.598.149-1.123.448-1.572a3.432 3.432 0 011.272-1.123 3.432 3.432 0 01-1.272-1.123 3.08 3.08 0 01-.448-1.572c0-.748.224-1.348.672-1.797.449-.448 1.049-.672 1.797-.672.599 0 1.123.15 1.572.448a3.432 3.432 0 011.123 1.272 3.432 3.432 0 011.123-1.272c.449-.299.974-.448 1.572-.448.749 0 1.349.224 1.797.672.449.449.672 1.049.672 1.797 0 .599-.149 1.123-.448 1.572a3.432 3.432 0 01-1.272 1.123c.523.3.948.698 1.272 1.123.299.449.448.974.448 1.572 0 .749-.223 1.348-.672 1.797z" />
                            </svg>
                        </a>
                        <!-- TikTok -->
                        <a href="https://www.tiktok.com/@brillioafrica" target="_blank" rel="noopener noreferrer"
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-primary-100 hover:text-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
                            </svg>
                        </a>
                        <!-- YouTube -->
                        <a href="https://www.youtube.com/channel/UCGUzyoVoVLXzNGxhRN8tXCg" target="_blank"
                            rel="noopener noreferrer"
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-primary-100 hover:text-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div data-aos="fade-left">
                <div class="bg-gray-50 rounded-3xl p-8 md:p-10">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Envoyez-nous un message</h3>

                    @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom complet *</label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                placeholder="Votre nom">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                placeholder="votre@email.com">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Sujet *</label>
                            <select id="subject" name="subject" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('subject') border-red-500 @enderror">
                                <option value="">Sélectionnez un sujet</option>
                                <option value="Question générale" {{ old('subject')=='Question générale' ? 'selected'
                                    : '' }}>Question générale</option>
                                <option value="Support technique" {{ old('subject')=='Support technique' ? 'selected'
                                    : '' }}>Support technique</option>
                                <option value="Devenir mentor" {{ old('subject')=='Devenir mentor' ? 'selected' : '' }}>
                                    Devenir mentor</option>
                                <option value="Partenariat" {{ old('subject')=='Partenariat' ? 'selected' : '' }}>
                                    Partenariat</option>
                                <option value="Presse / Média" {{ old('subject')=='Presse / Média' ? 'selected' : '' }}>
                                    Presse / Média</option>
                                <option value="Autre" {{ old('subject')=='Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('subject')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea id="message" name="message" rows="5" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none @error('message') border-red-500 @enderror"
                                placeholder="Votre message...">{{ old('message') }}</textarea>
                            @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                            class="w-full px-8 py-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-bold rounded-xl hover:shadow-lg transition-all duration-300">
                            Envoyer le message
                        </button>

                        <p class="text-xs text-gray-500 text-center">
                            En soumettant ce formulaire, vous acceptez notre
                            <a href="{{ route('privacy-policy') }}" class="underline">politique de confidentialité</a>.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12" data-aos="fade-up">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">FAQ</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-4">Questions fréquentes</h2>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- FAQ Item 1 -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                    <button @click="open = open === 1 ? null : 1"
                        class="w-full px-6 py-4 text-left flex items-center justify-between">
                        <span class="font-semibold text-gray-900">Comment télécharger l'application Brillio ?</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">
                            <!-- TODO: Mettre les vrais liens de téléchargement -->
                            Brillio est disponible sur l'App Store pour iOS et le Google Play Store pour Android.
                            Recherchez simplement "Brillio" ou suivez les liens de téléchargement sur notre page
                            d'accueil. L'inscription est gratuite et vous donne accès à un essai complet de la
                            plateforme.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="150">
                    <button @click="open = open === 2 ? null : 2"
                        class="w-full px-6 py-4 text-left flex items-center justify-between">
                        <span class="font-semibold text-gray-900">Quel est le modèle tarifaire de Brillio ?</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">
                            Brillio propose un modèle freemium accessible à tous. L'inscription est gratuite et vous
                            donne accès à un essai complet incluant le test de personnalité, le chatbot IA, et la
                            découverte des profils de mentors. Des fonctionnalités premium sont disponibles pour
                            approfondir votre accompagnement.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <button @click="open = open === 3 ? null : 3"
                        class="w-full px-6 py-4 text-left flex items-center justify-between">
                        <span class="font-semibold text-gray-900">Comment devenir mentor sur Brillio ?</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">
                            <!-- TODO: Expliquer le processus pour devenir mentor -->
                            Si vous êtes un professionnel africain souhaitant partager votre parcours et inspirer les
                            jeunes,
                            vous pouvez créer un compte mentor via l'application ou nous contacter via ce formulaire.
                            Notre équipe validera votre profil avant publication.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="250">
                    <button @click="open = open === 4 ? null : 4"
                        class="w-full px-6 py-4 text-left flex items-center justify-between">
                        <span class="font-semibold text-gray-900">Mes données sont-elles protégées ?</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">
                            Absolument. La protection de vos données est notre priorité. Nous utilisons un chiffrement
                            de bout en bout et ne partageons jamais vos informations avec des tiers. Consultez notre
                            <a href="{{ route('privacy-policy') }}" class="text-primary-600 hover:underline">politique
                                de confidentialité</a>
                            pour plus de détails.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                    <button @click="open = open === 5 ? null : 5"
                        class="w-full px-6 py-4 text-left flex items-center justify-between">
                        <span class="font-semibold text-gray-900">Comment fonctionne le chatbot IA ?</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open === 5 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open === 5" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">
                            Notre chatbot utilise l'intelligence artificielle DeepSeek R1 pour répondre à vos questions
                            sur l'orientation, les métiers et les formations. Il est spécialement entraîné pour
                            comprendre
                            le contexte africain et donner des conseils pertinents. Vos conversations sont privées et
                            sécurisées.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Optional) -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-200 rounded-3xl overflow-hidden h-96 flex items-center justify-center" data-aos="fade-up">
            <!-- TODO: Intégrer une vraie carte Google Maps ou autre -->
            <div class="text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p>Carte interactive - Cotonou, Bénin</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endpush