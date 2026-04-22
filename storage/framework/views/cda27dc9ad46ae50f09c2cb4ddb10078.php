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
     <?php $__env->slot('header', null, []); ?> Mode Lapangan <?php $__env->endSlot(); ?>

    <style>
        .mobile-hub-page {
            min-height: 100vh;
            background: #030712;
            padding: 0 0 2rem;
        }

        /* ── Header ── */
        .mob-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            padding: 1.25rem 1.25rem 1rem;
        }

        .mob-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .mob-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.01em;
        }

        .mob-user-info {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .mob-avatar {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .mob-user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #e2e8f0;
            line-height: 1.2;
        }

        .mob-role-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
            margin-top: 0.15rem;
        }

        /* ── Stats Strip ── */
        .mob-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.625rem;
            padding: 0.875rem 1.25rem 0;
        }

        .mob-stat-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.875rem;
            padding: 0.625rem 0.5rem;
            text-align: center;
        }

        .mob-stat-num {
            font-size: 1.4rem;
            font-weight: 800;
            color: #f1f5f9;
            line-height: 1;
        }

        .mob-stat-label {
            font-size: 0.6rem;
            font-weight: 500;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 0.25rem;
        }

        /* ── Section Label ── */
        .mob-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 1.25rem 1.25rem 0.625rem;
        }

        /* ── Action Grid ── */
        .mob-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.875rem;
            padding: 0 1.25rem;
        }

        .mob-action-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-height: 9rem;
            padding: 1.25rem 1rem;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            text-decoration: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background 0.18s, border-color 0.18s, transform 0.12s;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-action-card:active {
            transform: scale(0.96);
        }

        .mob-action-card:hover {
            background: #263449;
            border-color: rgba(255, 255, 255, 0.14);
        }

        .mob-action-card.disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .mob-card-glow {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            opacity: 0.12;
            filter: blur(18px);
        }

        .mob-card-icon {
            font-size: 2rem;
            line-height: 1;
            margin-bottom: 0.75rem;
            position: relative;
        }

        .mob-card-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.3;
            position: relative;
        }

        .mob-card-desc {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 0.25rem;
            line-height: 1.4;
            position: relative;
        }

        .mob-card-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            font-size: 0.6rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ef4444;
            color: #fff;
        }

        .mob-card-badge.zero {
            background: rgba(255, 255, 255, 0.08);
            color: #475569;
        }

        /* Color accents per card */
        .card-opname .mob-card-glow {
            background: #22d3ee;
        }

        .card-picking .mob-card-glow {
            background: #3b82f6;
        }

        .card-transfer .mob-card-glow {
            background: #a855f7;
        }

        .card-farm .mob-card-glow {
            background: #22c55e;
        }

        /* ── Back Link ── */
        .mob-back-wrap {
            padding: 1.5rem 1.25rem 0;
        }

        .mob-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8rem;
            color: #475569;
            text-decoration: none;
            padding: 0.5rem 0.875rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.07);
            background: rgba(255, 255, 255, 0.03);
            transition: color 0.15s, background 0.15s;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-back-link:hover {
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.06);
        }
    </style>

    <div class="mobile-hub-page">

        
        <div id="offline-banner" style="display:none;" class="px-4 py-2 bg-amber-500/20 border-b border-amber-500/30">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-medium text-amber-300">⚠️ Mode Offline — Perubahan akan disinkronisasi saat
                    online</span>
                <button onclick="forceSync()"
                    class="text-xs px-2 py-1 rounded bg-amber-500/30 hover:bg-amber-500/40 text-amber-200 transition">Sinkronkan</button>
            </div>
        </div>

        
        <div class="mob-header">
            <div class="mob-header-top">
                <span class="mob-title">📱 Mode Lapangan</span>
                <div class="mob-user-info">
                    <div class="mob-avatar"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                    <div>
                        <div class="mob-user-name"><?php echo e($user->name); ?></div>
                        <div class="mob-role-badge"><?php echo e(ucfirst($user->role ?? 'staff')); ?></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mob-stats">
            <div class="mob-stat-card">
                <div class="mob-stat-num" style="color:#60a5fa"><?php echo e($pendingPicking); ?></div>
                <div class="mob-stat-label">Picking Aktif</div>
            </div>
            <div class="mob-stat-card">
                <div class="mob-stat-num" style="color:#34d399"><?php echo e($pendingOpname); ?></div>
                <div class="mob-stat-label">Opname Aktif</div>
            </div>
            <div class="mob-stat-card">
                <div class="mob-stat-num" style="color:#fb923c"><?php echo e($myPicking); ?></div>
                <div class="mob-stat-label">Tugas Saya</div>
            </div>
        </div>

        
        <p class="mob-section-label">Pilih Aktivitas</p>
        <div class="mob-actions-grid">

            
            <a href="<?php echo e(route('mobile.opname')); ?>" class="mob-action-card card-opname">
                <div class="mob-card-glow"></div>
                <?php if($pendingOpname > 0): ?>
                    <span class="mob-card-badge"><?php echo e($pendingOpname); ?></span>
                <?php else: ?>
                    <span class="mob-card-badge zero">0</span>
                <?php endif; ?>
                <span class="mob-card-icon">📋</span>
                <span class="mob-card-title">Stock Opname</span>
                <span class="mob-card-desc">Hitung & verifikasi stok fisik gudang</span>
            </a>

            
            <a href="<?php echo e(route('mobile.picking')); ?>" class="mob-action-card card-picking">
                <div class="mob-card-glow"></div>
                <?php if($pendingPicking > 0): ?>
                    <span class="mob-card-badge"><?php echo e($pendingPicking); ?></span>
                <?php else: ?>
                    <span class="mob-card-badge zero">0</span>
                <?php endif; ?>
                <span class="mob-card-icon">🛒</span>
                <span class="mob-card-title">Picking</span>
                <span class="mob-card-desc">Ambil barang dari bin untuk pengiriman</span>
            </a>

            
            <div class="mob-action-card card-transfer disabled">
                <div class="mob-card-glow"></div>
                <span class="mob-card-icon">↔️</span>
                <span class="mob-card-title">Transfer Stok</span>
                <span class="mob-card-desc">Pindah stok antar gudang / bin</span>
            </div>

            
            <a href="<?php echo e(route('mobile.farm-activity')); ?>" class="mob-action-card card-farm">
                <div class="mob-card-glow"></div>
                <span class="mob-card-icon">🌾</span>
                <span class="mob-card-title">Aktivitas Lahan</span>
                <span class="mob-card-desc">Catat kegiatan & panen di lahan</span>
            </a>

        </div>

        
        <div class="mob-back-wrap">
            <a href="<?php echo e(route('dashboard')); ?>" class="mob-back-link">
                ← Kembali ke Dashboard
            </a>
        </div>

    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // ── Offline detection & banner ───────────────────────────────────────
            const offlineBanner = document.getElementById('offline-banner');

            function updateOfflineBanner() {
                if (offlineBanner) {
                    offlineBanner.style.display = navigator.onLine ? 'none' : 'block';
                }
            }

            window.addEventListener('online', () => {
                updateOfflineBanner();
                if (window.ErpOffline) {
                    window.ErpOffline.flush().then(synced => {
                        if (synced > 0) {
                            showToast(`${synced} perubahan berhasil disinkronisasi`, 'success');
                        }
                    });
                }
            });

            window.addEventListener('offline', () => {
                updateOfflineBanner();
                showToast('Anda offline. Perubahan akan disimpan sementara.', 'warning');
            });

            // Force sync button handler
            window.forceSync = async function() {
                if (!window.ErpOffline) return;
                const pending = await window.ErpOffline.pendingCount();
                if (pending === 0) {
                    showToast('Tidak ada data tertunda', 'info');
                    return;
                }
                showToast(`Menyinkronkan ${pending} perubahan...`, 'info');
                const synced = await window.ErpOffline.flush();
                if (synced > 0) {
                    showToast(`${synced} berhasil disinkronisasi`, 'success');
                } else {
                    showToast('Gagal sinkronisasi. Periksa koneksi.', 'error');
                }
            };

            // Simple toast
            function showToast(msg, type) {
                const colors = {
                    success: '#059669',
                    warning: '#d97706',
                    error: '#dc2626',
                    info: '#2563eb'
                };
                const t = document.createElement('div');
                t.style.cssText = `position:fixed;top:1rem;left:1rem;right:1rem;z-index:9999;
                    padding:0.875rem 1rem;border-radius:1rem;color:#fff;font-size:0.875rem;font-weight:500;
                    background:${colors[type]||colors.info};box-shadow:0 4px 20px rgba(0,0,0,0.4);
                    opacity:0;transition:opacity 0.25s;text-align:center;`;
                t.textContent = msg;
                document.body.appendChild(t);
                requestAnimationFrame(() => {
                    t.style.opacity = '1';
                });
                setTimeout(() => {
                    t.style.opacity = '0';
                    setTimeout(() => t.remove(), 300);
                }, 3500);
            }

            // Init
            updateOfflineBanner();
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\mobile\hub.blade.php ENDPATH**/ ?>