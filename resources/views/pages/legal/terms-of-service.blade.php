<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Terms of Service') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Terms of Service</h1>
                    <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F d, Y') }}</p>

                    <div class="prose dark:prose-invert max-w-none space-y-6">
                        <section>
                            <h2 class="text-2xl font-semibold mb-3">1. Acceptance of Terms</h2>
                            <p>By accessing or using Qalcuity ERP, you agree to be bound by these Terms of Service and
                                all applicable laws and regulations.</p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-semibold mb-3">2. Use License</h2>
                            <p>Permission is granted to temporarily use Qalcuity ERP for personal or business purposes.
                                This is the grant of a license, not a transfer of title.</p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-semibold mb-3">3. Subscription and Payment</h2>
                            <p>Access to Qalcuity ERP requires a subscription. All fees are non-refundable unless
                                otherwise stated in your subscription agreement.</p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-semibold mb-3">4. Contact</h2>
                            <p>For questions about these terms, contact us at legal@qalcuity.com</p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
