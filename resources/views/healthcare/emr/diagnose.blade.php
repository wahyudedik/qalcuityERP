<x-app-layout>
    <x-slot name="header">Input Diagnosa - {{ $visit->patient?->full_name ?? 'Pasien' }}</x-slot>

    <div class="max-w-4xl mx-auto">
        {{-- Patient Info Banner --}}
        <div class="bg-gradient-to-r from-red-500 to-orange-600 rounded-2xl p-6 mb-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold">{{ $visit->patient?->full_name ?? '-' }}</h2>
                    <p class="text-sm text-white/80">RM: {{ $visit->patient?->medical_record_number ?? '-' }} |
                        {{ $visit->chief_complaint ?? 'Tidak ada keluhan' }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('healthcare.emr.diagnoses.store', $visit) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Diagnosis Entry --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Entry Diagnosa ICD-10</h3>
                    <button type="button" onclick="addDiagnosis()"
                        class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        + Tambah Diagnosa
                    </button>
                </div>
                <div class="p-6">
                    <div id="diagnosis-list" class="space-y-6">
                        {{-- Diagnosis Item 1 --}}
                        <div
                            class="diagnosis-item p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900">Diagnosa #1</h4>
                                    <span class="text-xs text-gray-500">(Wajib)</span>
                                </div>
                                <button type="button" onclick="removeDiagnosis(this)"
                                    class="text-red-600 hover:text-red-700 hidden">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Cari Kode ICD-10 <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="diagnoses[0][icd_code]" id="icd-search-0" required
                                            placeholder="Ketik kode atau nama penyakit..."
                                            oninput="searchICD10(this.value, 0)"
                                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <div id="icd-suggestions-0"
                                            class="absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto hidden">
                                            <!-- ICD-10 suggestions will appear here -->
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Diagnosa
                                    </label>
                                    <input type="text" name="diagnoses[0][description]" id="icd-description-0"
                                        readonly placeholder="Otomatis terisi saat memilih kode ICD-10"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-900">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipe Diagnosa <span class="text-red-500">*</span>
                                    </label>
                                    <select name="diagnoses[0][type]" required
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Pilih Tipe</option>
                                        <option value="primary">Diagnosa Utama (Primary)</option>
                                        <option value="secondary">Diagnosa Sekunder (Secondary)</option>
                                        <option value="working">Diagnosa Kerja (Working)</option>
                                        <option value="differential">Diagnosa Banding (Differential)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Status
                                    </label>
                                    <select name="diagnoses[0][status]"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="confirmed">Confirmed (Dikonfirmasi)</option>
                                        <option value="provisional">Provisional (Sementara)</option>
                                        <option value="ruled_out">Ruled Out (Disingkirkan)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Catatan Klinis
                                    </label>
                                    <textarea name="diagnoses[0][notes]" rows="2" placeholder="Catatan tambahan terkait diagnosa..."
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clinical Notes --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Catatan Klinis</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Assessment / Rencana Pengobatan
                            </label>
                            <textarea name="clinical_notes" rows="4" placeholder="Tulis assessment dan rencana pengobatan..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tindak Lanjut
                            </label>
                            <select name="follow_up"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Tindak Lanjut</option>
                                <option value="follow_up_needed">Perlu Follow-up</option>
                                <option value="refer_to_specialist">Rujuk ke Spesialis</option>
                                <option value="admission">Rawat Inap</option>
                                <option value="discharge">Pulang / Selesai</option>
                                <option value="lab_test">Perlu Pemeriksaan Lab</option>
                                <option value="radiology">Perlu Pemeriksaan Radiologi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Follow-up
                            </label>
                            <input type="datetime-local" name="follow_up_date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="complete_visit" value="1"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Tandai kunjungan selesai</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('healthcare.emr.show', $visit) }}"
                    class="px-6 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Simpan Diagnosa
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let diagnosisCount = 1;

            // ICD-10 Sample Data (In production, this should come from API/database)
            const icd10Data = [{
                    code: 'A00',
                    description: 'Cholera'
                },
                {
                    code: 'A01',
                    description: 'Typhoid and paratyphoid fevers'
                },
                {
                    code: 'A09',
                    description: 'Diarrhoea and gastro-enteritis of presumed infectious origin'
                },
                {
                    code: 'B34',
                    description: 'Viral infection of unspecified site'
                },
                {
                    code: 'I10',
                    description: 'Essential (primary) hypertension'
                },
                {
                    code: 'I11',
                    description: 'Hypertensive heart disease'
                },
                {
                    code: 'J00',
                    description: 'Acute nasopharyngitis [common cold]'
                },
                {
                    code: 'J06',
                    description: 'Acute upper respiratory infections of multiple and unspecified sites'
                },
                {
                    code: 'J18',
                    description: 'Pneumonia, unspecified organism'
                },
                {
                    code: 'J20',
                    description: 'Acute bronchitis'
                },
                {
                    code: 'J22',
                    description: 'Unspecified acute lower respiratory infection'
                },
                {
                    code: 'E11',
                    description: 'Non-insulin-dependent diabetes mellitus'
                },
                {
                    code: 'E78',
                    description: 'Disorders of lipoprotein metabolism and other lipidaemias'
                },
                {
                    code: 'K21',
                    description: 'Gastro-oesophageal reflux disease'
                },
                {
                    code: 'K29',
                    description: 'Gastritis and duodenitis'
                },
                {
                    code: 'K30',
                    description: 'Functional dyspepsia'
                },
                {
                    code: 'M54',
                    description: 'Dorsalgia'
                },
                {
                    code: 'M79',
                    description: 'Other soft tissue disorders, not elsewhere classified'
                },
                {
                    code: 'N39',
                    description: 'Other disorders of urinary system'
                },
                {
                    code: 'R05',
                    description: 'Cough'
                },
                {
                    code: 'R06',
                    description: 'Abnormalities of breathing'
                },
                {
                    code: 'R10',
                    description: 'Abdominal and pelvic pain'
                },
                {
                    code: 'R11',
                    description: 'Nausea and vomiting'
                },
                {
                    code: 'R50',
                    description: 'Fever of other and unknown origin'
                },
                {
                    code: 'R51',
                    description: 'Headache'
                },
                {
                    code: 'R52',
                    description: 'Pain, not elsewhere classified'
                },
            ];

            function searchICD10(query, index) {
                const suggestionsDiv = document.getElementById(`icd-suggestions-${index}`);

                if (!query || query.length < 2) {
                    suggestionsDiv.classList.add('hidden');
                    return;
                }

                const filtered = icd10Data.filter(item =>
                    item.code.toLowerCase().includes(query.toLowerCase()) ||
                    item.description.toLowerCase().includes(query.toLowerCase())
                );

                if (filtered.length === 0) {
                    suggestionsDiv.classList.add('hidden');
                    return;
                }

                suggestionsDiv.innerHTML = filtered.map(item => `
                <div onclick="selectICD10('${item.code}', '${item.description}', ${index})"
                    class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-sm font-semibold text-blue-600">${item.code}</span>
                        <span class="text-sm text-gray-700">${item.description}</span>
                    </div>
                </div>
            `).join('');

                suggestionsDiv.classList.remove('hidden');
            }

            function selectICD10(code, description, index) {
                document.getElementById(`icd-search-${index}`).value = code;
                document.getElementById(`icd-description-${index}`).value = description;
                document.getElementById(`icd-suggestions-${index}`).classList.add('hidden');
            }

            // Close suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.matches('input[id^="icd-search-"]')) {
                    document.querySelectorAll('[id^="icd-suggestions-"]').forEach(el => {
                        el.classList.add('hidden');
                    });
                }
            });

            function addDiagnosis() {
                const template = document.querySelector('.diagnosis-item').cloneNode(true);
                diagnosisCount++;

                // Update all field names and IDs
                template.querySelectorAll('input, select, textarea').forEach(field => {
                    const name = field.getAttribute('name');
                    const id = field.getAttribute('id');

                    if (name) {
                        field.setAttribute('name', name.replace(/\[\d+\]/, `[${diagnosisCount - 1}]`));
                        if (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA') {
                            field.value = '';
                        } else if (field.tagName === 'SELECT') {
                            field.selectedIndex = 0;
                        }
                    }

                    if (id) {
                        field.setAttribute('id', id.replace(/\d+$/, `${diagnosisCount - 1}`));
                    }

                    // Remove oninput handler temporarily
                    if (field.getAttribute('oninput')) {
                        field.setAttribute('oninput', `searchICD10(this.value, ${diagnosisCount - 1})`);
                    }
                });

                // Update label
                const label = template.querySelector('h4');
                if (label) {
                    label.textContent = `Diagnosa #${diagnosisCount}`;
                }

                // Show remove button for additional items
                const removeBtn = template.querySelector('button[onclick="removeDiagnosis(this)"]');
                if (removeBtn) {
                    removeBtn.classList.remove('hidden');
                }

                // Remove the "Wajib" text for additional items
                const wajibText = template.querySelector('.text-xs');
                if (wajibText) {
                    wajibText.remove();
                }

                document.getElementById('diagnosis-list').appendChild(template);
            }

            function removeDiagnosis(button) {
                const items = document.querySelectorAll('.diagnosis-item');
                if (items.length > 1) {
                    button.closest('.diagnosis-item').remove();
                } else {
                    alert('Minimal harus ada 1 diagnosa');
                }
            }
        </script>
    @endpush
</x-app-layout>
