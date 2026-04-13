<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Privacy Policy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Privacy Policy</h1>
                    <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F d, Y') }}</p>

                    <div class="prose dark:prose-invert max-w-none space-y-6">
                        <section>
                            <h2 class="text-2xl font-semibold mb-3">1. Information We Collect</h2>
                            <p>We collect information you provide directly to us, including but not limited to your
                                name, email address, phone number, and business information when you create an account
                                or contact us.</p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-semibold mb-3">2. How We Use Your Information</h2>
                            <p>We use the information we collect to:</p>
                            <ul class="list-disc list-inside space-y-2">
                                <li>Provide, maintain, and improve our services</li>
                                <li>Process your transactions and send related information</li>
                                <li>Send you technical notices and support messages</li>
                                <li>Respond to your comments and questions</li>
                                <li>Comply with legal obligations</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-semibold mb-3">3. Data Security</h2>
                            <p>We implement appropriate technical and organizational measures to protect your personal
                                data against unauthorized access, alteration, disclosure, or destruction.</p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-semibold mb-3">4. Contact Us</h2>
                            <p>If you have questions about this Privacy Policy, please contact us at:</p>
                            <p class="mt-2">
                                <strong>Email:</strong> privacy@qalcuity.com<br>
                                <strong>Phone:</strong> +62 816-5493-2383
                            </p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
