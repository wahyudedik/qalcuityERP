/**
 * FormWizard - Multi-Step Form Wizard System
 *
 * TASK-018: Implements step-by-step form navigation with:
 * - Progress indicator
 * - Step-by-step validation
 * - Save draft functionality
 * - Keyboard navigation
 * - Auto-save to localStorage
 * - Mobile responsive
 *
 * @version 1.0.0
 */

class FormWizard {
    /**
     * Create FormWizard instance
     *
     * @param {HTMLElement} formElement - Form element
     * @param {Object} options - Configuration options
     */
    constructor(formElement, options = {}) {
        this.form = formElement;
        this.currentStep = 1;
        this.totalSteps = parseInt(formElement.dataset.steps) || 1;
        this.steps = [];
        this.validationErrors = new Map();
        this.draftKey = formElement.dataset.draftKey || `wizard_draft_${formElement.id}`;
        this.autoSaveInterval = null;

        // Options
        this.options = {
            enableAutoSave: options.enableAutoSave !== false,
            autoSaveInterval: options.autoSaveInterval || 30000, // 30 seconds
            validateOnNext: options.validateOnNext !== false,
            showProgress: options.showProgress !== false,
            allowStepJump: options.allowStepJump || false,
            onSubmit: options.onSubmit || null,
            onStepChange: options.onStepChange || null,
            ...options
        };

        // Initialize
        this.init();
    }

    /**
     * Initialize wizard
     */
    init() {
        // Discover steps
        this.discoverSteps();

        // Create progress bar
        if (this.options.showProgress) {
            this.createProgressBar();
        }

        // Load draft if exists
        this.loadDraft();

        // Setup event listeners
        this.setupEventListeners();

        // Start auto-save
        if (this.options.enableAutoSave) {
            this.startAutoSave();
        }

        // Render initial step
        this.render();

        console.log(`FormWizard: Initialized with ${this.totalSteps} steps`);
    }

    /**
     * Discover form steps
     */
    discoverSteps() {
        this.steps = [];

        for (let i = 1; i <= this.totalSteps; i++) {
            const stepElement = this.form.querySelector(`[data-step="${i}"]`);
            if (stepElement) {
                this.steps.push({
                    number: i,
                    element: stepElement,
                    title: stepElement.dataset.stepTitle || `Step ${i}`,
                    fields: stepElement.querySelectorAll('input, select, textarea'),
                    requiredFields: stepElement.querySelectorAll('input[required], select[required], textarea[required]')
                });
            }
        }

        if (this.steps.length === 0) {
            console.warn('FormWizard: No steps found. Make sure each step has data-step attribute.');
        }
    }

    /**
     * Create progress bar UI
     */
    createProgressBar() {
        const progressBar = document.createElement('div');
        progressBar.className = 'wizard-progress-bar';
        progressBar.innerHTML = `
            <div class="progress-steps">
                ${this.steps.map(step => `
                    <div class="progress-step" data-step="${step.number}">
                        <div class="step-indicator">
                            <span class="step-number">${step.number}</span>
                            <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="step-label">${step.title}</div>
                    </div>
                    ${step.number < this.totalSteps ? '<div class="step-connector"></div>' : ''}
                `).join('')}
            </div>
        `;

        // Insert before form
        this.form.parentNode.insertBefore(progressBar, this.form);
        this.progressBar = progressBar;

        // Add click handlers for step jumping
        if (this.options.allowStepJump) {
            progressBar.querySelectorAll('.progress-step').forEach(el => {
                el.addEventListener('click', () => {
                    const step = parseInt(el.dataset.step);
                    this.goToStep(step);
                });
            });
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Next button
        this.form.addEventListener('click', (e) => {
            if (e.target.matches('[data-wizard-next]')) {
                e.preventDefault();
                this.next();
            }

            if (e.target.matches('[data-wizard-prev]')) {
                e.preventDefault();
                this.prev();
            }

            if (e.target.matches('[data-wizard-save-draft]')) {
                e.preventDefault();
                this.saveDraft();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' && e.ctrlKey) {
                e.preventDefault();
                this.next();
            }
            if (e.key === 'ArrowLeft' && e.ctrlKey) {
                e.preventDefault();
                this.prev();
            }
        });

        // Form submit
        this.form.addEventListener('submit', (e) => {
            if (this.currentStep < this.totalSteps) {
                e.preventDefault();
                this.next();
            } else if (this.options.onSubmit) {
                e.preventDefault();
                this.options.onSubmit(this);
            }
        });
    }

    /**
     * Go to next step
     */
    next() {
        if (this.currentStep >= this.totalSteps) {
            return false;
        }

        // Validate current step
        if (this.options.validateOnNext) {
            const isValid = this.validateCurrentStep();
            if (!isValid) {
                this.showValidationErrors();
                return false;
            }
        }

        // Clear errors
        this.validationErrors.clear();

        // Move to next step
        this.currentStep++;
        this.render();
        this.scrollToTop();

        // Trigger callback
        if (this.options.onStepChange) {
            this.options.onStepChange(this.currentStep, this.totalSteps);
        }

        return true;
    }

    /**
     * Go to previous step
     */
    prev() {
        if (this.currentStep <= 1) {
            return false;
        }

        this.currentStep--;
        this.render();
        this.scrollToTop();

        // Trigger callback
        if (this.options.onStepChange) {
            this.options.onStepChange(this.currentStep, this.totalSteps);
        }

        return true;
    }

    /**
     * Go to specific step
     */
    goToStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > this.totalSteps) {
            return false;
        }

        // Validate all steps up to target if jumping forward
        if (stepNumber > this.currentStep && this.options.validateOnNext) {
            for (let i = this.currentStep; i < stepNumber; i++) {
                const step = this.steps[i - 1];
                if (!this.validateStep(step)) {
                    this.currentStep = i;
                    this.render();
                    return false;
                }
            }
        }

        this.currentStep = stepNumber;
        this.render();
        this.scrollToTop();

        if (this.options.onStepChange) {
            this.options.onStepChange(this.currentStep, this.totalSteps);
        }

        return true;
    }

    /**
     * Validate current step
     */
    validateCurrentStep() {
        const currentStepData = this.steps[this.currentStep - 1];
        if (!currentStepData) return true;

        return this.validateStep(currentStepData);
    }

    /**
     * Validate specific step
     */
    validateStep(step) {
        let isValid = true;
        this.validationErrors.clear();

        step.requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Validate individual field
     */
    validateField(field) {
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (field.required && !field.value.trim()) {
            isValid = false;
            errorMessage = 'Field ini wajib diisi';
        }

        // Email validation
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                isValid = false;
                errorMessage = 'Format email tidak valid';
            }
        }

        // Number validation
        if (field.type === 'number' && field.value) {
            const num = parseFloat(field.value);
            if (isNaN(num)) {
                isValid = false;
                errorMessage = 'Harus berupa angka';
            }
            if (field.min !== undefined && num < parseFloat(field.min)) {
                isValid = false;
                errorMessage = `Nilai minimum: ${field.min}`;
            }
            if (field.max !== undefined && num > parseFloat(field.max)) {
                isValid = false;
                errorMessage = `Nilai maksimum: ${field.max}`;
            }
        }

        // Store error
        if (!isValid) {
            this.validationErrors.set(field.name || field.id, errorMessage);
            field.classList.add('wizard-error');
        } else {
            field.classList.remove('wizard-error');
        }

        return isValid;
    }

    /**
     * Show validation errors
     */
    showValidationErrors() {
        // Remove existing error messages
        this.form.querySelectorAll('.wizard-error-message').forEach(el => el.remove());

        // Show errors for current step
        this.validationErrors.forEach((message, fieldName) => {
            const field = this.form.querySelector(`[name="${fieldName}"], #${fieldName}`);
            if (field) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'wizard-error-message';
                errorDiv.textContent = message;
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
        });

        // Scroll to first error
        const firstError = this.form.querySelector('.wizard-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }

    /**
     * Render current step
     */
    render() {
        // Hide all steps
        this.steps.forEach(step => {
            step.element.style.display = 'none';
            step.element.classList.remove('wizard-step-active');
        });

        // Show current step
        const currentStepData = this.steps[this.currentStep - 1];
        if (currentStepData) {
            currentStepData.element.style.display = 'block';
            currentStepData.element.classList.add('wizard-step-active');
        }

        // Update progress bar
        if (this.progressBar) {
            this.updateProgressBar();
        }

        // Update navigation buttons
        this.updateNavigationButtons();

        // Save draft
        this.saveDraft();
    }

    /**
     * Update progress bar
     */
    updateProgressBar() {
        this.progressBar.querySelectorAll('.progress-step').forEach((el, index) => {
            const stepNumber = index + 1;
            el.classList.remove('step-completed', 'step-active');

            if (stepNumber < this.currentStep) {
                el.classList.add('step-completed');
            } else if (stepNumber === this.currentStep) {
                el.classList.add('step-active');
            }
        });

        // Update connectors
        this.progressBar.querySelectorAll('.step-connector').forEach((el, index) => {
            if (index + 1 < this.currentStep) {
                el.classList.add('connector-completed');
            } else {
                el.classList.remove('connector-completed');
            }
        });
    }

    /**
     * Update navigation buttons
     */
    updateNavigationButtons() {
        const prevBtn = this.form.querySelector('[data-wizard-prev]');
        const nextBtn = this.form.querySelector('[data-wizard-next]');
        const submitBtn = this.form.querySelector('[type="submit"]');

        if (prevBtn) {
            prevBtn.style.display = this.currentStep === 1 ? 'none' : 'inline-block';
        }

        if (nextBtn) {
            nextBtn.style.display = this.currentStep === this.totalSteps ? 'none' : 'inline-block';
        }

        if (submitBtn) {
            submitBtn.style.display = this.currentStep === this.totalSteps ? 'inline-block' : 'none';
        }
    }

    /**
     * Save draft to localStorage
     */
    saveDraft() {
        if (!this.options.enableAutoSave) return;

        try {
            const formData = new FormData(this.form);
            const data = {};

            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            data._wizardStep = this.currentStep;
            data._wizardTimestamp = new Date().toISOString();

            localStorage.setItem(this.draftKey, JSON.stringify(data));

            // Show save indicator
            this.showSaveIndicator();
        } catch (e) {
            console.warn('FormWizard: Failed to save draft', e);
        }
    }

    /**
     * Load draft from localStorage
     */
    async loadDraft() {
        try {
            const saved = localStorage.getItem(this.draftKey);
            if (!saved) return;

            const data = JSON.parse(saved);

            // Ask user if they want to restore
            if (data._wizardTimestamp) {
                const savedDate = new Date(data._wizardTimestamp);
                const hoursAgo = Math.floor((Date.now() - savedDate.getTime()) / (1000 * 60 * 60));

                let message = 'Ada draft yang tersimpan.';
                if (hoursAgo > 0) {
                    message += ` Disimpan ${hoursAgo} jam yang lalu.`;
                }
                message += ' Muat draft?';

                const confirmed = await Dialog.confirm(message);
                if (!confirmed) {
                    this.clearDraft();
                    return;
                }
            }

            // Restore form data
            Object.keys(data).forEach(key => {
                if (key.startsWith('_wizard')) return;

                const field = this.form.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = data[key];
                    // Trigger change events
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            // Restore step
            if (data._wizardStep) {
                this.currentStep = data._wizardStep;
            }

            console.log('FormWizard: Draft loaded');
        } catch (e) {
            console.warn('FormWizard: Failed to load draft', e);
        }
    }

    /**
     * Clear draft
     */
    clearDraft() {
        localStorage.removeItem(this.draftKey);
    }

    /**
     * Start auto-save interval
     */
    startAutoSave() {
        this.autoSaveInterval = setInterval(() => {
            this.saveDraft();
        }, this.options.autoSaveInterval);
    }

    /**
     * Stop auto-save
     */
    stopAutoSave() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
            this.autoSaveInterval = null;
        }
    }

    /**
     * Show save indicator
     */
    showSaveIndicator() {
        let indicator = document.querySelector('.wizard-save-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'wizard-save-indicator';
            indicator.textContent = 'Draft tersimpan';
            document.body.appendChild(indicator);
        }

        indicator.classList.add('show');
        setTimeout(() => {
            indicator.classList.remove('show');
        }, 2000);
    }

    /**
     * Scroll to top of form
     */
    scrollToTop() {
        this.form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Get form data
     */
    getData() {
        const formData = new FormData(this.form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    }

    /**
     * Destroy wizard
     */
    destroy() {
        this.stopAutoSave();
        this.clearDraft();

        // Show all steps
        this.steps.forEach(step => {
            step.element.style.display = 'block';
        });

        // Remove progress bar
        if (this.progressBar) {
            this.progressBar.remove();
        }
    }
}

// Auto-initialize wizards
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-wizard]').forEach(form => {
        new FormWizard(form);
    });
});

// Make globally available
window.FormWizard = FormWizard;

export default FormWizard;
