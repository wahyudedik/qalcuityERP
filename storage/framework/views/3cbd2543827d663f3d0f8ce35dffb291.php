<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> SOAP Note - <?php echo e($visit->patient->full_name); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.emr.dashboard', $visit->patient_id)); ?>"
                class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                Back to Dashboard
            </a>
    </div>

    <div class="max-w-6xl mx-auto">
        <form action="<?php echo e(route('healthcare.emr.soap-note.store', $visit)); ?>" method="POST" id="soapForm">
            <?php echo csrf_field(); ?>

            
            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 rounded-2xl p-6 mb-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold"><?php echo e($visit->patient->full_name); ?></h3>
                        <p class="text-sm text-white/80">
                            MRN: <?php echo e($visit->patient->medical_record_number); ?> |
                            Age: <?php echo e($visit->patient->birth_date ? $visit->patient->birth_date->age : 'N/A'); ?> |
                            Gender: <?php echo e(ucfirst($visit->patient->gender)); ?>

                        </p>
                    </div>
                    <div id="autoSaveStatus" class="text-xs text-white/60">
                        Auto-save: Off
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <span
                                    class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-600">S</span>
                                Subjective
                            </h3>
                            <button type="button" onclick="startVoiceInput('chief_complaint')"
                                class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                                Voice Input
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Chief Complaint *
                                </label>
                                <textarea name="subjective[chief_complaint]" id="chief_complaint" rows="3" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Patient's primary complaint..."><?php echo e(old('subjective.chief_complaint')); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    History of Present Illness
                                </label>
                                <textarea name="subjective[history_of_present_illness]" rows="4"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Detailed history of the present condition..."><?php echo e(old('subjective.history_of_present_illness')); ?></textarea>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span
                                class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-sm font-bold text-green-600">O</span>
                            Objective
                        </h3>

                        <div class="space-y-4">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Vital Signs
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Temperature
                                            (°C)</label>
                                        <input type="number" step="0.1" name="objective[vital_signs][temperature]"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"
                                            placeholder="36.5">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Blood Pressure</label>
                                        <input type="text" name="objective[vital_signs][blood_pressure]"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"
                                            placeholder="120/80">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Heart Rate</label>
                                        <input type="number" name="objective[vital_signs][heart_rate]"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"
                                            placeholder="72">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Respiratory
                                            Rate</label>
                                        <input type="number" name="objective[vital_signs][respiratory_rate]"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"
                                            placeholder="16">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">SpO2 (%)</label>
                                        <input type="number" name="objective[vital_signs][spo2]"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"
                                            placeholder="98">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Weight (kg)</label>
                                        <input type="number" step="0.1" name="objective[vital_signs][weight]"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"
                                            placeholder="70">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Physical Examination
                                </label>
                                <textarea name="objective[physical_examination]" rows="4"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Physical examination findings..."><?php echo e(old('objective.physical_examination')); ?></textarea>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span
                                class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-sm font-bold text-amber-600">A</span>
                            Assessment
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Diagnosis (ICD-10)
                                </label>
                                <div class="relative">
                                    <input type="text" id="icd10_search"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Search ICD-10 codes..." autocomplete="off">
                                    <div id="icd10_results"
                                        class="hidden absolute z-10 w-full mt-2 bg-white rounded-xl border border-gray-200 shadow-lg max-h-60 overflow-y-auto">
                                        <!-- ICD-10 results will appear here -->
                                    </div>
                                </div>
                                <div id="selected_diagnoses" class="mt-3 flex flex-wrap gap-2">
                                    <!-- Selected diagnoses will appear here -->
                                </div>
                                <input type="hidden" name="assessment[diagnoses]" id="diagnoses_json">
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span
                                class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-sm font-bold text-purple-600">P</span>
                            Plan
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Treatment Plan
                                </label>
                                <textarea name="plan[treatment_plan]" rows="4"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Detailed treatment plan..."><?php echo e(old('plan.treatment_plan')); ?></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Follow-up Date
                                    </label>
                                    <input type="date" name="plan[follow_up_date]"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Follow-up Instructions
                                    </label>
                                    <input type="text" name="plan[follow_up_instructions]"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900"
                                        placeholder="e.g., Return in 1 week">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-6">
                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Previous Records</h3>
                        <div class="space-y-3">
                            <?php $__empty_1 = true; $__currentLoopData = $previousRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($record->record_date->format('d M Y')); ?>

                                    </p>
                                    <p class="text-sm text-gray-900 mt-1 truncate">
                                        <?php echo e($record->chief_complaint); ?>

                                    </p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-sm text-gray-500 text-center py-4">No previous
                                    records</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Drug Interaction Checker
                        </h3>
                        <div class="space-y-3">
                            <div id="medication_list" class="space-y-2">
                                <!-- Added medications will appear here -->
                            </div>
                            <button type="button" onclick="checkInteractions()"
                                class="w-full px-3 py-2 text-sm bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition">
                                Check Interactions
                            </button>
                            <div id="interaction_results" class="hidden">
                                <!-- Interaction results will appear here -->
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <div class="space-y-3">
                            <button type="submit"
                                class="w-full px-4 py-3 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                                Save SOAP Note
                            </button>
                            <a href="<?php echo e(route('healthcare.emr.dashboard', $visit->patient_id)); ?>"
                                class="block w-full px-4 py-3 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 text-center">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            let selectedDiagnoses = [];
            let medications = [];
            let voiceRecognition = null;

            // Voice-to-Text Integration
            function startVoiceInput(targetId) {
                if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                    alert('Voice recognition is not supported in this browser. Please use Chrome or Edge.');
                    return;
                }

                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                voiceRecognition = new SpeechRecognition();
                voiceRecognition.continuous = true;
                voiceRecognition.interimResults = true;
                voiceRecognition.lang = 'en-US';

                const textarea = document.getElementById(targetId);
                let finalTranscript = '';

                voiceRecognition.onstart = function() {
                    document.getElementById('autoSaveStatus').textContent = '🎤 Listening...';
                };

                voiceRecognition.onresult = function(event) {
                    let interimTranscript = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        if (event.results[i].isFinal) {
                            finalTranscript += event.results[i][0].transcript;
                        } else {
                            interimTranscript += event.results[i][0].transcript;
                        }
                    }
                    textarea.value = finalTranscript + interimTranscript;
                };

                voiceRecognition.onerror = function(event) {
                    console.error('Speech recognition error', event.error);
                    document.getElementById('autoSaveStatus').textContent = 'Voice input error';
                };

                voiceRecognition.onend = function() {
                    document.getElementById('autoSaveStatus').textContent = 'Auto-save: Off';
                };

                voiceRecognition.start();

                // Stop after 30 seconds
                setTimeout(() => {
                    if (voiceRecognition) {
                        voiceRecognition.stop();
                    }
                }, 30000);
            }

            // ICD-10 Search with Autocomplete
            let icd10Timeout;
            document.getElementById('icd10_search').addEventListener('input', function(e) {
                clearTimeout(icd10Timeout);
                const query = e.target.value;

                if (query.length < 2) {
                    document.getElementById('icd10_results').classList.add('hidden');
                    return;
                }

                icd10Timeout = setTimeout(() => {
                    fetch(`/healthcare/emr/search-icd10?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            const resultsDiv = document.getElementById('icd10_results');
                            if (data.length === 0) {
                                resultsDiv.classList.add('hidden');
                                return;
                            }

                            resultsDiv.innerHTML = data.map(item => `
                                <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0"
                                    onclick="selectDiagnosis('${item.code}', '${item.description.replace(/'/g, "\\'")}')">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900">${item.code}</span>
                                        <span class="text-xs text-gray-500">ICD-10</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">${item.description}</p>
                                </div>
                            `).join('');
                            resultsDiv.classList.remove('hidden');
                        });
                }, 300);
            });

            function selectDiagnosis(code, description) {
                if (selectedDiagnoses.find(d => d.code === code)) {
                    return;
                }

                selectedDiagnoses.push({
                    code,
                    description
                });
                updateDiagnosesDisplay();
                document.getElementById('icd10_search').value = '';
                document.getElementById('icd10_results').classList.add('hidden');
            }

            function removeDiagnosis(code) {
                selectedDiagnoses = selectedDiagnoses.filter(d => d.code !== code);
                updateDiagnosesDisplay();
            }

            function updateDiagnosesDisplay() {
                const container = document.getElementById('selected_diagnoses');
                container.innerHTML = selectedDiagnoses.map(d => `
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-sm">
                        <span class="font-medium">${d.code}</span>
                        <span class="text-xs">${d.description}</span>
                        <button type="button" onclick="removeDiagnosis('${d.code}')" class="ml-1 hover:text-red-600">×</button>
                    </span>
                `).join('');

                document.getElementById('diagnoses_json').value = JSON.stringify(selectedDiagnoses);
            }

            // Drug Interaction Checker
            function checkInteractions() {
                if (medications.length < 2) {
                    alert('Please add at least 2 medications to check interactions');
                    return;
                }

                fetch('/healthcare/emr/check-drug-interactions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            medications
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('interaction_results');
                        if (!data.has_interactions) {
                            resultsDiv.innerHTML =
                                '<p class="text-sm text-green-600">✓ No interactions found</p>';
                        } else {
                            resultsDiv.innerHTML = data.interactions.map(interaction => `
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-medium text-red-800">
                                    ⚠️ ${interaction.drug1} + ${interaction.drug2}
                                </p>
                                <p class="text-xs text-red-700 mt-1">${interaction.description}</p>
                                <span class="inline-block mt-2 px-2 py-1 text-xs bg-red-600 text-white rounded">${interaction.severity}</span>
                            </div>
                        `).join('');
                        }
                        resultsDiv.classList.remove('hidden');
                    });
            }

            // Close ICD-10 dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#icd10_search')) {
                    document.getElementById('icd10_results').classList.add('hidden');
                }
            });

            // Auto-save draft every 30 seconds
            setInterval(() => {
                const formData = new FormData(document.getElementById('soapForm'));
                const data = Object.fromEntries(formData.entries());
                localStorage.setItem('soap_draft_<?php echo e($visit->id); ?>', JSON.stringify(data));
                document.getElementById('autoSaveStatus').textContent = '✓ Saved';
                setTimeout(() => {
                    document.getElementById('autoSaveStatus').textContent = 'Auto-save: On';
                }, 2000);
            }, 30000);
        </script>
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\emr\soap-note.blade.php ENDPATH**/ ?>