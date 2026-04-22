
<?php
    $color = $tenant->letter_head_color ?? '#1d4ed8';
    $logoUrl = $tenant->logo ? Storage::disk('public')->url($tenant->logo) : null;
?>
<style>
    .lh-wrap { border-bottom: 3px solid <?php echo e($color); ?>; padding-bottom: 12px; margin-bottom: 18px; }
    .lh-inner { display: flex; justify-content: space-between; align-items: flex-start; }
    .lh-logo { max-height: 60px; max-width: 160px; object-fit: contain; }
    .lh-company { flex: 1; padding-left: <?php echo e($logoUrl ? '16px' : '0'); ?>; }
    .lh-company-name { font-size: 16px; font-weight: bold; color: <?php echo e($color); ?>; }
    .lh-company-tagline { font-size: 9px; color: #6b7280; margin-top: 1px; }
    .lh-company-detail { font-size: 9px; color: #374151; margin-top: 4px; line-height: 1.6; }
    .lh-doc-title { text-align: right; }
    .lh-doc-title h2 { font-size: 18px; font-weight: bold; color: <?php echo e($color); ?>; text-transform: uppercase; letter-spacing: 1px; }
    .lh-doc-title p { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .lh-npwp { font-size: 9px; color: #6b7280; margin-top: 2px; }
</style>

<div class="lh-wrap">
    <div class="lh-inner">
        <div style="display:flex;align-items:flex-start;">
            <?php if($logoUrl): ?>
            <img src="<?php echo e($logoUrl); ?>" class="lh-logo" alt="<?php echo e($tenant->name); ?>">
            <?php endif; ?>
            <div class="lh-company">
                <div class="lh-company-name"><?php echo e($tenant->name); ?></div>
                <?php if($tenant->tagline): ?>
                <div class="lh-company-tagline"><?php echo e($tenant->tagline); ?></div>
                <?php endif; ?>
                <div class="lh-company-detail">
                    <?php if($tenant->address): ?><?php echo e($tenant->address); ?><?php endif; ?>
                    <?php if($tenant->city || $tenant->province): ?>
                    , <?php echo e($tenant->city); ?><?php echo e($tenant->province ? ', ' . $tenant->province : ''); ?>

                    <?php endif; ?>
                    <?php if($tenant->postal_code): ?> <?php echo e($tenant->postal_code); ?><?php endif; ?>
                    <?php if($tenant->phone): ?><br>Telp: <?php echo e($tenant->phone); ?><?php endif; ?>
                    <?php if($tenant->email): ?> | Email: <?php echo e($tenant->email); ?><?php endif; ?>
                    <?php if($tenant->website): ?><br><?php echo e($tenant->website); ?><?php endif; ?>
                </div>
                <?php if($tenant->npwp): ?>
                <div class="lh-npwp">NPWP: <?php echo e($tenant->npwp); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if(isset($docTitle)): ?>
        <div class="lh-doc-title">
            <h2><?php echo e($docTitle); ?></h2>
            <?php if(isset($docSubtitle)): ?><p><?php echo e($docSubtitle); ?></p><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\partials\pdf-letterhead.blade.php ENDPATH**/ ?>