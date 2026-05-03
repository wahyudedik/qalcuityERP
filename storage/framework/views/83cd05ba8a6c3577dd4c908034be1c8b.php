<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Consultation - <?php echo e(config('app.name')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src='<?php echo e(rtrim($jitsiServerUrl, '/')); ?>/external_api.js'></script>
    <script>
        tailwind.config = {
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
    <?php if(!isset($consultation)): ?>
        <div class="flex items-center justify-center h-screen">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg>
                <p class="text-gray-400">Konsultasi tidak ditemukan</p>
                <a href="<?php echo e(route('healthcare.telemedicine.consultations')); ?>"
                    class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kembali</a>
            </div>
        </div>
    <?php else: ?>
        <div class="h-screen flex flex-col">
            
            <div class="bg-dark-card border-b border-white/10 px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Video Consultation</h1>
                    <p class="text-sm text-gray-400 mt-1">
                        <?php echo e($consultation->patient ? $consultation->patient->full_name : '-'); ?>

                        <span class="text-gray-500">|</span>
                        Dr. <?php echo e($consultation->doctor ? $consultation->doctor->name : '-'); ?>

                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center gap-2 px-4 py-2 bg-green-500/20 border border-green-500/30 rounded-xl">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-400">Connected</span>
                    </div>
                    <div class="text-sm text-gray-400" id="timer">00:00:00</div>
                    <a href="<?php echo e(route('healthcare.telemedicine.consultations')); ?>"
                        class="px-4 py-2 text-sm border border-white/10 rounded-xl hover:bg-white/5">
                        Exit
                    </a>
                </div>
            </div>

            
            <div class="flex-1 p-4">
                <div id="jitsi-meet-container" class="w-full h-full rounded-2xl overflow-hidden border border-white/10">
                </div>
            </div>
        </div>

        <script>
            // Jitsi Meet Integration
            let api = null;
            let seconds = 0;

            // Timer
            const timerInterval = setInterval(() => {
                seconds++;
                const hrs = String(Math.floor(seconds / 3600)).padStart(2, '0');
                const mins = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
                const secs = String(seconds % 60).padStart(2, '0');
                const timerEl = document.getElementById('timer');
                if (timerEl) {
                    timerEl.textContent = `${hrs}:${mins}:${secs}`;
                }
            }, 1000);

            // Initialize Jitsi Meet
            function initJitsiMeet() {
                const domain = '<?php echo e($jitsiDomain); ?>';
                const roomName = '<?php echo e($roomName); ?>';

                const options = {
                    roomName: roomName,
                    width: '100%',
                    height: '100%',
                    parentNode: document.querySelector('#jitsi-meet-container'),
                    configOverwrite: {
                        startWithAudioMuted: false,
                        startWithVideoMuted: false,
                        disableDeepLinking: true,
                        enableWelcomePage: false,
                        prejoinPageEnabled: <?php echo e($settings->enable_waiting_room ? 'false' : 'true'); ?>,
                        enableNoisyDetection: false,
                        enableRecording: <?php echo e($settings->enable_recording ? 'true' : 'false'); ?>,
                    },
                    interfaceConfigOverwrite: {
                        TOOLBAR_BUTTONS: [
                            'microphone', 'camera', 'closedcaptions', 'desktop',
                            'fullscreen', 'fodeviceselection', 'hangup', 'profile',
                            <?php if($settings->enable_chat): ?>
                                'chat',
                            <?php endif; ?>
                            <?php if($settings->enable_recording): ?>
                                'recording',
                            <?php endif; ?>
                            'livestreaming', 'etherpad',
                            'sharedvideo', 'settings', 'raisehand', 'videoquality',
                            'filmstrip', 'feedback', 'stats', 'shortcuts', 'tileview',
                            'download', 'help', 'mute-everyone', 'security'
                        ],
                        SHOW_JITSI_WATERMARK: false,
                        SHOW_WATERMARK_FOR_GUESTS: false,
                        DEFAULT_BACKGROUND: '#0f172a',
                    },
                    userInfo: {
                        displayName: '<?php echo e(auth()->user()->name); ?>',
                        email: '<?php echo e(auth()->user()->email); ?>'
                    }
                };

                api = new JitsiMeetExternalAPI(domain, options);

                // Event listeners
                api.addEventListeners({
                    videoConferenceJoined: (e) => {
                        console.log('Joined conference', e);
                        // Update consultation status to 'in_progress'
                        fetch('<?php echo e(route('healthcare.telemedicine.start', $consultation->id)); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            }
                        });
                    },
                    videoConferenceLeft: (e) => {
                        console.log('Left conference', e);
                        clearInterval(timerInterval);
                        // Redirect to feedback or consultation summary
                        window.location.href = '<?php echo e(route('healthcare.telemedicine.consultations')); ?>';
                    },
                    recordingStatusChanged: (e) => {
                        console.log('Recording status changed', e);
                        // Handle recording status if needed
                    },
                    participantJoined: (e) => {
                        console.log('Participant joined', e);
                        // Hide waiting state
                        const waitingState = document.getElementById('waitingState');
                        if (waitingState) {
                            waitingState.style.display = 'none';
                        }
                    }
                });
            }

            // Initialize on load
            window.addEventListener('load', initJitsiMeet);
        </script>
    <?php endif; ?>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\video-room.blade.php ENDPATH**/ ?>