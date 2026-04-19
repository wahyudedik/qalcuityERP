# JavaScript Utilities

## Number Formatting (`number-format.js`)

Indonesian number formatting utilities for client-side dynamic content.

### Usage in Alpine.js

The number formatting utilities are automatically registered as Alpine.js magic helpers:

```html
<!-- Currency -->
<div x-data="{ amount: 1234567 }">
    <p x-text="$formatCurrency(amount)"></p>
    <!-- Output: Rp 1.234.567 -->
</div>

<!-- Number -->
<div x-data="{ quantity: 1234 }">
    <p x-text="$formatNumber(quantity)"></p>
    <!-- Output: 1.234 -->
</div>

<!-- Percentage -->
<div x-data="{ rate: 12.34 }">
    <p x-text="$formatPercentage(rate)"></p>
    <!-- Output: 12,34% -->
</div>

<!-- Abbreviated -->
<div x-data="{ revenue: 1234567 }">
    <p x-text="$abbreviateNumber(revenue)"></p>
    <!-- Output: 1,2 Jt -->
</div>
```

### Usage in Vanilla JavaScript

```javascript
import NumberFormat from './utils/number-format';

// Or use global
const { formatCurrency, formatNumber, formatPercentage } = window.NumberFormat;

// Format currency
const formatted = formatCurrency(1234567);
console.log(formatted); // "Rp 1.234.567"

// Format number
const formatted = formatNumber(1234567);
console.log(formatted); // "1.234.567"

// Format with decimals
const formatted = formatNumber(1234.56, 2);
console.log(formatted); // "1.234,56"

// Format percentage
const formatted = formatPercentage(12.34);
console.log(formatted); // "12,34%"

// Abbreviate
const formatted = abbreviateNumber(1234567);
console.log(formatted); // "1,2 Jt"

// Parse back to number
const parsed = parseNumber("Rp 1.234.567,89");
console.log(parsed); // 1234567.89
```

### Usage in Input Fields

```html
<!-- Auto-format on blur -->
<input 
    type="text" 
    x-data 
    x-on:blur="NumberFormat.formatInputCurrency($el)"
    placeholder="Masukkan jumlah"
>

<!-- Or with Alpine magic -->
<input 
    type="text" 
    x-data="{ value: '' }"
    x-model="value"
    x-on:blur="value = $formatCurrency($parseNumber(value))"
    placeholder="Masukkan jumlah"
>
```

### API Reference

#### `formatNumber(number, decimals = 0, showZero = true)`
Format number with Indonesian thousand separator (titik) and decimal separator (koma).

**Parameters:**
- `number` (number|string|null): The number to format
- `decimals` (number): Number of decimal places (default: 0)
- `showZero` (boolean): Show 0 or empty string for null/empty (default: true)

**Returns:** `string` - Formatted number (e.g., "1.234.567,89")

#### `formatCurrency(amount, showSymbol = true)`
Format as Indonesian Rupiah currency.

**Parameters:**
- `amount` (number|string|null): The amount to format
- `showSymbol` (boolean): Show "Rp" prefix (default: true)

**Returns:** `string` - Formatted currency (e.g., "Rp 1.234.567")

#### `formatPercentage(number, decimals = 2)`
Format as percentage.

**Parameters:**
- `number` (number|string|null): The percentage value
- `decimals` (number): Number of decimal places (default: 2)

**Returns:** `string` - Formatted percentage (e.g., "12,34%")

#### `abbreviateNumber(number, decimals = 1)`
Format large numbers with Indonesian abbreviations.

**Parameters:**
- `number` (number|string|null): The number to abbreviate
- `decimals` (number): Number of decimal places (default: 1)

**Returns:** `string` - Abbreviated number (e.g., "1,2 Jt")

**Abbreviations:**
- `< 1.000`: No abbreviation
- `≥ 1.000`: "Rb" (Ribu / Thousand)
- `≥ 1.000.000`: "Jt" (Juta / Million)
- `≥ 1.000.000.000`: "M" (Miliar / Billion)

#### `parseNumber(formatted)`
Parse Indonesian formatted number back to float.

**Parameters:**
- `formatted` (string): Indonesian formatted number string

**Returns:** `number` - Parsed float value

**Examples:**
```javascript
parseNumber("Rp 1.234.567,89") // 1234567.89
parseNumber("1.234,56") // 1234.56
parseNumber("12,34%") // 12.34
```

#### `formatInputNumber(input, decimals = 0)`
Format number for input field (on blur).

**Parameters:**
- `input` (HTMLInputElement): The input element
- `decimals` (number): Number of decimal places

#### `formatInputCurrency(input, showSymbol = false)`
Format currency input (on blur).

**Parameters:**
- `input` (HTMLInputElement): The input element
- `showSymbol` (boolean): Show "Rp" prefix

### Examples

#### Dynamic Dashboard Stats

```html
<div x-data="{
    revenue: 1234567,
    orders: 1234,
    conversionRate: 12.34,
    avgOrderValue: 987654
}">
    <div class="stat-card">
        <p class="text-sm">Total Revenue</p>
        <p class="text-3xl" x-text="$abbreviateNumber(revenue)"></p>
    </div>
    
    <div class="stat-card">
        <p class="text-sm">Total Orders</p>
        <p class="text-3xl" x-text="$formatNumber(orders)"></p>
    </div>
    
    <div class="stat-card">
        <p class="text-sm">Conversion Rate</p>
        <p class="text-3xl" x-text="$formatPercentage(conversionRate)"></p>
    </div>
    
    <div class="stat-card">
        <p class="text-sm">Avg Order Value</p>
        <p class="text-3xl" x-text="$formatCurrency(avgOrderValue)"></p>
    </div>
</div>
```

#### Dynamic Table

```html
<table x-data="{ items: [] }" x-init="fetchItems()">
    <thead>
        <tr>
            <th>Product</th>
            <th class="text-right">Price</th>
            <th class="text-right">Stock</th>
            <th class="text-right">Total Value</th>
        </tr>
    </thead>
    <tbody>
        <template x-for="item in items" :key="item.id">
            <tr>
                <td x-text="item.name"></td>
                <td class="text-right" x-text="$formatCurrency(item.price)"></td>
                <td class="text-right" x-text="$formatNumber(item.stock)"></td>
                <td class="text-right" x-text="$formatCurrency(item.price * item.stock)"></td>
            </tr>
        </template>
    </tbody>
</table>
```

#### Currency Input with Formatting

```html
<div x-data="{ 
    amount: '', 
    displayAmount: '',
    formatOnBlur() {
        const parsed = this.$parseNumber(this.displayAmount);
        this.amount = parsed;
        this.displayAmount = this.$formatCurrency(parsed, false);
    }
}">
    <label>Amount</label>
    <div class="input-group">
        <span class="prefix">Rp</span>
        <input 
            type="text" 
            x-model="displayAmount"
            x-on:blur="formatOnBlur()"
            placeholder="0"
        >
    </div>
    <input type="hidden" name="amount" x-model="amount">
</div>
```

#### Real-time Calculation

```html
<div x-data="{
    quantity: 0,
    price: 0,
    get total() {
        return this.quantity * this.price;
    }
}">
    <div>
        <label>Quantity</label>
        <input type="number" x-model.number="quantity">
    </div>
    
    <div>
        <label>Price</label>
        <input type="number" x-model.number="price">
    </div>
    
    <div class="total">
        <span>Total:</span>
        <span x-text="$formatCurrency(total)"></span>
    </div>
</div>
```

### Testing

```javascript
import { 
    formatNumber, 
    formatCurrency, 
    formatPercentage, 
    abbreviateNumber, 
    parseNumber 
} from './number-format';

// Test formatNumber
console.assert(formatNumber(1234567) === '1.234.567');
console.assert(formatNumber(1234.56, 2) === '1.234,56');

// Test formatCurrency
console.assert(formatCurrency(1234567) === 'Rp 1.234.567');
console.assert(formatCurrency(1234567, false) === '1.234.567');

// Test formatPercentage
console.assert(formatPercentage(12.34) === '12,34%');

// Test abbreviateNumber
console.assert(abbreviateNumber(1234567) === '1,2 Jt');
console.assert(abbreviateNumber(1234) === '1,2 Rb');

// Test parseNumber
console.assert(parseNumber('Rp 1.234.567,89') === 1234567.89);
console.assert(parseNumber('1.234,56') === 1234.56);
```

### Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with polyfills for `Number.toFixed()`)

### Related Documentation

- [Server-side Number Formatting Guide](../../../docs/NUMBER-FORMATTING-GUIDE.md)
- [Migration Example](../../../docs/MIGRATION-EXAMPLE-NUMBER-FORMAT.md)
- [Task 6 UI/UX Audit Summary](../../../.kiro/specs/erp-comprehensive-audit-fix/TASK-6-UI-UX-AUDIT-SUMMARY.md)
