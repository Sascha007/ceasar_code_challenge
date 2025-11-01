<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team {{ $team->display_name }} - Caesar Challenge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen" x-data="teamPage()">
    <div class="container mx-auto px-4 py-8">
        <header class="text-center mb-12">
            <h1 class="text-3xl font-bold mb-2">Team {{ $team->display_name }}</h1>
            <div x-show="competition.status === 'running'" class="text-xl">
                <span x-text="timer" class="font-mono"></span>
            </div>
        </header>

        <main class="max-w-lg mx-auto">
            <!-- Bereit-Status -->
            <div x-show="competition.status === 'ready' && !team.ready_at" 
                class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Klicke auf "Bereit", wenn du startklar bist.
                        </p>
                        <button @click="markReady" 
                            class="mt-2 bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                            Bereit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Warte auf Start -->
            <div x-show="competition.status === 'ready' && team.ready_at"
                class="bg-blue-100 border-l-4 border-blue-500 p-4 mb-8">
                <p class="text-blue-700">Warte auf den Start des Wettbewerbs...</p>
            </div>

            <!-- Aktives Rätsel -->
            <div x-show="competition.status === 'running' && !team.solved_at"
                class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Deine verschlüsselte Nachricht:</h2>
                    <div class="bg-gray-100 p-4 rounded font-mono break-words">
                        {{ $team->puzzle->ciphertext }}
                    </div>
                </div>

                <form @submit.prevent="submitSolution">
                    <div class="mb-4">
                        <label for="solution" class="block text-gray-700 font-medium mb-2">
                            Deine Lösung:
                        </label>
                        <textarea
                            id="solution"
                            x-model="solution"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            rows="3"
                            required
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit"
                            class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Lösung einreichen
                        </button>
                        <span class="text-gray-600">
                            Versuche: <span x-text="team.attempts">{{ $team->attempts }}</span>
                        </span>
                    </div>
                </form>
            </div>

            <!-- Gelöst -->
            <div x-show="team.solved_at"
                class="bg-green-100 border-l-4 border-green-500 p-4 mb-8">
                <h2 class="text-xl font-bold text-green-800 mb-2">Glückwunsch!</h2>
                <p class="text-green-700">
                    Du hast das Rätsel gelöst in <span x-text="formatTime(solvedTime)"></span> mit 
                    <span x-text="team.attempts"></span> Versuch(en).
                </p>
            </div>

            <!-- Feedback Message -->
            <div x-show="message" 
                :class="messageType === 'error' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-green-100 border-green-500 text-green-700'"
                class="border-l-4 p-4 mb-8"
                x-text="message">
            </div>
        </main>
    </div>

    <script>
        function teamPage() {
            return {
                competition: @json($team->competition),
                team: @json($team),
                solution: '',
                message: '',
                messageType: '',
                timer: '00:00',
                solvedTime: {{ $team->solved_at ? $team->solved_at->diffInSeconds($team->competition->started_at) : 0 }},

                init() {
                    this.setupEventSource();
                    this.startTimer();
                },

                setupEventSource() {
                    if (typeof EventSource !== 'undefined') {
                        const source = new EventSource(`/events/competition/${this.competition.id}`);
                        source.onmessage = (event) => {
                            const data = JSON.parse(event.data);
                            this.competition = data.competition;
                            this.team = data.teams.find(t => t.id === this.team.id);
                        };
                    }
                },

                startTimer() {
                    if (this.competition.status === 'running' && this.competition.started_at) {
                        const startTime = new Date(this.competition.started_at).getTime();
                        setInterval(() => {
                            const now = Date.now();
                            const diff = Math.floor((now - startTime) / 1000);
                            this.timer = this.formatTime(diff);
                        }, 1000);
                    }
                },

                async markReady() {
                    try {
                        const response = await fetch(`/t/${this.team.slug}/ready`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await response.json();
                        if (data.status === 'success') {
                            this.team.ready_at = new Date().toISOString();
                        }
                    } catch (error) {
                        console.error('Error marking ready:', error);
                    }
                },

                async submitSolution() {
                    try {
                        const response = await fetch(`/t/${this.team.slug}/submit`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ solution: this.solution })
                        });

                        const data = await response.json();
                        this.message = data.message;
                        this.messageType = data.status;
                        this.team.attempts = data.attempts;

                        if (data.status === 'success') {
                            this.team.solved_at = new Date().toISOString();
                            this.solvedTime = data.time;
                        }

                        setTimeout(() => {
                            this.message = '';
                        }, 5000);

                    } catch (error) {
                        console.error('Error submitting solution:', error);
                        this.message = 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.';
                        this.messageType = 'error';
                    }
                },

                formatTime(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                }
            }
        }
    </script>
</body>
</html>