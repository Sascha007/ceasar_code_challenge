<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto space-y-8 py-8">
        <!-- Competition Management Section -->
        <livewire:admin.competition-management />

        <!-- Team Management Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 mx-8">
            <div class="p-8 bg-gradient-to-r from-blue-600 to-blue-700">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-2">Team Management</h2>
                        <p class="text-blue-100">Verwalten Sie Ihre Teams</p>
                    </div>
                    <a href="{{ route('admin.teams.qr-codes') }}" 
                       class="inline-flex items-center px-6 py-3 bg-white text-blue-700 border border-transparent rounded-xl font-semibold text-sm hover:bg-blue-50 active:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        QR-Codes herunterladen
                    </a>
                </div>
            </div>
            <div class="p-8">
                <!-- Hier kommt der Rest des Dashboards -->
            </div>
        </div>
    </div>
</div>
