<div class="p-8 bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-6 bg-gradient-to-r from-emerald-50 to-teal-50 border-l-4 border-emerald-500 text-emerald-900 px-6 py-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ session('message') }}</span>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-900 px-6 py-4 rounded-r-lg shadow-sm animate-fade-in" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
            <div class="p-8 bg-gradient-to-r from-blue-600 to-blue-700">
                <!-- Header -->
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-2">Wettbewerbe verwalten</h2>
                        <p class="text-blue-100">Erstellen und verwalten Sie Ihre Wettbewerbe</p>
                    </div>
                    <button wire:click="create" class="inline-flex items-center px-6 py-3 bg-white text-blue-700 border border-transparent rounded-xl font-semibold text-sm hover:bg-blue-50 active:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Neuer Wettbewerb
                    </button>
                </div>
            </div>
            <div class="p-8">

                <!-- Competitions List -->
                <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Teams</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Start-Zeit</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">End-Zeit</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($competitions as $competition)
                                <tr class="hover:bg-blue-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $competition->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm
                                            @if($competition->status === 'draft') bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800
                                            @elseif($competition->status === 'ready') bg-gradient-to-r from-yellow-100 to-amber-200 text-yellow-900
                                            @elseif($competition->status === 'running') bg-gradient-to-r from-green-100 to-emerald-200 text-green-900
                                            @elseif($competition->status === 'finished') bg-gradient-to-r from-blue-100 to-indigo-200 text-blue-900
                                            @endif">
                                            @if($competition->status === 'draft') Entwurf
                                            @elseif($competition->status === 'ready') Bereit
                                            @elseif($competition->status === 'running') Läuft
                                            @elseif($competition->status === 'finished') Beendet
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700">{{ $competition->teams_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $competition->started_at ? $competition->started_at->format('d.m.Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $competition->finished_at ? $competition->finished_at->format('d.m.Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <button wire:click="edit({{ $competition->id }})" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline transition-colors">Bearbeiten</button>
                                            
                                            @if($competition->status === 'ready' && $competition->teams_count > 0)
                                                <button wire:click="startCompetition({{ $competition->id }})" class="text-green-600 hover:text-green-800 font-semibold hover:underline transition-colors">Start</button>
                                            @endif
                                            
                                            @if($competition->status === 'running')
                                                <button wire:click="finishCompetition({{ $competition->id }})" class="text-orange-600 hover:text-orange-800 font-semibold hover:underline transition-colors">Beenden</button>
                                            @endif
                                            
                                            @if($competition->status === 'finished')
                                                <button wire:click="resetCompetition({{ $competition->id }})" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline transition-colors">Zurücksetzen</button>
                                            @endif
                                            
                                            <a href="{{ route('status.show', $competition) }}" target="_blank" class="text-gray-600 hover:text-gray-800 font-semibold hover:underline transition-colors">Status</a>
                                            
                                            <button wire:click="confirmDelete({{ $competition->id }})" class="text-red-600 hover:text-red-800 font-semibold hover:underline transition-colors">Löschen</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-gray-500 font-medium">Keine Wettbewerbe vorhanden</p>
                                        <p class="text-gray-400 text-sm mt-1">Erstellen Sie einen neuen Wettbewerb, um zu beginnen</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed z-50 inset-0 overflow-y-auto animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-slide-up">
                    <form wire:submit.prevent="save">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                            <h3 class="text-2xl leading-6 font-bold text-white flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                {{ $selectedCompetition ? 'Wettbewerb bearbeiten' : 'Neuer Wettbewerb' }}
                            </h3>
                        </div>
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="mb-6">
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Name</label>
                                <input type="text" wire:model="name" id="name" class="mt-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 transition-all @error('name') border-red-500 ring-2 ring-red-200 @enderror" placeholder="Geben Sie einen Wettbewerbsnamen ein">
                                @error('name') 
                                    <div class="mt-2 flex items-center text-red-600 text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                                <select wire:model="status" id="status" class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all">
                                    <option value="draft">Entwurf</option>
                                    <option value="ready">Bereit</option>
                                    <option value="running">Läuft</option>
                                    <option value="finished">Beendet</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-blue-600 text-base font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 sm:w-auto sm:text-sm transition-all transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Speichern
                            </button>
                            <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 sm:mt-0 sm:w-auto sm:text-sm transition-all">
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed z-50 inset-0 overflow-y-auto animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeDeleteModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full animate-slide-up">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-red-100 to-red-200 sm:mx-0 sm:h-14 sm:w-14 shadow-lg">
                                <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-2xl leading-6 font-bold text-gray-900 mb-3">Wettbewerb löschen</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        Sind Sie sicher, dass Sie <span class="font-bold text-gray-900">"{{ $competitionToDelete?->name }}"</span> löschen möchten? 
                                    </p>
                                    <p class="text-sm text-red-600 font-medium mt-2">
                                        Diese Aktion kann nicht rückgängig gemacht werden.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="delete" class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-red-600 text-base font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 sm:w-auto sm:text-sm transition-all transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Löschen
                        </button>
                        <button type="button" wire:click="closeDeleteModal" class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 sm:mt-0 sm:w-auto sm:text-sm transition-all">
                            Abbrechen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
    </style>
</div>
