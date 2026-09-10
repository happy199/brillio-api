<span class="inline-block align-baseline relative"
      x-data="cvPlaceholderItem({
          cvId: {{ $cvId }},
          fieldType: '{{ $fieldType }}',
          expIndex: {{ $expIndex !== null ? (int)$expIndex : 'null' }},
          bulletIndex: {{ $bulletIndex !== null ? (int)$bulletIndex : 'null' }},
          originalTag: @js($originalTag),
          currentValue: @js($currentValue),
          isFilled: {{ $isFilled ? 'true' : 'false' }}
      })">
    <!-- Non rempli (mode consultation) -->
    <template x-if="!isEditing && !isFilled">
        <button type="button"
                @click.stop="startEdit()"
                class="inline-flex items-center gap-1.5 mx-0.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200 transition-colors shadow-2xs cursor-pointer group select-none align-middle"
                title="Cliquer pour compléter cette information">
            <span x-text="originalTag">{{ $originalTag }}</span>
            <svg class="w-3 h-3 text-amber-700 opacity-70 group-hover:opacity-100 no-print transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
        </button>
    </template>

    <!-- Rempli (mode consultation avec crayon) -->
    <template x-if="!isEditing && isFilled">
        <span class="inline-flex items-baseline gap-1 mx-0.5 group select-text">
            <span class="font-semibold text-gray-900 underline decoration-amber-400 decoration-2 underline-offset-2 cv-filled-text cursor-pointer hover:text-amber-950 transition-colors"
                  @click.stop="startEdit()"
                  title="Cliquer pour modifier"
                  x-text="currentValue">{{ $currentValue }}</span>
            <button type="button"
                    @click.stop="startEdit()"
                    class="no-print opacity-60 hover:opacity-100 transition-opacity p-0.5 rounded text-gray-500 hover:text-amber-700 cursor-pointer inline-flex items-center align-middle"
                    title="Modifier cette information">
                <svg class="w-3 h-3 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
        </span>
    </template>

    <!-- Mode Édition Inline -->
    <template x-if="isEditing">
        <span class="inline-flex items-center gap-1 mx-0.5 no-print" @click.stop>
            <input type="text"
                   x-ref="inputField"
                   x-model="tempValue"
                   @keydown.enter.prevent.stop="save()"
                   @keydown.escape.prevent.stop="cancel()"
                   class="px-2 py-0.5 text-xs font-semibold text-gray-900 bg-white border-2 border-amber-500 rounded-md shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-400 min-w-[130px] max-w-[260px]"
                   :placeholder="placeholderText" />
            <button type="button"
                    @click.stop="save()"
                    :disabled="isSaving"
                    class="p-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md shadow-xs text-xs font-bold disabled:opacity-50 cursor-pointer"
                    title="Valider (Entrée)">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </button>
            <button type="button"
                    @click.stop="cancel()"
                    class="p-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-xs font-bold cursor-pointer"
                    title="Annuler (Échap)">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </span>
    </template>
</span>
