@extends('layouts.app')

@section('title', 'Welcome to Qalcuity ERP - Industry Selection')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
        x-data="industryWizard()">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">🚀 Welcome to Qalcuity ERP</h1>
                <p class="text-lg text-gray-600">Let's set up your workspace in just 3 simple steps</p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                            1</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Select Industry</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">
                            2</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Sample Data</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">
                            3</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Ready!</span>
                    </div>
                </div>
            </div>

            <!-- Step 1: Industry Selection -->
            <div x-show="step === 1" class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">What industry are you in?</h2>
                <p class="text-gray-600 mb-8">We'll customize the setup based on your business type</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Retail -->
                    <button @click="selectIndustry('retail')"
                        :class="selectedIndustry === 'retail' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">🛍️</div>
                        <h3 class="font-bold text-lg mb-2">Retail & POS</h3>
                        <p class="text-sm text-gray-600">Shops, stores, e-commerce with point-of-sale</p>
                    </button>

                    <!-- Restaurant -->
                    <button @click="selectIndustry('restaurant')"
                        :class="selectedIndustry === 'restaurant' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">🍽️</div>
                        <h3 class="font-bold text-lg mb-2">Restaurant & F&B</h3>
                        <p class="text-sm text-gray-600">Restaurants, cafes, bars with table management</p>
                    </button>

                    <!-- Hotel -->
                    <button @click="selectIndustry('hotel')"
                        :class="selectedIndustry === 'hotel' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">🏨</div>
                        <h3 class="font-bold text-lg mb-2">Hotel & Hospitality</h3>
                        <p class="text-sm text-gray-600">Hotels, resorts, guesthouses with booking system</p>
                    </button>

                    <!-- Construction -->
                    <button @click="selectIndustry('construction')"
                        :class="selectedIndustry === 'construction' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">🏗️</div>
                        <h3 class="font-bold text-lg mb-2">Construction</h3>
                        <p class="text-sm text-gray-600">Building projects, contractors, material tracking</p>
                    </button>

                    <!-- Agriculture -->
                    <button @click="selectIndustry('agriculture')"
                        :class="selectedIndustry === 'agriculture' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">🌾</div>
                        <h3 class="font-bold text-lg mb-2">Agriculture</h3>
                        <p class="text-sm text-gray-600">Farming, crop management, weather integration</p>
                    </button>

                    <!-- Manufacturing -->
                    <button @click="selectIndustry('manufacturing')"
                        :class="selectedIndustry === 'manufacturing' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">🏭</div>
                        <h3 class="font-bold text-lg mb-2">Manufacturing</h3>
                        <p class="text-sm text-gray-600">Production, inventory, supply chain management</p>
                    </button>

                    <!-- Services -->
                    <button @click="selectIndustry('services')"
                        :class="selectedIndustry === 'services' ? 'border-blue-500 bg-blue-50' :
                            'border-gray-200 hover:border-blue-300'"
                        class="border-2 rounded-xl p-6 text-left transition-all">
                        <div class="text-4xl mb-3">💼</div>
                        <h3 class="font-bold text-lg mb-2">Professional Services</h3>
                        <p class="text-sm text-gray-600">Consulting, agencies, service-based businesses</p>
                    </button>
                </div>

                <!-- Business Size -->
                <div class="mb-8" x-show="selectedIndustry">
                    <h3 class="font-semibold text-gray-900 mb-4">Business Size</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <button @click="businessSize = 'micro'"
                            :class="businessSize === 'micro' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            class="border-2 rounded-lg p-4 text-center transition-all">
                            <div class="font-bold">Micro</div>
                            <div class="text-xs text-gray-600">1-5 employees</div>
                        </button>
                        <button @click="businessSize = 'small'"
                            :class="businessSize === 'small' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            class="border-2 rounded-lg p-4 text-center transition-all">
                            <div class="font-bold">Small</div>
                            <div class="text-xs text-gray-600">6-20 employees</div>
                        </button>
                        <button @click="businessSize = 'medium'"
                            :class="businessSize === 'medium' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            class="border-2 rounded-lg p-4 text-center transition-all">
                            <div class="font-bold">Medium</div>
                            <div class="text-xs text-gray-600">21-100 employees</div>
                        </button>
                        <button @click="businessSize = 'large'"
                            :class="businessSize === 'large' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            class="border-2 rounded-lg p-4 text-center transition-all">
                            <div class="font-bold">Large</div>
                            <div class="text-xs text-gray-600">100+ employees</div>
                        </button>
                    </div>
                </div>

                <!-- Continue Button -->
                <div class="flex justify-end">
                    <button @click="nextStep()" :disabled="!canProceed"
                        :class="canProceed ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-8 py-3 rounded-lg text-white font-semibold transition-colors">
                        Continue →
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h3 class="text-xl font-semibold mb-2">Setting up your workspace...</h3>
                <p class="text-gray-600">This will only take a moment</p>
            </div>
        </div>
    </div>

    <script>
        function industryWizard() {
            return {
                step: 1,
                selectedIndustry: '',
                businessSize: '',
                employeeCount: null,
                loading: false,

                get canProceed() {
                    return this.selectedIndustry && this.businessSize;
                },

                selectIndustry(industry) {
                    this.selectedIndustry = industry;
                },

                async nextStep() {
                    if (!this.canProceed) return;

                    this.loading = true;

                    try {
                        const response = await fetch('/onboarding/save-industry', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                industry: this.selectedIndustry,
                                business_size: this.businessSize,
                                employee_count: this.employeeCount
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            window.location.href = result.next_step;
                        } else {
                            Dialog.warning('Failed to save industry selection');
                            this.loading = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Dialog.warning('An error occurred. Please try again.');
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection
