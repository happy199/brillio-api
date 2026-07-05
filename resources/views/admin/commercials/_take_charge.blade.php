@if(auth()->user()->isAdmin() || auth()->user()->isCommercial())
    @php
        $modelClass = match($type) {
            'user' => \App\Models\User::class,
            'mentor' => \App\Models\MentorProfile::class,
            'organization' => \App\Models\Organization::class,
        };
        $activeActivity = \App\Models\CommercialActivity::where('assignable_type', $modelClass)
            ->where('assignable_id', $id)
            ->where('status', 'active')
            ->first();
    @endphp

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 {{ $activeActivity ? 'border-indigo-500' : 'border-gray-300' }}">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Suivi Commercial</h3>
        
        @if($activeActivity)
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Ce dossier est actuellement pris en charge par <span class="font-bold">{{ $activeActivity->commercial->name }}</span> depuis le {{ $activeActivity->started_at->format('d/m/Y H:i') }}.
                </p>
                @if(auth()->id() === $activeActivity->commercial_id || auth()->user()->isAdmin())
                    <button type="button" 
                            @click="$dispatch('open-close-activity-modal', { url: '{{ route('admin.commercials.end_charge', $activeActivity) }}' })"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Clôturer le dossier
                    </button>
                @endif
            </div>
        @else
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Aucun commercial n'est actuellement en charge de ce dossier.</p>
                <form action="{{ route('admin.commercials.take_charge') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="id" value="{{ $id }}">
                    <button type="submit" class="px-4 py-2 bg-white border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 text-sm font-medium">
                        Prendre en charge
                    </button>
                </form>
            </div>
        @endif
    </div>
@endif