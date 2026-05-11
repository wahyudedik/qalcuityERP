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
                `<th class="px-4 py-2.5 text-left text-xs uppercase tracking-wide font-semibold bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-slate-400">${this.parser.parseInline(cell.tokens)}</th>`
            ).join('');
            const rows = token.rows.map((row, i) => {
                const cells = row.map(cell =>
                    `<td class="px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300">${this.parser.parseInline(cell.tokens)}</td>`
                ).join('');
                return `<tr class="${i % 2 === 0 ? '' : 'bg-gray-50/50 dark:bg-white/[0.02]'} hover:bg-blue-50/30 dark:hover:bg-blue-500/10 transition">${cells}</tr>`;
            }).join('');
            return `<div class="overflow-x-auto my-3 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm">
  <table class="min-w-full text-sm">
    <thead><tr>${headerCells}</tr></thead>
    <tbody class="divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-transparent">${rows}</tbody>
  </table>
</div>`;
        },
        code(token) {
            const lang = token.lang || 'code';
            const escaped = token.text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return `<div class="my-2 rounded-xl overflow-hidden border border-gray-200">
  <div class="bg-gray-800 px-4 py-2 text-xs text-gray-400 flex items-center justify-between">
    <span>${lang}</span>
    <button onclick="copyToClipboard(this.closest('.my-2').querySelector('code').textContent);this.textContent='✓';setTimeout(()=>this.textContent='Salin',2000)" class="text-gray-500 hover:text-white transition text-xs">Salin</button>
  </div>
  <pre class="bg-gray-900 text-green-400 px-4 py-3 text-xs overflow-x-auto"><code>${escaped}</code></pre>
</div>`;
        },
        blockquote(token) {
            return `<blockquote class="border-l-4 border-blue-400 pl-4 my-2 text-gray-600 dark:text-slate-400 italic bg-blue-50/40 dark:bg-blue-500/10 py-2 rounded-r-lg">${this.parser.parse(token.tokens)}</blockquote>`;
        },
        list(token) {
            const tag = token.ordered ? 'ol' : 'ul';
            const cls = token.ordered ? 'list-decimal' : 'list-disc';
            const items = token.items.map(item => `<li class="text-sm text-gray-700 dark:text-slate-300">${this.parser.parse(item.tokens)}</li>`).join('');
            return `<${tag} class="${cls} pl-5 my-2 space-y-1">${items}</${tag}>`;
        },
        heading(token) {
            const sizes = { 1: 'text-lg', 2: 'text-base', 3: 'text-sm' };
            const text = this.parser.parseInline(token.tokens);
            return `<h${token.depth} class="${sizes[token.depth] ?? 'text-sm'} font-semibold text-gray-800 dark:text-slate-100 mt-4 mb-1.5">${text}</h${token.depth}>`;
        },
        strong(token) { return `<strong class="font-semibold text-gray-900 dark:text-slate-100">${this.parser.parseInline(token.tokens)}</strong>`; },
        em(token) { return `<em class="italic text-gray-600 dark:text-slate-400">${this.parser.parseInline(token.tokens)}</em>`; },
        hr() { return `<hr class="my-3 border-gray-200 dark:border-white/10">`; },
    }
});

// ── Clipboard helper (works on HTTP + HTTPS) ──────────────────
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }
    // Fallback for HTTP (non-secure context)
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); } catch { }
    document.body.removeChild(ta);
    return Promise.resolve();
}

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
// Supported: ```chart, ```grid, ```kpi, ```letter, ```invoice, ```print, ```actions

function renderRichContent(text) {
    const container = document.createElement('div');
    container.className = 'space-y-3';

    // Split by special fenced blocks — handles optional meta on same line as type
    const parts = text.split(/(```(?:chart|grid|kpi|letter|invoice|print|actions)(?:[^\n]*)?\n[\s\S]*?```)/g);

    parts.forEach(part => {
        const fenceMatch = part.match(/^```(chart|grid|kpi|letter|invoice|print|actions)([^\n]*)\n([\s\S]*?)```$/);
        if (fenceMatch) {
            const [, type, meta, body] = fenceMatch;
            const block = renderSpecialBlock(type.trim(), meta.trim(), body.trim());
            if (block) { container.appendChild(block); return; }
            // If parse failed, fall through to render as code block
        }
        // Normal markdown
        if (part.trim()) {
            const div = document.createElement('div');
            div.className = 'prose-chat text-sm leading-relaxed text-gray-800 dark:text-slate-200';
            div.innerHTML = parseMarkdown(part);
            container.appendChild(div);
        }
    });

    return container;
}

// Safe JSON parse — returns null on failure instead of throwing
function safeJson(str) {
    try { return JSON.parse(str); } catch { return null; }
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
    const cfg = safeJson(body);
    if (!cfg) return renderParseError('chart', body);

    // Normalize: support both cfg.data (Chart.js native) and flat cfg.labels/cfg.datasets
    if (!cfg.data && cfg.labels) {
        cfg.data = { labels: cfg.labels, datasets: cfg.datasets || [] };
    }
    if (!cfg.data?.datasets?.length) return renderParseError('chart', body);

    const id = 'chart-' + (++chartCounter);
    const wrap = document.createElement('div');
    wrap.className = 'bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 shadow-sm my-2';

    const title = cfg.title || meta || 'Grafik';
    wrap.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-gray-800 dark:text-slate-100">${title}</p>
            <button onclick="downloadChart('${id}')" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Unduh</button>
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

// ── Parse error fallback ──────────────────────────────────────
// Renders a subtle warning instead of silently dropping the block
function renderParseError(type, body) {
    const wrap = document.createElement('div');
    wrap.className = 'my-2 p-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl text-xs text-amber-700 dark:text-amber-300';
    wrap.innerHTML = `<span class="font-semibold">⚠ Gagal render blok <code>${type}</code>.</span> Data mungkin tidak valid.
        <details class="mt-1"><summary class="cursor-pointer text-amber-600 dark:text-amber-400">Lihat raw</summary>
        <pre class="mt-1 text-[10px] text-gray-500 dark:text-slate-400 overflow-x-auto whitespace-pre-wrap">${body.replace(/</g, '&lt;').slice(0, 500)}</pre></details>`;
    return wrap;
}

// ── Grid renderer ─────────────────────────────────────────────
function renderGrid(body) {
    const cfg = safeJson(body);
    if (!cfg) return renderParseError('grid', body);

    const wrap = document.createElement('div');
    wrap.className = 'my-2';

    if (cfg.title) {
        const h = document.createElement('p');
        h.className = 'text-sm font-semibold text-gray-800 dark:text-slate-100 mb-2';
        h.textContent = cfg.title;
        wrap.appendChild(h);
    }

    const cols = cfg.columns || [];
    const rows = cfg.rows || [];

    const tableWrap = document.createElement('div');
    tableWrap.className = 'overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10 shadow-sm';

    const table = document.createElement('table');
    table.className = 'min-w-full text-sm';

    // Header
    const thead = document.createElement('thead');
    thead.innerHTML = `<tr>${cols.map(c =>
        `<th class="px-4 py-2.5 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-slate-400 font-semibold bg-gray-50 dark:bg-white/5">${c.label || c}</th>`
    ).join('')}</tr>`;
    table.appendChild(thead);

    // Body
    const tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-transparent';
    rows.forEach((row, i) => {
        const tr = document.createElement('tr');
        tr.className = (i % 2 ? 'bg-gray-50/40 dark:bg-white/[0.02]' : '') + ' hover:bg-blue-50/30 dark:hover:bg-blue-500/10 transition';
        cols.forEach(col => {
            const key = col.key || col;
            const val = row[key] ?? '-';
            const td = document.createElement('td');
            td.className = 'px-4 py-2.5 text-gray-700 dark:text-slate-300';

            // Badge support
            if (col.badge) {
                const colorMap = { success: 'bg-green-100 text-green-700', warning: 'bg-amber-100 text-amber-700', danger: 'bg-red-100 text-red-700', info: 'bg-blue-100 text-blue-700', default: 'bg-gray-100 text-gray-600' };
                // col.badge maps value → color name (e.g. {"Aktif": "success"})
                const colorKey = col.badge[val] || 'default';
                const colorCls = colorMap[colorKey] || colorMap.default;
                td.innerHTML = `<span class="text-xs font-medium px-2 py-0.5 rounded-full ${colorCls}">${val}</span>`;
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
    const cfg = safeJson(body);
    if (!cfg) return renderParseError('kpi', body);

    const wrap = document.createElement('div');
    wrap.className = 'my-2';

    if (cfg.title) {
        const h = document.createElement('p');
        h.className = 'text-sm font-semibold text-gray-800 dark:text-slate-100 mb-2';
        h.textContent = cfg.title;
        wrap.appendChild(h);
    }

    const grid = document.createElement('div');
    grid.className = `grid gap-3 ${cfg.cards?.length > 3 ? 'grid-cols-2 lg:grid-cols-4' : 'grid-cols-2 lg:grid-cols-3'}`;

    const colorMap = { blue: 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400', green: 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400', red: 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400', amber: 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400', purple: 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400', gray: 'bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-slate-400' };

    (cfg.cards || []).forEach(card => {
        const color = colorMap[card.color] || colorMap.blue;
        const div = document.createElement('div');
        div.className = 'bg-white dark:bg-[#1e293b] rounded-xl border border-gray-100 dark:border-white/10 p-4 shadow-sm';
        div.innerHTML = `
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">${card.label}</p>
            <p class="text-xl font-bold text-gray-800 dark:text-slate-100">${card.value}</p>
            ${card.sub ? `<p class="text-xs text-gray-400 dark:text-slate-500 mt-1">${card.sub}</p>` : ''}
            ${card.trend ? `<p class="text-xs mt-1 font-medium ${card.trend >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">${card.trend >= 0 ? '▲' : '▼'} ${Math.abs(card.trend)}%</p>` : ''}`;
        grid.appendChild(div);
    });

    wrap.appendChild(grid);
    return wrap;
}

// ── Letter renderer ───────────────────────────────────────────
function renderLetter(body) {
    const cfg = safeJson(body);
    if (!cfg) return renderParseError('letter', body);

    const wrap = document.createElement('div');
    wrap.className = 'my-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden';

    // Toolbar
    const toolbar = document.createElement('div');
    toolbar.className = 'flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10';
    toolbar.innerHTML = `
        <span class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wide">📄 ${cfg.type || 'Surat'}</span>
        <div class="flex gap-2">
            <button onclick="printLetter(this)" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition">🖨 Cetak</button>
            <button onclick="copyLetter(this)" class="text-xs bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 text-gray-700 dark:text-slate-300 px-3 py-1.5 rounded-lg transition">📋 Salin</button>
        </div>`;
    wrap.appendChild(toolbar);

    // Letter body
    const letterBody = document.createElement('div');
    letterBody.className = 'letter-content p-8 font-serif text-sm text-gray-800 dark:text-slate-200 leading-relaxed max-w-2xl mx-auto';
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
    const cfg = safeJson(body);
    if (!cfg) return renderParseError('invoice', body);

    const wrap = document.createElement('div');
    wrap.className = 'my-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden';

    const toolbar = document.createElement('div');
    toolbar.className = 'flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10';
    toolbar.innerHTML = `
        <span class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wide">🧾 Invoice / Faktur</span>
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
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">${cfg.company || 'Perusahaan'}</h2>
                <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">${cfg.company_address || ''}</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">INVOICE</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">No: <strong>${cfg.number || '-'}</strong></p>
                <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal: ${cfg.date || new Date().toLocaleDateString('id-ID')}</p>
                ${cfg.due_date ? `<p class="text-xs text-red-500 dark:text-red-400">Jatuh Tempo: ${cfg.due_date}</p>` : ''}
            </div>
        </div>
        <div class="mb-6 p-3 bg-gray-50 dark:bg-white/5 rounded-xl">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Tagihan Kepada:</p>
            <p class="font-semibold text-gray-800 dark:text-slate-200">${cfg.to?.name || '-'}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400">${cfg.to?.address || ''}</p>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10 mb-4">
            <table class="min-w-full text-sm">
                <thead><tr class="bg-gray-50 dark:bg-white/5">
                    <th class="px-4 py-2.5 text-left text-xs text-gray-500 dark:text-slate-400 font-semibold">Deskripsi</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-slate-400 font-semibold">Qty</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-slate-400 font-semibold">Harga</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-slate-400 font-semibold">Subtotal</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    ${items.map(i => `<tr>
                        <td class="px-4 py-2.5 text-gray-700 dark:text-slate-300">${i.name}</td>
                        <td class="px-4 py-2.5 text-right text-gray-700 dark:text-slate-300">${i.qty} ${i.unit || ''}</td>
                        <td class="px-4 py-2.5 text-right text-gray-700 dark:text-slate-300">${fmt(i.price)}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-800 dark:text-slate-200">${fmt(i.qty * i.price)}</td>
                    </tr>`).join('')}
                </tbody>
            </table>
        </div>
        <div class="flex justify-end">
            <div class="w-64 space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Subtotal</span><span class="text-gray-800 dark:text-slate-200">${fmt(subtotal)}</span></div>
                ${cfg.discount ? `<div class="flex justify-between text-red-600 dark:text-red-400"><span>Diskon</span><span>- ${fmt(cfg.discount)}</span></div>` : ''}
                ${tax ? `<div class="flex justify-between text-gray-500 dark:text-slate-400"><span>Pajak ${cfg.tax_percent ? cfg.tax_percent + '%' : ''}</span><span>${fmt(tax)}</span></div>` : ''}
                <div class="flex justify-between font-bold text-base border-t border-gray-200 dark:border-white/10 pt-2 mt-2"><span class="text-gray-800 dark:text-slate-100">Total</span><span class="text-blue-600 dark:text-blue-400">${fmt(total)}</span></div>
            </div>
        </div>
        ${cfg.notes ? `<div class="mt-4 p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl text-xs text-amber-700 dark:text-amber-300"><strong>Catatan:</strong> ${cfg.notes}</div>` : ''}
        ${cfg.payment_info ? `<div class="mt-3 p-3 bg-blue-50 dark:bg-blue-500/10 rounded-xl text-xs text-blue-700 dark:text-blue-300"><strong>Info Pembayaran:</strong> ${cfg.payment_info}</div>` : ''}`;

    wrap.appendChild(inv);
    return wrap;
}

// ── Printable block ───────────────────────────────────────────
function renderPrintable(meta, body) {
    const wrap = document.createElement('div');
    wrap.className = 'my-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden';

    const toolbar = document.createElement('div');
    toolbar.className = 'flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10';
    toolbar.innerHTML = `
        <span class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wide">🖨 ${meta || 'Dokumen'}</span>
        <button onclick="printLetter(this)" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition">Cetak</button>`;
    wrap.appendChild(toolbar);

    const content = document.createElement('div');
    content.className = 'letter-content p-6 text-sm text-gray-800 dark:text-slate-200 leading-relaxed';
    content.innerHTML = parseMarkdown(body);
    wrap.appendChild(content);
    return wrap;
}

// ── Actions renderer ──────────────────────────────────────────
function renderActions(body) {
    const buttons = safeJson(body);
    if (!Array.isArray(buttons) || buttons.length === 0) return null;

    const wrap = document.createElement('div');
    wrap.className = 'flex flex-wrap gap-1.5 mt-2';

    const styleMap = {
        primary: 'bg-blue-500/15 hover:bg-blue-500/25 text-blue-400 border border-blue-500/30',
        success: 'bg-green-500/15 hover:bg-green-500/25 text-green-400 border border-green-500/30',
        danger: 'bg-red-500/15 hover:bg-red-500/25 text-red-400 border border-red-500/30',
        warning: 'bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/30',
        default: 'bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10',
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
    copyToClipboard(content.innerText);
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
        copyBtn.className = 'text-xs text-slate-500 hover:text-slate-300 transition flex items-center gap-1';
        copyBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin`;
        copyBtn.onclick = () => {
            copyToClipboard(content);
            copyBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersalin`;
            setTimeout(() => { copyBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin`; }, 2000);
        };
        meta.appendChild(copyBtn);

        if (timestamp) {
            const d = new Date(timestamp);
            const timeSpan = document.createElement('span');
            timeSpan.className = 'text-xs text-slate-500';
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
// Ditampilkan sebagai detail kecil di bawah bubble AI, tersembunyi by default
function appendActionBadges(actions) {
    // Action badges disembunyikan dari UI — tidak perlu ditampilkan ke user
    return;
}

// ── Loading state ─────────────────────────────────────────────
function setLoading(state) {
    isLoading = state;
    btnSend.disabled = state;
    typingEl.classList.toggle('hidden', !state);
    typingEl.classList.toggle('flex', state);
}

// ── Error message with retry ──────────────────────────────────
function appendErrorMessage(text, retryText = null) {
    document.getElementById('empty-state')?.remove();
    const wrap = document.createElement('div');
    wrap.className = 'flex justify-start gap-3 max-w-4xl mx-auto w-full mb-4';

    const avatar = document.createElement('div');
    avatar.className = 'shrink-0 w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center text-red-500 text-xs font-bold mt-1';
    avatar.textContent = '!';
    wrap.appendChild(avatar);

    const col = document.createElement('div');
    col.className = 'flex flex-col gap-1.5';

    const bubble = document.createElement('div');
    bubble.className = 'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-300 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm';
    bubble.textContent = text;
    col.appendChild(bubble);

    if (retryText) {
        const retryBtn = document.createElement('button');
        retryBtn.className = 'self-start text-xs text-red-500 hover:text-red-700 border border-red-200 dark:border-red-500/30 hover:border-red-400 bg-white dark:bg-transparent rounded-lg px-3 py-1.5 transition';
        retryBtn.textContent = '↺ Coba lagi';
        retryBtn.onclick = () => { wrap.remove(); sendMessage(retryText); };
        col.appendChild(retryBtn);
    }

    wrap.appendChild(col);
    msgBox.appendChild(wrap);
    scrollBottom();
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
            appendErrorMessage(`⚠️ Terjadi kesalahan (${res.status}). Silakan coba lagi.`, text);
            return;
        }
        handleChatResponse(data, text);
    } catch {
        appendErrorMessage('⚠️ Koneksi bermasalah. Silakan coba lagi.', text);
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
            appendErrorMessage(`⚠️ Terjadi kesalahan (${res.status}). Silakan coba lagi.`, text);
            return;
        }
        handleChatResponse(data, text);
    } catch {
        appendErrorMessage('⚠️ Gagal mengirim file. Pastikan ukuran file tidak melebihi 20MB.', text);
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

    // Update quota bar if server returned quota info
    if (data.quota) updateQuotaBar(data.quota);

    // Handle quota exceeded
    if (data.quota_exceeded) {
        appendMessage('model', data.message ?? 'Kuota habis.', null, new Date().toISOString());
        return;
    }

    // Handle HTTP error responses
    if (data.error || (!data.message && !data.session_id)) {
        appendErrorMessage(data.message ?? 'Terjadi kesalahan pada server.', originalText);
        return;
    }

    appendMessage('model', data.message ?? 'Terjadi kesalahan.', data.model, new Date().toISOString());
    appendActionBadges(data.actions);
    appendSuggestedFollowUps();
}

function updateQuotaBar(quota) {
    const textEl = document.getElementById('quota-text');
    const barEl = document.getElementById('quota-bar');
    if (!textEl || !barEl) return;

    if (quota.unlimited) {
        textEl.textContent = '∞ pesan';
        barEl.style.width = '10%';
        barEl.className = 'h-full rounded-full transition-all bg-blue-400';
        return;
    }

    const pct = quota.limit > 0 ? Math.min(100, Math.round((quota.used / quota.limit) * 100)) : 0;
    textEl.textContent = `${quota.used}/${quota.limit} pesan`;
    barEl.style.width = pct + '%';
    barEl.className = 'h-full rounded-full transition-all ' +
        (pct >= 90 ? 'bg-red-400' : pct >= 70 ? 'bg-amber-400' : 'bg-blue-400');

    // Show warning toast at 80%
    if (pct >= 80 && pct < 100 && !window._quotaWarnShown) {
        window._quotaWarnShown = true;
        const remaining = quota.remaining ?? 0;
        appendMessage('model',
            `⚠️ Kuota AI hampir habis. Sisa ${remaining} pesan bulan ini. Upgrade paket untuk akses lebih banyak.`,
            null, new Date().toISOString()
        );
    }
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
            Dialog.warning(`File "${file.name}" terlalu besar (maks 20MB).`);
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
    aiMessageCount = 0;
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
    document.getElementById('empty-sessions-msg')?.remove();
    sessionList.querySelector('p')?.remove();
    const div = document.createElement('div');
    div.className = 'session-item group flex items-center rounded-xl hover:bg-gray-50 bg-blue-50 transition cursor-pointer';
    div.dataset.session = id;
    div.dataset.title = title;
    div.innerHTML = `
        <button class="flex-1 text-left px-3 py-2.5 text-sm text-gray-600 truncate session-btn">${title}</button>
        <div class="hidden group-hover:flex items-center gap-0.5 pr-1">
            <button class="session-rename flex items-center p-1.5 text-gray-300 hover:text-blue-500 transition rounded" data-session="${id}" title="Ganti nama">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button class="session-delete flex items-center p-1.5 text-gray-300 hover:text-red-500 transition rounded" data-session="${id}" title="Hapus">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>`;
    div.querySelector('.session-btn').addEventListener('click', () => loadSession(id, title));
    div.querySelector('.session-rename').addEventListener('click', e => { e.stopPropagation(); renameSession(id, div); });
    div.querySelector('.session-delete').addEventListener('click', e => { e.stopPropagation(); deleteSession(id, div); });
    sessionList.prepend(div);
}

function updateSessionTitle(id, title) {
    const el = sessionList.querySelector(`[data-session="${id}"] .session-btn`);
    if (el) { el.textContent = title; el.closest('.session-item').dataset.title = title; }
}

async function renameSession(id, el) {
    const currentTitle = el.querySelector('.session-btn').textContent.trim();
    const newTitle = await Dialog.prompt('Ganti nama percakapan:', currentTitle);
    if (!newTitle || newTitle.trim() === currentTitle) return;
    try {
        const res = await fetch(`/chat/${id}/rename`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ title: newTitle.trim() }),
        });
        const data = await res.json();
        if (data.success) {
            updateSessionTitle(id, data.title);
            if (currentSessionId == id) chatTitle.textContent = data.title;
        }
    } catch { /* silent */ }
}

async function deleteSession(id, el) {
    const confirmed = await Dialog.danger('Hapus percakapan ini?');
    if (!confirmed) return;
    await fetch(`/chat/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
    el.remove();
    if (currentSessionId == id) {
        currentSessionId = null;
        msgBox.innerHTML = '';
        chatTitle.textContent = 'Percakapan Baru';
    }
    if (!sessionList.querySelector('.session-item')) {
        sessionList.innerHTML = '<p class="text-xs text-gray-400 px-3 py-3" id="empty-sessions-msg">Belum ada percakapan</p>';
    }
}

// ── Event listeners ───────────────────────────────────────────
btnSend.addEventListener('click', () => sendMessage(input.value));
input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(input.value); }
});
input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 160) + 'px';
});
document.getElementById('btn-new-chat').addEventListener('click', () => {
    currentSessionId = null;
    aiMessageCount = 0;
    chatTitle.textContent = 'Percakapan Baru';
    modelLabel.textContent = 'Qalcuity AI · Siap membantu';
    msgBox.innerHTML = `<div id="empty-state" class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-16"><p class="text-sm">Mulai percakapan baru...</p></div>`;
    document.querySelectorAll('.session-item').forEach(el => el.classList.remove('bg-blue-50'));
    input.focus();
});
document.querySelectorAll('.hint-btn').forEach(btn =>
    btn.addEventListener('click', () => {
        // Ambil teks dari span kedua saja (bukan emoji span pertama)
        const spans = btn.querySelectorAll('span');
        const text = spans.length > 1 ? spans[spans.length - 1].textContent.trim() : btn.textContent.trim();
        sendMessage(text);
    }));
document.querySelectorAll('.session-item').forEach(item => {
    item.querySelector('.session-btn')?.addEventListener('click', () =>
        loadSession(item.dataset.session, item.dataset.title));
    item.querySelector('.session-rename')?.addEventListener('click', e => {
        e.stopPropagation();
        renameSession(item.dataset.session, item);
    });
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

// ── Session search ────────────────────────────────────────────
const sessionSearch = document.getElementById('session-search');
if (sessionSearch) {
    sessionSearch.addEventListener('input', () => {
        const q = sessionSearch.value.toLowerCase().trim();
        document.querySelectorAll('.session-item').forEach(item => {
            const title = (item.dataset.title || '').toLowerCase();
            item.style.display = (!q || title.includes(q)) ? '' : 'none';
        });
    });
}

// ── Suggested follow-up ───────────────────────────────────────
const FOLLOW_UPS = [
    'Tampilkan dalam grafik',
    'Ekspor ke CSV',
    'Bandingkan dengan bulan lalu',
    'Buat laporan lengkap',
    'Apa rekomendasi Anda?',
    'Tampilkan detail lebih lanjut',
    'Buat invoice untuk ini',
    'Kirim ringkasan ke email saya',
];

let aiMessageCount = 0; // track jumlah pesan AI

function appendSuggestedFollowUps() {
    aiMessageCount++;
    if (aiMessageCount % 3 !== 0) return;

    const shuffled = [...FOLLOW_UPS].sort(() => Math.random() - 0.5).slice(0, 3);
    const wrap = document.createElement('div');
    wrap.className = 'flex justify-start max-w-4xl mx-auto w-full pl-10 mb-3 mt-1';
    const inner = document.createElement('div');
    inner.className = 'flex flex-wrap gap-1.5';
    shuffled.forEach(text => {
        const btn = document.createElement('button');
        btn.className = 'text-xs bg-white/5 border border-white/10 rounded-full px-3 py-1.5 text-slate-400 hover:border-blue-500/50 hover:text-blue-400 hover:bg-blue-500/10 transition';
        btn.textContent = text;
        btn.onclick = () => { wrap.remove(); sendMessage(text); };
        inner.appendChild(btn);
    });
    wrap.appendChild(inner);
    msgBox.appendChild(wrap);
    scrollBottom();
}

input.focus();

// ── Pre-fill from URL ?q= param (e.g. from dashboard insight links) ──────────
(function () {
    const params = new URLSearchParams(window.location.search);
    const q = params.get('q');
    if (q && q.trim()) {
        input.value = q.trim();
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 160) + 'px';
        // Auto-send after short delay so UI is ready
        setTimeout(() => sendMessage(q.trim()), 400);
        // Clean URL without reload
        history.replaceState(null, '', window.location.pathname);
    }
})();
