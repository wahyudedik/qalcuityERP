import { marked } from 'marked';
import DOMPurify from 'dompurify';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// ── Marked config ─────────────────────────────────────────────
marked.setOptions({ breaks: true, gfm: true });

marked.use({
    renderer: {
        table(token) {
            const headerCells = token.header.map(cell =>
                `<th class="px-4 py-2.5 text-left text-xs uppercase tracking-wide text-gray-500 font-semibold bg-gray-50">${this.parser.parseInline(cell.tokens)}</th>`
            ).join('');
            const rows = token.rows.map((row, i) => {
                const cells = row.map(cell =>
                    `<td class="px-4 py-2.5 text-gray-700 text-sm">${this.parser.parseInline(cell.tokens)}</td>`
                ).join('');
                return `<tr class="${i % 2 === 0 ? '' : 'bg-gray-50/50'} hover:bg-blue-50/30 transition">${cells}</tr>`;
            }).join('');
            return `<div class="overflow-x-auto my-3 rounded-xl border border-gray-200 shadow-sm">
  <table class="min-w-full text-sm">
    <thead><tr>${headerCells}</tr></thead>
    <tbody class="divide-y divide-gray-100 bg-white">${rows}</tbody>
  </table>
</div>`;
        },
        code(token) {
            const lang = token.lang || 'code';
            const escaped = token.text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return `<div class="my-2 rounded-xl overflow-hidden border border-gray-200">
  <div class="bg-gray-800 px-4 py-2 text-xs text-gray-400 flex items-center justify-between">
    <span>${lang}</span>
    <button onclick="navigator.clipboard.writeText(this.closest('.my-2').querySelector('code').textContent)" class="text-gray-500 hover:text-white transition text-xs">Salin</button>
  </div>
  <pre class="bg-gray-900 text-green-400 px-4 py-3 text-xs overflow-x-auto"><code>${escaped}</code></pre>
</div>`;
        },
        blockquote(token) {
            return `<blockquote class="border-l-4 border-blue-400 pl-4 my-2 text-gray-600 italic bg-blue-50/40 py-2 rounded-r-lg">${this.parser.parse(token.tokens)}</blockquote>`;
        },
        list(token) {
            const tag = token.ordered ? 'ol' : 'ul';
            const cls = token.ordered ? 'list-decimal' : 'list-disc';
            const items = token.items.map(item => `<li class="text-sm text-gray-700">${this.parser.parse(item.tokens)}</li>`).join('');
            return `<${tag} class="${cls} pl-5 my-2 space-y-1">${items}</${tag}>`;
        },
        heading(token) {
            const sizes = { 1: 'text-lg', 2: 'text-base', 3: 'text-sm' };
            const text = this.parser.parseInline(token.tokens);
            return `<h${token.depth} class="${sizes[token.depth] ?? 'text-sm'} font-semibold text-gray-800 mt-4 mb-1.5">${text}</h${token.depth}>`;
        },
        strong(token) { return `<strong class="font-semibold text-gray-900">${this.parser.parseInline(token.tokens)}</strong>`; },
        em(token) { return `<em class="italic text-gray-600">${this.parser.parseInline(token.tokens)}</em>`; },
        hr() { return `<hr class="my-3 border-gray-200">`; },
    }
});

// ── State ─────────────────────────────────────────────────────
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const msgBox = document.getElementById('chat-messages');
const input = document.getElementById('chat-input');
const btnSend = document.getElementById('btn-send');
const typingEl = document.getElementById('typing-indicator');
const modelLabel = document.getElementById('model-label');
const chatTitle = document.getElementById('chat-title');
const sessionList = document.getElementById('session-list');
const fileInput = document.getElementById('file-input');
const filePreviewStrip = document.getElementById('file-preview-strip');

let currentSessionId = null;
let isLoading = false;
let chartCounter = 0;
let pendingFiles = []; // Files queued for next send

// ── Markdown parse ────────────────────────────────────────────
function parseMarkdown(text) {
    return DOMPurify.sanitize(marked.parse(text), { ADD_ATTR: ['class', 'onclick'] });
}

// ── Rich Renderer ─────────────────────────────────────────────
// Detects special blocks in AI response and renders them richly.
// Supported: ```chart, ```grid, ```kpi, ```letter, ```invoice, ```print

function renderRichContent(text) {
    const container = document.createElement('div');
    container.className = 'space-y-3';

    // Split by special fenced blocks: ```type\n...\n```
    const parts = text.split(/(```(?:chart|grid|kpi|letter|invoice|print|actions)[^\n]*\n[\s\S]*?```)/g);

    parts.forEach(part => {
        const fenceMatch = part.match(/^```(chart|grid|kpi|letter|invoice|print|actions)([^\n]*)\n([\s\S]*?)```$/);
        if (fenceMatch) {
            const [, type, meta, body] = fenceMatch;
            const block = renderSpecialBlock(type.trim(), meta.trim(), body.trim());
            if (block) { container.appendChild(block); return; }
        }
        // Normal markdown
        if (part.trim()) {
            const div = document.createElement('div');
            div.className = 'prose-chat text-sm text-gray-800 leading-relaxed';
            div.innerHTML = parseMarkdown(part);
            container.appendChild(div);
        }
    });

    return container;
}

function renderSpecialBlock(type, meta, body) {
    switch (type) {
        case 'chart': return renderChart(meta, body);
        case 'grid': return renderGrid(body);
        case 'kpi': return renderKpi(body);
        case 'letter': return renderLetter(body);
        case 'invoice': return renderInvoice(body);
        case 'print': return renderPrintable(meta, body);
        case 'actions': return renderActions(body);
        default: return null;
    }
}

// ── Chart renderer ────────────────────────────────────────────
function renderChart(meta, body) {
    let cfg;
    try { cfg = JSON.parse(body); } catch { return null; }

    const id = 'chart-' + (++chartCounter);
    const wrap = document.createElement('div');
    wrap.className = 'bg-white rounded-2xl border border-gray-200 p-4 shadow-sm my-2';

    const title = cfg.title || meta || 'Grafik';
    wrap.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-gray-800">${title}</p>
            <button onclick="downloadChart('${id}')" class="text-xs text-blue-600 hover:underline">Unduh</button>
        </div>
        <div style="position:relative;height:${cfg.height || 220}px">
            <canvas id="${id}"></canvas>
        </div>`;

    // Render chart after DOM insert
    requestAnimationFrame(() => {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const defaults = {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { size: 11, family: 'Inter' }, boxWidth: 10, usePointStyle: true } } },
            scales: cfg.type === 'pie' || cfg.type === 'doughnut' ? {} : {
                y: { ticks: { font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } },
            },
        };
        new Chart(canvas, {
            type: cfg.type || 'bar',
            data: cfg.data,
            options: { ...defaults, ...(cfg.options || {}) },
        });
    });

    return wrap;
}

window.downloadChart = function (id) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    const a = document.createElement('a');
    a.download = 'chart.png';
    a.href = canvas.toDataURL();
    a.click();
};

// ── Grid renderer ─────────────────────────────────────────────
function renderGrid(body) {
    let cfg;
    try { cfg = JSON.parse(body); } catch { return null; }

    const wrap = document.createElement('div');
    wrap.className = 'my-2';

    if (cfg.title) {
        const h = document.createElement('p');
        h.className = 'text-sm font-semibold text-gray-800 mb-2';
        h.textContent = cfg.title;
        wrap.appendChild(h);
    }

    const cols = cfg.columns || [];
    const rows = cfg.rows || [];

    const tableWrap = document.createElement('div');
    tableWrap.className = 'overflow-x-auto rounded-xl border border-gray-200 shadow-sm';

    const table = document.createElement('table');
    table.className = 'min-w-full text-sm';

    // Header
    const thead = document.createElement('thead');
    thead.innerHTML = `<tr>${cols.map(c =>
        `<th class="px-4 py-2.5 text-left text-xs uppercase tracking-wide text-gray-500 font-semibold bg-gray-50">${c.label || c}</th>`
    ).join('')}</tr>`;
    table.appendChild(thead);

    // Body
    const tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-gray-100 bg-white';
    rows.forEach((row, i) => {
        const tr = document.createElement('tr');
        tr.className = (i % 2 ? 'bg-gray-50/40' : '') + ' hover:bg-blue-50/30 transition';
        cols.forEach(col => {
            const key = col.key || col;
            const val = row[key] ?? '-';
            const td = document.createElement('td');
            td.className = 'px-4 py-2.5 text-gray-700';

            // Badge support
            if (col.badge) {
                const colors = { success: 'bg-green-100 text-green-700', warning: 'bg-amber-100 text-amber-700', danger: 'bg-red-100 text-red-700', info: 'bg-blue-100 text-blue-700', default: 'bg-gray-100 text-gray-600' };
                const color = col.badge[val] || 'default';
                td.innerHTML = `<span class="text-xs font-medium px-2 py-0.5 rounded-full ${colors[color] || colors.default}">${val}</span>`;
            } else {
                td.textContent = val;
            }
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    tableWrap.appendChild(table);
    wrap.appendChild(tableWrap);

    // Export button
    if (cfg.exportable !== false) {
        const btn = document.createElement('button');
        btn.className = 'mt-2 text-xs text-blue-600 hover:underline';
        btn.textContent = '⬇ Ekspor CSV';
        btn.onclick = () => exportGridCsv(cols, rows, cfg.title || 'data');
        wrap.appendChild(btn);
    }

    return wrap;
}

function exportGridCsv(cols, rows, filename) {
    const headers = cols.map(c => c.label || c).join(',');
    const lines = rows.map(row => cols.map(col => `"${row[col.key || col] ?? ''}"`).join(','));
    const csv = [headers, ...lines].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename.replace(/\s+/g, '_') + '.csv';
    a.click();
}

// ── KPI Cards renderer ────────────────────────────────────────
function renderKpi(body) {
    let cfg;
    try { cfg = JSON.parse(body); } catch { return null; }

    const wrap = document.createElement('div');
    wrap.className = 'my-2';

    if (cfg.title) {
        const h = document.createElement('p');
        h.className = 'text-sm font-semibold text-gray-800 mb-2';
        h.textContent = cfg.title;
        wrap.appendChild(h);
    }

    const grid = document.createElement('div');
    grid.className = `grid gap-3 ${cfg.cards?.length > 3 ? 'grid-cols-2 lg:grid-cols-4' : 'grid-cols-2 lg:grid-cols-3'}`;

    const colorMap = { blue: 'bg-blue-50 text-blue-600', green: 'bg-green-50 text-green-600', red: 'bg-red-50 text-red-600', amber: 'bg-amber-50 text-amber-600', purple: 'bg-purple-50 text-purple-600', gray: 'bg-gray-50 text-gray-500' };

    (cfg.cards || []).forEach(card => {
        const color = colorMap[card.color] || colorMap.blue;
        const div = document.createElement('div');
        div.className = 'bg-white rounded-xl border border-gray-100 p-4 shadow-sm';
        div.innerHTML = `
            <p class="text-xs text-gray-500 mb-2">${card.label}</p>
            <p class="text-xl font-bold text-gray-800">${card.value}</p>
            ${card.sub ? `<p class="text-xs text-gray-400 mt-1">${card.sub}</p>` : ''}
            ${card.trend ? `<p class="text-xs mt-1 font-medium ${card.trend >= 0 ? 'text-green-600' : 'text-red-600'}">${card.trend >= 0 ? '▲' : '▼'} ${Math.abs(card.trend)}%</p>` : ''}`;
        grid.appendChild(div);
    });

    wrap.appendChild(grid);
    return wrap;
}

// ── Letter renderer ───────────────────────────────────────────
function renderLetter(body) {
    let cfg;
    try { cfg = JSON.parse(body); } catch { return null; }

    const wrap = document.createElement('div');
    wrap.className = 'my-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';

    // Toolbar
    const toolbar = document.createElement('div');
    toolbar.className = 'flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-200';
    toolbar.innerHTML = `
        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">📄 ${cfg.type || 'Surat'}</span>
        <div class="flex gap-2">
            <button onclick="printLetter(this)" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition">🖨 Cetak</button>
            <button onclick="copyLetter(this)" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded-lg transition">📋 Salin</button>
        </div>`;
    wrap.appendChild(toolbar);

    // Letter body
    const letterBody = document.createElement('div');
    letterBody.className = 'letter-content p-8 font-serif text-sm text-gray-800 leading-relaxed max-w-2xl mx-auto';
    letterBody.style.fontFamily = "'Times New Roman', serif";

    // Header
    if (cfg.from) {
        letterBody.innerHTML += `<div class="text-right mb-6"><p class="font-semibold">${cfg.from.name || ''}</p><p class="text-gray-600">${cfg.from.address || ''}</p><p class="text-gray-600">${cfg.from.city || ''}, ${cfg.date || new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p></div>`;
    }
    if (cfg.to) {
        letterBody.innerHTML += `<div class="mb-6"><p>Kepada Yth.</p><p class="font-semibold">${cfg.to.name || ''}</p><p class="text-gray-600">${cfg.to.address || ''}</p></div>`;
    }
    if (cfg.subject) {
        letterBody.innerHTML += `<p class="mb-4"><strong>Perihal: ${cfg.subject}</strong></p>`;
    }
    letterBody.innerHTML += `<div class="mb-6 leading-loose">${(cfg.body || '').replace(/\n/g, '<br>')}</div>`;
    if (cfg.closing) {
        letterBody.innerHTML += `<div class="mt-8"><p>${cfg.closing}</p><br><br><br><p class="font-semibold">${cfg.signer || ''}</p>${cfg.position ? `<p class="text-gray-600">${cfg.position}</p>` : ''}</div>`;
    }

    wrap.appendChild(letterBody);
    return wrap;
}

// ── Invoice renderer ──────────────────────────────────────────
function renderInvoice(body) {
    let cfg;
    try { cfg = JSON.parse(body); } catch { return null; }

    const wrap = document.createElement('div');
    wrap.className = 'my-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';

    const toolbar = document.createElement('div');
    toolbar.className = 'flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-200';
    toolbar.innerHTML = `
        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">🧾 Invoice / Faktur</span>
        <div class="flex gap-2">
            <button onclick="printLetter(this)" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition">🖨 Cetak</button>
        </div>`;
    wrap.appendChild(toolbar);

    const inv = document.createElement('div');
    inv.className = 'letter-content p-6 text-sm';

    const items = cfg.items || [];
    const subtotal = items.reduce((s, i) => s + (i.qty * i.price), 0);
    const tax = cfg.tax_percent ? subtotal * (cfg.tax_percent / 100) : (cfg.tax || 0);
    const total = subtotal + tax - (cfg.discount || 0);

    const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');

    inv.innerHTML = `
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">${cfg.company || 'Perusahaan'}</h2>
                <p class="text-gray-500 text-xs mt-1">${cfg.company_address || ''}</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-blue-600">INVOICE</p>
                <p class="text-xs text-gray-500 mt-1">No: <strong>${cfg.number || '-'}</strong></p>
                <p class="text-xs text-gray-500">Tanggal: ${cfg.date || new Date().toLocaleDateString('id-ID')}</p>
                ${cfg.due_date ? `<p class="text-xs text-red-500">Jatuh Tempo: ${cfg.due_date}</p>` : ''}
            </div>
        </div>
        <div class="mb-6 p-3 bg-gray-50 rounded-xl">
            <p class="text-xs text-gray-500 mb-1">Tagihan Kepada:</p>
            <p class="font-semibold">${cfg.to?.name || '-'}</p>
            <p class="text-xs text-gray-500">${cfg.to?.address || ''}</p>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200 mb-4">
            <table class="min-w-full text-sm">
                <thead><tr class="bg-gray-50">
                    <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-semibold">Deskripsi</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 font-semibold">Qty</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 font-semibold">Harga</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 font-semibold">Subtotal</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    ${items.map(i => `<tr>
                        <td class="px-4 py-2.5">${i.name}</td>
                        <td class="px-4 py-2.5 text-right">${i.qty} ${i.unit || ''}</td>
                        <td class="px-4 py-2.5 text-right">${fmt(i.price)}</td>
                        <td class="px-4 py-2.5 text-right font-medium">${fmt(i.qty * i.price)}</td>
                    </tr>`).join('')}
                </tbody>
            </table>
        </div>
        <div class="flex justify-end">
            <div class="w-64 space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>${fmt(subtotal)}</span></div>
                ${cfg.discount ? `<div class="flex justify-between text-red-600"><span>Diskon</span><span>- ${fmt(cfg.discount)}</span></div>` : ''}
                ${tax ? `<div class="flex justify-between text-gray-500"><span>Pajak ${cfg.tax_percent ? cfg.tax_percent + '%' : ''}</span><span>${fmt(tax)}</span></div>` : ''}
                <div class="flex justify-between font-bold text-base border-t border-gray-200 pt-2 mt-2"><span>Total</span><span class="text-blue-600">${fmt(total)}</span></div>
            </div>
        </div>
        ${cfg.notes ? `<div class="mt-4 p-3 bg-amber-50 rounded-xl text-xs text-amber-700"><strong>Catatan:</strong> ${cfg.notes}</div>` : ''}
        ${cfg.payment_info ? `<div class="mt-3 p-3 bg-blue-50 rounded-xl text-xs text-blue-700"><strong>Info Pembayaran:</strong> ${cfg.payment_info}</div>` : ''}`;

    wrap.appendChild(inv);
    return wrap;
}

// ── Printable block ───────────────────────────────────────────
function renderPrintable(meta, body) {
    const wrap = document.createElement('div');
    wrap.className = 'my-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';

    const toolbar = document.createElement('div');
    toolbar.className = 'flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-200';
    toolbar.innerHTML = `
        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">🖨 ${meta || 'Dokumen'}</span>
        <button onclick="printLetter(this)" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition">Cetak</button>`;
    wrap.appendChild(toolbar);

    const content = document.createElement('div');
    content.className = 'letter-content p-6 text-sm text-gray-800 leading-relaxed';
    content.innerHTML = parseMarkdown(body);
    wrap.appendChild(content);
    return wrap;
}

// ── Actions renderer ──────────────────────────────────────────
function renderActions(body) {
    let buttons;
    try { buttons = JSON.parse(body); } catch { return null; }
    if (!Array.isArray(buttons) || buttons.length === 0) return null;

    const wrap = document.createElement('div');
    wrap.className = 'flex flex-wrap gap-2 mt-2';

    const styleMap = {
        primary: 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-200',
        success: 'bg-green-600 hover:bg-green-700 text-white shadow-sm shadow-green-200',
        danger: 'bg-red-500 hover:bg-red-600 text-white shadow-sm shadow-red-200',
        warning: 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm shadow-amber-200',
        default: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 shadow-sm',
    };

    buttons.forEach(btn => {
        const el = document.createElement('button');
        const cls = styleMap[btn.style] || styleMap.default;
        el.className = `inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg transition ${cls}`;
        el.innerHTML = `${btn.icon ? `<span>${btn.icon}</span>` : ''}<span>${btn.label}</span>`;
        el.onclick = () => {
            if (btn.message) sendMessage(btn.message);
        };
        wrap.appendChild(el);
    });

    return wrap;
}

// ── Print helper ──────────────────────────────────────────────
window.printLetter = function (btn) {
    const content = btn.closest('.overflow-hidden').querySelector('.letter-content');
    if (!content) return;
    const win = window.open('', '_blank');
    win.document.write(`<!DOCTYPE html><html><head>
        <meta charset="UTF-8"><title>Cetak</title>
        <style>
            body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #111; padding: 2cm; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background: #f5f5f5; font-weight: bold; }
            @media print { body { padding: 1cm; } }
        </style>
    </head><body>${content.innerHTML}</body></html>`);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); win.close(); }, 300);
};

window.copyLetter = function (btn) {
    const content = btn.closest('.overflow-hidden').querySelector('.letter-content');
    if (!content) return;
    navigator.clipboard.writeText(content.innerText);
    btn.textContent = '✓ Tersalin';
    setTimeout(() => btn.textContent = '📋 Salin', 2000);
};

// ── Message bubble ────────────────────────────────────────────
function appendMessage(role, content, modelUsed = null, timestamp = null) {
    document.getElementById('empty-state')?.remove();

    const isUser = role === 'user';
    const wrap = document.createElement('div');
    wrap.className = `flex ${isUser ? 'justify-end' : 'justify-start'} gap-3 max-w-4xl mx-auto w-full mb-4`;

    if (isUser) {
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[75%] bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm leading-relaxed';
        bubble.textContent = content;
        wrap.appendChild(bubble);
    } else {
        const avatar = document.createElement('div');
        avatar.className = 'shrink-0 w-7 h-7 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xs font-bold mt-1';
        avatar.textContent = 'Q';
        wrap.appendChild(avatar);

        const bubble = document.createElement('div');
        bubble.className = 'flex-1 min-w-0';

        const richContent = renderRichContent(content);
        bubble.appendChild(richContent);

        // Copy button + timestamp row
        const meta = document.createElement('div');
        meta.className = 'mt-1.5 flex items-center gap-3';

        const copyBtn = document.createElement('button');
        copyBtn.className = 'text-xs text-gray-300 hover:text-gray-500 transition flex items-center gap-1';
        copyBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin`;
        copyBtn.onclick = () => {
            navigator.clipboard.writeText(content);
            copyBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin`;
            setTimeout(() => { copyBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin`; }, 2000);
        };
        meta.appendChild(copyBtn);

        if (timestamp) {
            const d = new Date(timestamp);
            const timeSpan = document.createElement('span');
            timeSpan.className = 'text-xs text-gray-300';
            timeSpan.textContent = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            meta.appendChild(timeSpan);
        }
        bubble.appendChild(meta);
        wrap.appendChild(bubble);
    }

    msgBox.appendChild(wrap);
    return wrap;
}

// ── Action badges ─────────────────────────────────────────────
function appendActionBadges(actions) {
    if (!actions?.length) return;
    const wrap = document.createElement('div');
    wrap.className = 'flex justify-start max-w-4xl mx-auto w-full pl-10 mb-2';
    const inner = document.createElement('div');
    inner.className = 'flex flex-wrap gap-1.5';
    const writeTools = ['add_stock', 'create_purchase_order', 'create_sales_order', 'add_transaction', 'create_quick_sale', 'create_product', 'create_customer', 'create_employee', 'record_attendance', 'create_project', 'transfer_stock', 'create_work_order', 'record_production_output', 'create_recipe', 'produce_with_recipe', 'record_payment'];
    actions.forEach(a => {
        const isWrite = writeTools.includes(a.tool);
        const badge = document.createElement('span');
        badge.className = `text-xs px-2 py-0.5 rounded-full font-medium ${isWrite ? 'bg-green-100 text-green-700' : 'bg-blue-50 text-blue-600'}`;
        badge.textContent = `⚡ ${a.tool.replace(/_/g, ' ')}`;
        inner.appendChild(badge);
    });
    wrap.appendChild(inner);
    msgBox.appendChild(wrap);
}

// ── Loading state ─────────────────────────────────────────────
function setLoading(state) {
    isLoading = state;
    btnSend.disabled = state;
    typingEl.classList.toggle('hidden', !state);
    typingEl.classList.toggle('flex', state);
}

function scrollBottom() {
    msgBox.scrollTo({ top: msgBox.scrollHeight, behavior: 'smooth' });
}

// ── Send message ──────────────────────────────────────────────
async function sendMessage(text) {
    if ((!text.trim() && pendingFiles.length === 0) || isLoading) return;
    input.value = '';
    input.style.height = 'auto';
    setLoading(true);

    // If files are attached, use multimodal endpoint
    if (pendingFiles.length > 0) {
        const files = [...pendingFiles];
        clearFiles();
        appendMessageWithFiles('user', text || 'Analisis file ini:', files);
        scrollBottom();
        await sendWithFiles(text || 'Tolong analisis file/gambar ini.', files);
    } else {
        appendMessage('user', text);
        scrollBottom();
        await sendTextOnly(text);
    }

    setLoading(false);
    scrollBottom();
}

async function sendTextOnly(text) {
    try {
        const res = await fetch('/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ message: text, session_id: currentSessionId }),
        });
        const data = await res.json();
        if (!res.ok && !data.message) {
            appendMessage('model', `⚠️ Terjadi kesalahan (${res.status}). Silakan coba lagi.`);
            return;
        }
        handleChatResponse(data, text);
    } catch {
        appendMessage('model', '⚠️ Koneksi bermasalah. Silakan coba lagi.');
    }
}

async function sendWithFiles(text, files) {
    try {
        const formData = new FormData();
        formData.append('message', text);
        if (currentSessionId) formData.append('session_id', currentSessionId);
        files.forEach((f, i) => formData.append(`files[${i}]`, f.file));

        const res = await fetch('/chat/send-media', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: formData,
        });
        const data = await res.json();
        if (!res.ok && !data.message) {
            appendMessage('model', `⚠️ Terjadi kesalahan (${res.status}). Silakan coba lagi.`);
            return;
        }
        handleChatResponse(data, text);
    } catch {
        appendMessage('model', '⚠️ Gagal mengirim file. Pastikan ukuran file tidak melebihi 20MB.');
    }
}

function handleChatResponse(data, originalText) {
    if (data.session_id && !currentSessionId) {
        currentSessionId = data.session_id;
        addSessionToSidebar(data.session_id, data.session_title ?? originalText.slice(0, 50));
    }
    if (data.session_title) {
        chatTitle.textContent = data.session_title;
        updateSessionTitle(data.session_id, data.session_title);
    }
    if (data.model) modelLabel.textContent = 'Qalcuity AI';

    // Handle quota exceeded
    if (data.quota_exceeded) {
        appendMessage('model', data.message ?? 'Kuota habis.', null, new Date().toISOString());
        return;
    }

    // Handle HTTP error responses
    if (data.error || (!data.message && !data.session_id)) {
        appendMessage('model', data.message ?? 'Terjadi kesalahan pada server.', null, new Date().toISOString());
        return;
    }

    appendMessage('model', data.message ?? 'Terjadi kesalahan.', data.model, new Date().toISOString());
    appendActionBadges(data.actions);
}

// ── File handling ─────────────────────────────────────────────
function humanSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function isImageFile(file) {
    return file.type.startsWith('image/');
}

function addFilesToQueue(fileList) {
    const maxFiles = 5;
    const maxSize = 20 * 1024 * 1024; // 20MB

    Array.from(fileList).forEach(file => {
        if (pendingFiles.length >= maxFiles) return;
        if (file.size > maxSize) {
            alert(`File "${file.name}" terlalu besar (maks 20MB).`);
            return;
        }
        pendingFiles.push({ file, id: Date.now() + Math.random() });
    });

    renderFilePreview();
}

function renderFilePreview() {
    filePreviewStrip.innerHTML = '';
    if (pendingFiles.length === 0) {
        filePreviewStrip.classList.add('hidden');
        return;
    }
    filePreviewStrip.classList.remove('hidden');

    pendingFiles.forEach(({ file, id }) => {
        const item = document.createElement('div');
        item.className = 'relative group flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs text-gray-600 shadow-sm max-w-[160px]';
        item.dataset.fileId = id;

        if (isImageFile(file)) {
            const img = document.createElement('img');
            img.className = 'w-6 h-6 rounded object-cover shrink-0';
            const reader = new FileReader();
            reader.onload = e => img.src = e.target.result;
            reader.readAsDataURL(file);
            item.appendChild(img);
        } else {
            const icon = document.createElement('span');
            icon.className = 'text-base shrink-0';
            icon.textContent = file.type === 'application/pdf' ? '📄' : '📎';
            item.appendChild(icon);
        }

        const label = document.createElement('span');
        label.className = 'truncate flex-1';
        label.title = file.name;
        label.textContent = file.name;
        item.appendChild(label);

        const size = document.createElement('span');
        size.className = 'text-gray-400 shrink-0';
        size.textContent = humanSize(file.size);
        item.appendChild(size);

        const removeBtn = document.createElement('button');
        removeBtn.className = 'absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-white rounded-full text-xs hidden group-hover:flex items-center justify-center leading-none';
        removeBtn.textContent = '×';
        removeBtn.onclick = () => {
            pendingFiles = pendingFiles.filter(f => f.id !== id);
            renderFilePreview();
        };
        item.appendChild(removeBtn);

        filePreviewStrip.appendChild(item);
    });
}

function clearFiles() {
    pendingFiles = [];
    fileInput.value = '';
    renderFilePreview();
}

// Append user message with file thumbnails
function appendMessageWithFiles(role, content, files) {
    document.getElementById('empty-state')?.remove();
    const wrap = document.createElement('div');
    wrap.className = 'flex justify-end gap-3 max-w-4xl mx-auto w-full mb-4';

    const col = document.createElement('div');
    col.className = 'flex flex-col items-end gap-1.5 max-w-[75%]';

    // File thumbnails
    if (files.length > 0) {
        const thumbRow = document.createElement('div');
        thumbRow.className = 'flex gap-1.5 flex-wrap justify-end';
        files.forEach(({ file }) => {
            if (isImageFile(file)) {
                const img = document.createElement('img');
                img.className = 'w-20 h-20 rounded-xl object-cover border border-gray-200 shadow-sm';
                const reader = new FileReader();
                reader.onload = e => img.src = e.target.result;
                reader.readAsDataURL(file);
                thumbRow.appendChild(img);
            } else {
                const badge = document.createElement('div');
                badge.className = 'flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-600 shadow-sm';
                badge.innerHTML = `<span>${file.type === 'application/pdf' ? '📄' : '📎'}</span><span class="max-w-[120px] truncate">${file.name}</span>`;
                thumbRow.appendChild(badge);
            }
        });
        col.appendChild(thumbRow);
    }

    // Text bubble
    if (content.trim()) {
        const bubble = document.createElement('div');
        bubble.className = 'bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm leading-relaxed';
        bubble.textContent = content;
        col.appendChild(bubble);
    }

    wrap.appendChild(col);
    msgBox.appendChild(wrap);
    return wrap;
}

// ── Load session ──────────────────────────────────────────────
async function loadSession(sessionId, title) {
    currentSessionId = sessionId;
    chatTitle.textContent = title ?? 'Percakapan';
    msgBox.innerHTML = '';
    setLoading(true);
    try {
        const res = await fetch(`/chat/${sessionId}/messages`);
        const data = await res.json();
        if (!data.messages?.length) {
            msgBox.innerHTML = `<div id="empty-state" class="text-center text-gray-400 text-sm py-16">Belum ada pesan.</div>`;
        } else {
            data.messages.forEach(m => appendMessage(m.role, m.content, m.model_used, m.created_at));
        }
    } catch { appendMessage('model', 'Gagal memuat riwayat.'); }
    setLoading(false);
    scrollBottom();
    document.querySelectorAll('.session-item').forEach(el =>
        el.classList.toggle('bg-blue-50', el.dataset.session == sessionId));
}

// ── Sidebar helpers ───────────────────────────────────────────
function addSessionToSidebar(id, title) {
    sessionList.querySelector('p')?.remove();
    const div = document.createElement('div');
    div.className = 'session-item group flex items-center rounded-xl hover:bg-gray-50 bg-blue-50 transition cursor-pointer';
    div.dataset.session = id;
    div.dataset.title = title;
    div.innerHTML = `
        <button class="flex-1 text-left px-3 py-2.5 text-sm text-gray-600 truncate session-btn">${title}</button>
        <button class="session-delete hidden group-hover:flex items-center px-2 py-2 text-gray-300 hover:text-red-500" data-session="${id}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>`;
    div.querySelector('.session-btn').addEventListener('click', () => loadSession(id, title));
    div.querySelector('.session-delete').addEventListener('click', e => { e.stopPropagation(); deleteSession(id, div); });
    sessionList.prepend(div);
}

function updateSessionTitle(id, title) {
    const el = sessionList.querySelector(`[data-session="${id}"] .session-btn`);
    if (el) el.textContent = title;
}

async function deleteSession(id, el) {
    if (!confirm('Hapus percakapan ini?')) return;
    await fetch(`/chat/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
    el.remove();
    if (currentSessionId == id) {
        currentSessionId = null;
        msgBox.innerHTML = '';
        chatTitle.textContent = 'Percakapan Baru';
    }
}

// ── Event listeners ───────────────────────────────────────────
btnSend.addEventListener('click', () => sendMessage(input.value));
input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(input.value); }
});
input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 144) + 'px';
});
document.getElementById('btn-new-chat').addEventListener('click', () => {
    currentSessionId = null;
    chatTitle.textContent = 'Percakapan Baru';
    modelLabel.textContent = 'Siap membantu';
    msgBox.innerHTML = `<div id="empty-state" class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-16"><p class="text-sm">Mulai percakapan baru...</p></div>`;
    document.querySelectorAll('.session-item').forEach(el => el.classList.remove('bg-blue-50'));
    input.focus();
});
document.querySelectorAll('.hint-btn').forEach(btn =>
    btn.addEventListener('click', () => sendMessage(btn.textContent.trim())));
document.querySelectorAll('.session-item').forEach(item => {
    item.querySelector('.session-btn')?.addEventListener('click', () =>
        loadSession(item.dataset.session, item.dataset.title));
    item.querySelector('.session-delete')?.addEventListener('click', e => {
        e.stopPropagation();
        deleteSession(item.dataset.session, item);
    });
});

// ── File input ────────────────────────────────────────────────
fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
        addFilesToQueue(fileInput.files);
        fileInput.value = ''; // reset so same file can be re-added
    }
});

// Drag & drop onto chat area
msgBox.addEventListener('dragover', e => { e.preventDefault(); msgBox.classList.add('ring-2', 'ring-blue-300', 'ring-inset'); });
msgBox.addEventListener('dragleave', () => msgBox.classList.remove('ring-2', 'ring-blue-300', 'ring-inset'));
msgBox.addEventListener('drop', e => {
    e.preventDefault();
    msgBox.classList.remove('ring-2', 'ring-blue-300', 'ring-inset');
    if (e.dataTransfer.files.length > 0) addFilesToQueue(e.dataTransfer.files);
});

// Paste image from clipboard
document.addEventListener('paste', e => {
    const items = Array.from(e.clipboardData?.items || []);
    const imageItems = items.filter(i => i.kind === 'file' && i.type.startsWith('image/'));
    if (imageItems.length > 0) {
        const files = imageItems.map(i => i.getAsFile()).filter(Boolean);
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        addFilesToQueue(dt.files);
    }
});

input.focus();
