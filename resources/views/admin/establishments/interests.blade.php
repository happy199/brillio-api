@extends('layouts.admin')

@section('title', 'Prospects : ' . $establishment->name)

@section('header', 'Prospects : ' . $establishment->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('admin.establishments.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-2 mb-2">
                <i class="fas fa-arrow-left text-sm"></i> Retour
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Candidats intéressés par {{ $establishment->name }}</h1>
            <p class="text-sm text-gray-500">Liste des jeunes ayant manifesté un intérêt via la plateforme.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.establishments.interests.export-csv', $establishment) }}" 
                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition shadow-sm">
                <i class="fas fa-file-excel mr-2"></i> Exporter CSV
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jeune</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type d'intérêt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails Formulaire</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($interests as $interest)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $interest->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                    {{ substr($interest->user->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-gray-900">{{ $interest->user->name }}</div>
                                    <div class="text-xs text-gray-400">MBTI: {{ $interest->user->personalityTest->personality_type ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $interest->user->email }}</div>
                            <div class="text-xs text-gray-500">{{ $interest->user->phone ?? 'Pas de numéro' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($interest->type === 'quick')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-bolt mr-1"></i> Rapide
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-list mr-1"></i> Précis
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($interest->form_data)
                                <div class="text-xs space-y-1">
                                    @foreach($interest->form_data as $label => $value)
                                        <div><span class="font-bold text-gray-600">{{ $label }}:</span> <span class="text-gray-500">{{ is_array($value) ? implode(', ', $value) : $value }}</span></div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 italic text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-user-clock text-4xl mb-4 text-gray-200 block"></i>
                            <p class="text-lg font-medium">Aucun prospect pour le moment.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($interests->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $interests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
