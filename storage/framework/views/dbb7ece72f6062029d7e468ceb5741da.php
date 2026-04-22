<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Mix Design Calculation - <?php echo e($mixDesign->grade); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #2563eb;
            margin: 0;
        }

        .header p {
            color: #666;
            margin: 5px 0;
        }

        .section {
            margin-bottom: 25px;
        }

        .section h2 {
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .total-row {
            background: #eff6ff;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🧮 Mix Design Calculation Report</h1>
        <p><strong><?php echo e($mixDesign->grade); ?></strong> - <?php echo e($mixDesign->name); ?></p>
        <p>Generated: <?php echo e(now()->format('d M Y H:i:s')); ?></p>
    </div>

    <div class="section">
        <h2>Mix Design Specifications</h2>
        <table>
            <tr>
                <th>Grade</th>
                <td><?php echo e($mixDesign->grade); ?></td>
                <th>Target Strength</th>
                <td><?php echo e($mixDesign->target_strength); ?> <?php echo e($mixDesign->strength_unit); ?></td>
            </tr>
            <tr>
                <th>W/C Ratio</th>
                <td><?php echo e($mixDesign->water_cement_ratio); ?></td>
                <th>Cement Type</th>
                <td><?php echo e($mixDesign->cement_type); ?></td>
            </tr>
            <tr>
                <th>Slump Range</th>
                <td><?php echo e($mixDesign->slump_min); ?> - <?php echo e($mixDesign->slump_max); ?> cm</td>
                <th>Aggregate Size</th>
                <td><?php echo e($mixDesign->agg_max_size); ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Calculation Parameters</h2>
        <table>
            <tr>
                <th>Volume</th>
                <td class="text-right"><strong><?php echo e(number_format($volume, 2)); ?> m³</strong></td>
            </tr>
            <tr>
                <th>Waste Factor</th>
                <td class="text-right"><?php echo e($waste); ?>%</td>
            </tr>
            <tr>
                <th>Waste Multiplier</th>
                <td class="text-right"><?php echo e(number_format($calculation['adjusted']['waste_multiplier'], 2)); ?>x</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Material Requirements</h2>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="text-right">Base (per m³)</th>
                    <th class="text-right">With Waste</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Unit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>🏭 Semen</strong></td>
                    <td class="text-right"><?php echo e(number_format($mixDesign->cement_kg, 1)); ?></td>
                    <td class="text-right">
                        <?php echo e(number_format($calculation['adjusted']['cement_kg'] - $calculation['base']['cement_kg'], 1)); ?>

                    </td>
                    <td class="text-right">
                        <strong><?php echo e(number_format($calculation['adjusted']['cement_kg'], 1)); ?></strong></td>
                    <td class="text-center">kg</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;└ In sacks (50kg)</td>
                    <td class="text-right"><?php echo e(ceil($mixDesign->cement_kg / 50)); ?></td>
                    <td class="text-right">-</td>
                    <td class="text-right"><strong><?php echo e($calculation['adjusted']['cement_sak']); ?></strong></td>
                    <td class="text-center">sak</td>
                </tr>
                <tr>
                    <td><strong>💧 Air</strong></td>
                    <td class="text-right"><?php echo e(number_format($mixDesign->water_liter, 1)); ?></td>
                    <td class="text-right">
                        <?php echo e(number_format($calculation['adjusted']['water_liter'] - $calculation['base']['water_liter'], 1)); ?>

                    </td>
                    <td class="text-right">
                        <strong><?php echo e(number_format($calculation['adjusted']['water_liter'], 1)); ?></strong></td>
                    <td class="text-center">liter</td>
                </tr>
                <tr>
                    <td><strong>🪨 Pasir (Fine Aggregate)</strong></td>
                    <td class="text-right"><?php echo e(number_format($mixDesign->fine_agg_kg, 1)); ?></td>
                    <td class="text-right">
                        <?php echo e(number_format($calculation['adjusted']['fine_agg_kg'] - $calculation['base']['fine_agg_kg'], 1)); ?>

                    </td>
                    <td class="text-right">
                        <strong><?php echo e(number_format($calculation['adjusted']['fine_agg_kg'], 1)); ?></strong></td>
                    <td class="text-center">kg</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;└ In volume</td>
                    <td class="text-right"><?php echo e(number_format($mixDesign->fine_agg_kg / 1400, 2)); ?></td>
                    <td class="text-right">-</td>
                    <td class="text-right">
                        <strong><?php echo e(number_format($calculation['adjusted']['fine_agg_m3'], 2)); ?></strong></td>
                    <td class="text-center">m³</td>
                </tr>
                <tr>
                    <td><strong>🪨 Split (Coarse Aggregate)</strong></td>
                    <td class="text-right"><?php echo e(number_format($mixDesign->coarse_agg_kg, 1)); ?></td>
                    <td class="text-right">
                        <?php echo e(number_format($calculation['adjusted']['coarse_agg_kg'] - $calculation['base']['coarse_agg_kg'], 1)); ?>

                    </td>
                    <td class="text-right">
                        <strong><?php echo e(number_format($calculation['adjusted']['coarse_agg_kg'], 1)); ?></strong></td>
                    <td class="text-center">kg</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;└ In volume</td>
                    <td class="text-right"><?php echo e(number_format($mixDesign->coarse_agg_kg / 1500, 2)); ?></td>
                    <td class="text-right">-</td>
                    <td class="text-right">
                        <strong><?php echo e(number_format($calculation['adjusted']['coarse_agg_m3'], 2)); ?></strong></td>
                    <td class="text-center">m³</td>
                </tr>
                <?php if($mixDesign->admixture_liter > 0): ?>
                    <tr>
                        <td><strong>⚗️ Admixture</strong></td>
                        <td class="text-right"><?php echo e(number_format($mixDesign->admixture_liter, 3)); ?></td>
                        <td class="text-right">
                            <?php echo e(number_format($calculation['adjusted']['admixture_liter'] - $calculation['base']['admixture_liter'], 2)); ?>

                        </td>
                        <td class="text-right">
                            <strong><?php echo e(number_format($calculation['adjusted']['admixture_liter'], 2)); ?></strong></td>
                        <td class="text-center">liter</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Cost Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="text-right">Cost (Rp)</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>🏭 Semen</td>
                    <td class="text-right">Rp <?php echo e(number_format($costAnalysis['cost_per_m3']['cement'], 0, ',', '.')); ?>

                    </td>
                    <td class="text-right"><?php echo e($costAnalysis['breakdown_percent']['cement']); ?>%</td>
                </tr>
                <tr>
                    <td>💧 Air</td>
                    <td class="text-right">Rp <?php echo e(number_format($costAnalysis['cost_per_m3']['water'], 0, ',', '.')); ?>

                    </td>
                    <td class="text-right"><?php echo e($costAnalysis['breakdown_percent']['water']); ?>%</td>
                </tr>
                <tr>
                    <td>🪨 Pasir</td>
                    <td class="text-right">Rp
                        <?php echo e(number_format($costAnalysis['cost_per_m3']['fine_agg'], 0, ',', '.')); ?></td>
                    <td class="text-right"><?php echo e($costAnalysis['breakdown_percent']['fine_agg']); ?>%</td>
                </tr>
                <tr>
                    <td>🪨 Split</td>
                    <td class="text-right">Rp
                        <?php echo e(number_format($costAnalysis['cost_per_m3']['coarse_agg'], 0, ',', '.')); ?></td>
                    <td class="text-right"><?php echo e($costAnalysis['breakdown_percent']['coarse_agg']); ?>%</td>
                </tr>
                <?php if($costAnalysis['cost_per_m3']['admixture'] > 0): ?>
                    <tr>
                        <td>⚗️ Admixture</td>
                        <td class="text-right">Rp
                            <?php echo e(number_format($costAnalysis['cost_per_m3']['admixture'], 0, ',', '.')); ?></td>
                        <td class="text-right"><?php echo e($costAnalysis['breakdown_percent']['admixture']); ?>%</td>
                    </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">Rp <?php echo e(number_format($costAnalysis['total_cost'], 0, ',', '.')); ?></td>
                    <td class="text-right">100%</td>
                </tr>
            </tbody>
        </table>
        <p><strong>Cost per m³:</strong> Rp <?php echo e(number_format($costAnalysis['cost_per_m3']['total'], 0, ',', '.')); ?></p>
    </div>

    <div class="section">
        <h2>Material Availability</h2>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="text-right">Required</th>
                    <th class="text-right">Available</th>
                    <th class="text-right">Shortage</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $availability['availability']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e(ucfirst(str_replace('_', ' ', $material))); ?></strong></td>
                        <td class="text-right"><?php echo e(number_format($data['required'], 1)); ?> <?php echo e($data['unit']); ?></td>
                        <td class="text-right"><?php echo e(number_format($data['available'], 1)); ?> <?php echo e($data['unit']); ?></td>
                        <td class="text-right"><?php echo e($data['shortage'] > 0 ? number_format($data['shortage'], 1) : '-'); ?>

                            <?php echo e($data['unit']); ?></td>
                        <td class="text-center">
                            <?php if($data['sufficient']): ?>
                                <span class="badge badge-green">✓ Sufficient</span>
                            <?php else: ?>
                                <span class="badge badge-red">✗ Shortage</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php if($availability['all_available']): ?>
            <p class="badge badge-green" style="display:inline-block; margin-top:10px;">✅ All materials are available
            </p>
        <?php else: ?>
            <p class="badge badge-red" style="display:inline-block; margin-top:10px;">⚠️ Some materials are insufficient
            </p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>QalcuityERP - Mix Design Calculation Report | <?php echo e(config('app.name')); ?></p>
        <p>This report was automatically generated on <?php echo e(now()->format('d M Y H:i:s')); ?></p>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pdf\mix-design-calculation.blade.php ENDPATH**/ ?>