@extends('layouts.admin')

@section('title', 'Espace Coach')

@section('header', 'Espace Coach')

@section('content')
<div class="space-y-6">
    <div class="bg-indigo-700 rounded-lg shadow-lg p-6 text-white">
        <h2 class="text-2xl font-bold">Bienvenue, {{ auth()->user()->name }} !</h2>
        <p class="mt-2 text-indigo-100">Vous êtes connecté en tant que Coach Brillio.</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                    <i class="fas fa-comments fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 uppercase font-bold">Demandes en attente</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $stats['pending_support'] }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.chat.index', ['status' => 'needs_support']) }}"
                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    Voir les demandes <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                    <i class="fas fa-headset fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 uppercase font-bold">Mes sessions actives</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $stats['active_support'] }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.chat.index', ['status' => 'in_support']) }}"
                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                    Aller au chat <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Accès Rapides -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Outils de Coaching</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('admin.chat.index') }}"
                class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition text-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mb-3">
                    <i class="fas fa-comments text-xl"></i>
                </div>
                <span class="font-medium text-gray-900">Console de Chat</span>
                <span class="text-sm text-gray-500 mt-1">Répondre aux utilisateurs</span>
            </a>

            <a href="{{ route('admin.mentors.index') }}"
                class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition text-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mb-3">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <span class="font-medium text-gray-900">Annuaire des Mentors</span>
                <span class="text-sm text-gray-500 mt-1">Consulter les profils</span>
            </a>

            <a href="{{ route('admin.resources.index') }}"
                class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition text-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mb-3">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <span class="font-medium text-gray-900">Ressources</span>
                <span class="text-sm text-gray-500 mt-1">Bibliothèque de contenus</span>
            </a>
        </div>
    </div>
</div>
@endsection