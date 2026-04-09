<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Consultation - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            DEFAULT: '#0f172a',
                            card: '#1e293b'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-dark text-white h-screen overflow-hidden">
    @if (!isset($consultation))
        <div class="flex items-center justify-center h-screen">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg>
                <p class="text-gray-400">Konsultasi tidak ditemukan</p>
                <a href="{{ route('healthcare.telemedicine.consultations') }}"
                    class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kembali</a>
            </div>
        </div>
    @else
        <div class="h-screen flex flex-col">
            {{-- Header --}}
            <div class="bg-dark-card border-b border-white/10 px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Video Consultation</h1>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ $consultation->patient ? $consultation->patient->full_name : '-' }}
                        <span class="text-gray-500">|</span>
                        Dr. {{ $consultation->doctor ? $consultation->doctor->name : '-' }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center gap-2 px-4 py-2 bg-green-500/20 border border-green-500/30 rounded-xl">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-400">Connected</span>
                    </div>
                    <div class="text-sm text-gray-400" id="timer">00:00:00</div>
                    <a href="{{ route('healthcare.telemedicine.consultations') }}"
                        class="px-4 py-2 text-sm border border-white/10 rounded-xl hover:bg-white/5">
                        Exit
                    </a>
                </div>
            </div>

            {{-- Video Area --}}
            <div class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-4 p-4">
                {{-- Main Video (Patient) --}}
                <div class="lg:col-span-2 relative bg-dark-card rounded-2xl overflow-hidden border border-white/10">
                    <video id="remoteVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                    <div class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-sm px-4 py-2 rounded-xl">
                        <p class="text-sm font-medium">
                            {{ $consultation->patient ? $consultation->patient->full_name : 'Patient' }}</p>
                    </div>
                    {{-- Waiting State --}}
                    <div id="waitingState" class="absolute inset-0 flex items-center justify-center bg-dark-card">
                        <div class="text-center">
                            <div
                                class="w-24 h-24 mx-auto mb-4 rounded-full bg-blue-500/20 flex items-center justify-center">
                                <svg class="w-12 h-12 text-blue-500 animate-pulse" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <p class="text-lg font-medium">Waiting for patient to join...</p>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-4">
                    {{-- Self Video --}}
                    <div class="relative bg-dark-card rounded-2xl overflow-hidden border border-white/10 aspect-video">
                        <video id="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                        <div class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-lg">
                            <p class="text-xs font-medium">You (Doctor)</p>
                        </div>
                    </div>

                    {{-- Patient Info --}}
                    <div class="bg-dark-card rounded-2xl border border-white/10 p-4">
                        <h3 class="text-sm font-semibold mb-3">Patient Information</h3>
                        <div class="space-y-2 text-sm">
                            <div>
                                <p class="text-gray-400">Age</p>
                                <p class="font-medium">{{ $consultation->patient ? $consultation->patient->age : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400">Complaint</p>
                                <p class="font-medium">{{ $consultation->complaint ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">Allergies</p>
                                <p class="font-medium text-red-400">
                                    {{ $consultation->patient && $consultation->patient->allergies ? $consultation->patient->allergies : 'None' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="bg-dark-card rounded-2xl border border-white/10 p-4">
                        <h3 class="text-sm font-semibold mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="#"
                                class="block w-full px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 rounded-lg text-center">
                                View Medical Records
                            </a>
                            <a href="#"
                                class="block w-full px-3 py-2 text-sm bg-purple-600 hover:bg-purple-700 rounded-lg text-center">
                                Write Prescription
                            </a>
                            <a href="#"
                                class="block w-full px-3 py-2 text-sm bg-green-600 hover:bg-green-700 rounded-lg text-center">
                                Add Notes
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Controls --}}
            <div class="bg-dark-card border-t border-white/10 px-6 py-4">
                <div class="flex items-center justify-center gap-4">
                    <button id="muteBtn" onclick="toggleMute()"
                        class="p-4 bg-gray-700 hover:bg-gray-600 rounded-full transition-colors">
                        <svg id="micIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z">
                            </path>
                        </svg>
                    </button>
                    <button id="videoBtn" onclick="toggleVideo()"
                        class="p-4 bg-gray-700 hover:bg-gray-600 rounded-full transition-colors">
                        <svg id="cameraIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                    </button>
                    <button id="screenShareBtn" onclick="toggleScreenShare()"
                        class="p-4 bg-gray-700 hover:bg-gray-600 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </button>
                    <button onclick="endCall()"
                        class="p-4 bg-red-600 hover:bg-red-700 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <script>
            let localStream;
            let isMuted = false;
            let isVideoOff = false;
            let seconds = 0;

            // Timer
            setInterval(() => {
                seconds++;
                const hrs = String(Math.floor(seconds / 3600)).padStart(2, '0');
                const mins = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
                const secs = String(seconds % 60).padStart(2, '0');
                document.getElementById('timer').textContent = `${hrs}:${mins}:${secs}`;
            }, 1000);

            // Initialize camera
            async function initCamera() {
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    });
                    document.getElementById('localVideo').srcObject = localStream;
                } catch (err) {
                    console.error('Error accessing camera:', err);
                }
            }

            function toggleMute() {
                isMuted = !isMuted;
                localStream.getAudioTracks().forEach(track => track.enabled = !isMuted);
                document.getElementById('micIcon').innerHTML = isMuted ?
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707 4.707C10.923 13.378 12 12.288 12 12V5a3 3 0 016 0v6c0 .288-.077.577-.293.807L19.414 15H20a1 1 0 011 1v4a1 1 0 01-1 1h-1.586l-4.707-4.707C13.077 16.622 12 17.712 12 18v-3z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"></path>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>';
            }

            function toggleVideo() {
                isVideoOff = !isVideoOff;
                localStream.getVideoTracks().forEach(track => track.enabled = !isVideoOff);
            }

            function toggleScreenShare() {
                // Implement screen sharing
                alert('Screen sharing akan tersedia setelah WebRTC backend diimplementasikan');
            }

            function endCall() {
                if (confirm('Akhiri konsultasi ini?')) {
                    window.location.href = '{{ route('healthcare.telemedicine.consultations') }}';
                }
            }

            // Initialize on load
            window.addEventListener('load', initCamera);
        </script>
    @endif
</body>

</html>
