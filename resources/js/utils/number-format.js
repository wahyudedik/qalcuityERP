/**
 * Indonesian Number Formatting Utilities
 * 
 * Client-side number formatting for dynamic content (Alpine.js, vanilla JS)
 * Matches server-side NumberHelper formatting
 * 
 * @module utils/number-format
 */

/**
 * Format number with Indonesian thousand separator (titik) and decimal separator (koma)
 * 
 * @param {number|string|null} number - The number to format
 * @param {number} decimals - Number of decimal places (default: 0)
 * @param {boolean} showZero - Show 0 or empty string for null/empty (default: true)
 * @returns {string} Formatted number (e.g., "1.234.567,89")
 * 
 * @example
 * formatNumber(1234567) // "1.234.567"
 * formatNumber(1234.56, 2) // "1.234,56"
 * formatNumber(null, 0, false) // ""
 */
export function formatNumber(number, decimals = 0, showZero = true) {
    if (number === null || number === undefined || number === '') {
        return showZero ? '0' : '';
    }

    // Convert to number if string
    const num = typeof number === 'string' ? parseFloat(number) : number;

    if (isNaN(num)) {
        return showZero ? '0' : '';
    }

    // Split into integer and decimal parts
    const parts = num.toFixed(decimals).split('.');

    // Format integer part with thousand separator (titik)
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // Join with decimal separator (koma)
    return parts.join(',');
}

/**
 * Format as Indonesian Rupiah currency
 * 
 * @param {number|string|null} amount - The amount to format
 * @param {boolean} showSymbol - Show "Rp" prefix (default: true)
 * @returns {string} Formatted currency (e.g., "Rp 1.234.567")
 * 
 * @example
 * formatCurrency(1234567) // "Rp 1.234.567"
 * formatCurrency(1234567, false) // "1.234.567"
 */
export function formatCurrency(amount, showSymbol = true) {
    const formatted = formatNumber(amount, 0);
    return showSymbol ? `Rp ${formatted}` : formatted;
}

/**
 * Format as percentage
 * 
 * @param {number|string|null} number - The percentage value
 * @param {number} decimals - Number of decimal places (default: 2)
 * @returns {string} Formatted percentage (e.g., "12,34%")
 * 
 * @example
 * formatPercentage(12.34) // "12,34%"
 * formatPercentage(12.3456, 4) // "12,3456%"
 */
export function formatPercentage(number, decimals = 2) {
    return `${formatNumber(number, decimals)}%`;
}

/**
 * Format large numbers with Indonesian abbreviations
 * 
 * @param {number|string|null} number - The number to abbreviate
 * @param {number} decimals - Number of decimal places (default: 1)
 * @returns {string} Abbreviated number (e.g., "1,2 Jt")
 * 
 * @example
 * abbreviateNumber(1234567) // "1,2 Jt"
 * abbreviateNumber(1234) // "1,2 Rb"
 * abbreviateNumber(1234567890) // "1,2 M"
 */
export function abbreviateNumber(number, decimals = 1) {
    if (number === null || number === undefined || number === '') {
        return '0';
    }

    const num = typeof number === 'string' ? parseFloat(number) : number;

    if (isNaN(num)) {
        return '0';
    }

    // Miliar (Billion)
    if (num >= 1000000000) {
        return `${formatNumber(num / 1000000000, decimals)} M`;
    }

    // Juta (Million)
    if (num >= 1000000) {
        return `${formatNumber(num / 1000000, decimals)} Jt`;
    }

    // Ribu (Thousand)
    if (num >= 1000) {
        return `${formatNumber(num / 1000, decimals)} Rb`;
    }

    return formatNumber(num, 0);
}

/**
 * Parse Indonesian formatted number back to float
 * 
 * @param {string} formatted - Indonesian formatted number string
 * @returns {number} Parsed float value
 * 
 * @example
 * parseNumber("Rp 1.234.567,89") // 1234567.89
 * parseNumber("1.234,56") // 1234.56
 * parseNumber("12,34%") // 12.34
 */
export function parseNumber(formatted) {
    if (!formatted || typeof formatted !== 'string') {
        return 0;
    }

    // Remove Rp, %, spaces, and thousand separators (titik)
    let cleaned = formatted.replace(/[Rp\s%]/g, '').replace(/\./g, '');

    // Replace decimal separator (koma) with dot
    cleaned = cleaned.replace(',', '.');

    const parsed = parseFloat(cleaned);
    return isNaN(parsed) ? 0 : parsed;
}

/**
 * Format number for input field (on blur)
 * Useful for currency/number inputs that should display formatted
 * 
 * @param {HTMLInputElement} input - The input element
 * @param {number} decimals - Number of decimal places
 * 
 * @example
 * <input type="text" x-on:blur="formatInputNumber($el, 0)">
 */
export function formatInputNumber(input, decimals = 0) {
    if (!input || !input.value) return;

    const parsed = parseNumber(input.value);
    input.value = formatNumber(parsed, decimals);
}

/**
 * Format currency input (on blur)
 * 
 * @param {HTMLInputElement} input - The input element
 * @param {boolean} showSymbol - Show "Rp" prefix
 * 
 * @example
 * <input type="text" x-on:blur="formatInputCurrency($el)">
 */
export function formatInputCurrency(input, showSymbol = false) {
    if (!input || !input.value) return;

    const parsed = parseNumber(input.value);
    input.value = formatCurrency(parsed, showSymbol);
}

/**
 * Alpine.js magic helper for number formatting
 * Usage in Alpine: x-text="$formatNumber(value)"
 */
export function registerAlpineMagics() {
    if (typeof Alpine !== 'undefined') {
        Alpine.magic('formatNumber', () => formatNumber);
        Alpine.magic('formatCurrency', () => formatCurrency);
        Alpine.magic('idr', () => formatCurrency);
        Alpine.magic('formatPercentage', () => formatPercentage);
        Alpine.magic('abbreviateNumber', () => abbreviateNumber);
        Alpine.magic('parseNumber', () => parseNumber);
    }
}

// Auto-register Alpine magics if Alpine is available
if (typeof window !== 'undefined' && typeof Alpine !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        registerAlpineMagics();
    });
}

// Export default object for convenience
export default {
    formatNumber,
    formatCurrency,
    formatPercentage,
    abbreviateNumber,
    parseNumber,
    formatInputNumber,
    formatInputCurrency,
    registerAlpineMagics,

    // Aliases
    idr: formatCurrency,
    number: formatNumber,
    percent: formatPercentage,
    abbr: abbreviateNumber,
    parse: parseNumber,
};

