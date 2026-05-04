<x-app-layout>
    <x-slot name="header">Tanda Tangan Digital</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Document Info --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-white mb-1">Dokumen: {{ class_basename(get_class($model)) }} #{{ $modelId }}</h2>
            <p class="text-sm text-gray-500">Tanda tangani dokumen ini secara digital. Tanda tangan akan disimpan beserta timestamp dan IP address Anda.</p>
        </div>

        {{-- Existing Signatures --}}
        @if($existing->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-white mb-3">Tanda Tangan Sebelumnya</h3>
            <div class="space-y-3">
                @foreach($existing ?? [] as $sig)
                <div class="flex items-center gap-3 p-3 bg-green-500/10 rounded-xl border border-green-500/20">
                    <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $sig->user?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $sig->signed_at?->format('d M Y H:i') }} · {{ $sig->ip_address }}</p>
                    </div>
                    <img src="{{ $sig->signature_data }}" class="ml-auto h-10 border border-gray-200 rounded-lg bg-white" alt="Tanda tangan">
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Signature Pad --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-white mb-3">Tanda Tangan Anda</h3>
            <div class="border-2 border-dashed border-white/20 rounded-xl overflow-hidden bg-white">
                <canvas id="signature-canvas" height="200" class="w-full touch-none cursor-crosshair"></canvas>
            </div>
            <div class="flex gap-2 mt-3">
                <button onclick="clearPad()" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">Hapus</button>
                <button onclick="savePad()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">Simpan Tanda Tangan</button>
            </div>
            <p id="sign-status" class="text-sm mt-2 hidden"></p>
        </div>
    </div>

    @push('scripts')
    <script>
    const canvas  = document.getElementById('signature-canvas');
    const ctx     = canvas.getContext('2d');
    let drawing   = false;
    let lastX = 0, lastY = 0;

    // Set canvas internal resolution to match CSS width
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width  = rect.width  || 600;
        canvas.height = rect.height || 200;
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const src = e.touches ? e.touches[0] : e;
        return [(src.clientX - rect.left) * scaleX, (src.clientY - rect.top) * scaleY];
    }

    canvas.addEventListener('mousedown',  e => { drawing = true; [lastX, lastY] = getPos(e); });
    canvas.addEventListener('mousemove',  e => { if (!drawing) return; draw(e); });
    canvas.addEventListener('mouseup',    () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; [lastX, lastY] = getPos(e); }, { passive: false });
    canvas.addEventListener('touchmove',  e => { e.preventDefault(); if (!drawing) return; draw(e); }, { passive: false });
    canvas.addEventListener('touchend',   () => drawing = false);

    function draw(e) {
        const [x, y] = getPos(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.stroke();
        [lastX, lastY] = [x, y];
    }

    function clearPad() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    async function savePad() {
        const data = canvas.toDataURL('image/png');
        const status = document.getElementById('sign-status');

        const res = await fetch('{{ route("sign.sign", [$modelType, $modelId]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ signature_data: data }),
        });

        const json = await res.json();
        status.classList.remove('hidden');
        if (json.status === 'success') {
            status.className = 'text-sm mt-2 text-green-600';
            status.textContent = '✓ Tanda tangan berhasil disimpan.';
            setTimeout(() => location.reload(), 1500);
        } else {
            status.className = 'text-sm mt-2 text-red-600';
            status.textContent = 'Gagal menyimpan tanda tangan.';
        }
    }
    </script>
    @endpush
</x-app-layout>

