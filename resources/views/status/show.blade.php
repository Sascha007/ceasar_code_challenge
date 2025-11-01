<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wettbewerb-Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontSize: {
                        '7xl': '5rem',
                        '8xl': '6rem',
                        '9xl': '7rem',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen" x-data="statusPage()" x-init="init()">
    <div class="container mx-auto px-4 py-8">
        <header class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-4">{{ $competition->name }}</h1>
            <div class="text-2xl">
                <span x-text="statusText" class="font-medium"></span>
                <template x-if="competition.status === 'running'">
                    <span x-text="timer" class="ml-2 font-mono"></span>
                </template>
            </div>
        </header>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Team Status Grid -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-3xl font-bold mb-6">Team-Status</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <template x-for="team in teams" :key="team.name">
                        <div class="relative">
                            <div :class="{
                                'p-4 rounded-lg text-center transform transition-all duration-300': true,
                                'bg-gray-700 text-gray-300': team.status === 'waiting',
                                'bg-yellow-600 text-yellow-100': team.status === 'ready',
                                'bg-green-600 text-green-100': team.status === 'active',
                                'bg-blue-600 text-blue-100': team.status === 'solved',
                                'scale-105': team.status === 'solved'
                            }">
                                <div class="font-bold text-lg mb-1" x-text="team.name"></div>
                                <div class="text-sm opacity-90" x-text="getStatusText(team)"></div>
                                <template x-if="team.status === 'solved'">
                                    <div class="mt-2 text-xs">
                                        <div>Zeit: <span x-text="formatTime(team.time)"></span></div>
                                        <div>Versuche: <span x-text="team.attempts"></span></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ranking -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-3xl font-bold mb-6">Rangliste</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left">
                                <th class="px-4 py-2">Platz</th>
                                <th class="px-4 py-2">Team</th>
                                <th class="px-4 py-2">Zeit</th>
                                <th class="px-4 py-2">Versuche</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(team, index) in solvedTeams" :key="team.name">
                                <tr :class="index === 0 ? 'text-yellow-400' : index === 1 ? 'text-gray-400' : index === 2 ? 'text-yellow-700' : ''">
                                    <td class="px-4 py-2" x-text="index + 1"></td>
                                    <td class="px-4 py-2 font-medium" x-text="team.name"></td>
                                    <td class="px-4 py-2 font-mono" x-text="formatTime(team.time)"></td>
                                    <td class="px-4 py-2" x-text="team.attempts"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function statusPage() {
            return {
                competition: @json($competition),
                teams: @json($teams),
                startTime: @json(optional($competition->started_at)->timestamp),
                timer: '00:00',
                eventSource: null,

                init() {
                    this.setupEventSource();
                    this.startTimer();
                },

                setupEventSource() {
                    if (typeof EventSource !== 'undefined') {
                        this.eventSource = new EventSource(`/events/competition/${this.competition.id}`);
                        this.eventSource.onmessage = (event) => {
                            const data = JSON.parse(event.data);
                            this.teams = data.teams;
                        };
                    } else {
                        // Fallback: Polling alle 2 Sekunden
                        setInterval(() => {
                            fetch(`/status/${this.competition.id}`)
                                .then(response => response.json())
                                .then(data => {
                                    this.teams = data.teams;
                                });
                        }, 2000);
                    }
                },

                startTimer() {
                    if (this.competition.status === 'running' && this.startTime) {
                        setInterval(() => {
                            const now = Math.floor(Date.now() / 1000);
                            const diff = now - this.startTime;
                            this.timer = this.formatTime(diff);
                        }, 1000);
                    }
                },

                get statusText() {
                    switch (this.competition.status) {
                        case 'draft': return 'Entwurf';
                        case 'ready': return 'Bereit zum Start';
                        case 'running': return 'Läuft';
                        case 'finished': return 'Beendet';
                        default: return this.competition.status;
                    }
                },

                get solvedTeams() {
                    return this.teams.filter(team => team.status === 'solved');
                },

                getStatusText(team) {
                    switch (team.status) {
                        case 'waiting': return 'Wartet';
                        case 'ready': return 'Bereit';
                        case 'active': return 'Aktiv';
                        case 'solved': return 'Gelöst!';
                        default: return team.status;
                    }
                },

                formatTime(seconds) {
                    if (!seconds) return '--:--';
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                }
            }
        }
    </script>
</body>
</html>
