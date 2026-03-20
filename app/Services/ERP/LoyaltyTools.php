<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;

class LoyaltyTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'setup_loyalty_program',
                'description' => 'Setup program loyalitas/poin untuk pelanggan.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'name'               => ['type' => 'string', 'description' => 'Nama program (misal: Poin Setia)'],
                        'points_per_idr'     => ['type' => 'number', 'description' => 'Poin per Rp 1 (misal: 0.01 = 1 poin per Rp 100)'],
                        'idr_per_point'      => ['type' => 'number', 'description' => 'Nilai 1 poin dalam Rp (misal: 100)'],
                        'min_redeem_points'  => ['type' => 'integer', 'description' => 'Minimum poin untuk redeem (default: 100)'],
                        'expiry_days'        => ['type' => 'integer', 'description' => 'Masa berlaku poin dalam hari (0 = tidak kadaluarsa)'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name'        => 'add_loyalty_points',
                'description' => 'Tambah poin loyalitas ke pelanggan setelah transaksi.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name'      => ['type' => 'string', 'description' => 'Nama pelanggan'],
                        'transaction_amount' => ['type' => 'number', 'description' => 'Jumlah transaksi (Rp) untuk hitung poin otomatis'],
                        'points_override'    => ['type' => 'integer', 'description' => 'Jumlah poin manual (opsional, override kalkulasi otomatis)'],
                        'reference'          => ['type' => 'string', 'description' => 'Nomor referensi transaksi (opsional)'],
                    ],
                    'required' => ['customer_name', 'transaction_amount'],
                ],
            ],
            [
                'name'        => 'redeem_loyalty_points',
                'description' => 'Tukarkan poin pelanggan menjadi diskon/voucher.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Nama pelanggan'],
                        'points'        => ['type' => 'integer', 'description' => 'Jumlah poin yang ditukar'],
                        'reference'     => ['type' => 'string', 'description' => 'Nomor referensi (opsional)'],
                    ],
                    'required' => ['customer_name', 'points'],
                ],
            ],
            [
                'name'        => 'get_customer_points',
                'description' => 'Cek saldo poin loyalitas pelanggan dan tier/level mereka.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'customer_name' => ['type' => 'string', 'description' => 'Nama pelanggan (kosong = top pelanggan)'],
                    ],
                ],
            ],
        ];
    }

    public function setupLoyaltyProgram(array $args): array
    {
        $existing = LoyaltyProgram::where('tenant_id', $this->tenantId)->where('is_active', true)->first();
        if ($existing) {
            $existing->update([
                'name'              => $args['name'],
                'points_per_idr'    => $args['points_per_idr'] ?? $existing->points_per_idr,
                'idr_per_point'     => $args['idr_per_point'] ?? $existing->idr_per_point,
                'min_redeem_points' => $args['min_redeem_points'] ?? $existing->min_redeem_points,
                'expiry_days'       => $args['expiry_days'] ?? $existing->expiry_days,
            ]);
            $program = $existing;
        } else {
            $program = LoyaltyProgram::create([
                'tenant_id'         => $this->tenantId,
                'name'              => $args['name'],
                'points_per_idr'    => $args['points_per_idr'] ?? 0.01,
                'idr_per_point'     => $args['idr_per_point'] ?? 100,
                'min_redeem_points' => $args['min_redeem_points'] ?? 100,
                'expiry_days'       => $args['expiry_days'] ?? 365,
                'is_active'         => true,
            ]);

            // Default tiers
            $tiers = [
                ['name' => 'Bronze', 'min_points' => 0,    'multiplier' => 1.0, 'color' => '#cd7f32'],
                ['name' => 'Silver', 'min_points' => 1000, 'multiplier' => 1.5, 'color' => '#c0c0c0'],
                ['name' => 'Gold',   'min_points' => 5000, 'multiplier' => 2.0, 'color' => '#ffd700'],
            ];
            foreach ($tiers as $tier) {
                LoyaltyTier::create(['tenant_id' => $this->tenantId, 'program_id' => $program->id, ...$tier]);
            }
        }

        return [
            'status'  => 'success',
            'message' => "Program loyalitas **{$program->name}** berhasil disetup.\n"
                . "1 poin = Rp " . number_format($program->idr_per_point, 0, ',', '.') . " | "
                . "Setiap Rp " . number_format(1 / $program->points_per_idr, 0, ',', '.') . " = 1 poin\n"
                . "Tier: Bronze → Silver (1.000 poin) → Gold (5.000 poin)",
        ];
    }

    public function addLoyaltyPoints(array $args): array
    {
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['customer_name']}%")
            ->first();

        if (!$customer) {
            return ['status' => 'error', 'message' => "Pelanggan '{$args['customer_name']}' tidak ditemukan."];
        }

        $program = LoyaltyProgram::where('tenant_id', $this->tenantId)->where('is_active', true)->first();
        if (!$program) {
            return ['status' => 'error', 'message' => 'Program loyalitas belum disetup. Gunakan setup_loyalty_program terlebih dahulu.'];
        }

        $points = $args['points_override'] ?? $program->calculatePoints($args['transaction_amount']);

        $loyaltyPoint = LoyaltyPoint::firstOrCreate(
            ['tenant_id' => $this->tenantId, 'customer_id' => $customer->id, 'program_id' => $program->id],
            ['total_points' => 0, 'lifetime_points' => 0, 'tier' => 'Bronze']
        );

        $loyaltyPoint->increment('total_points', $points);
        $loyaltyPoint->increment('lifetime_points', $points);
        $loyaltyPoint->refresh(); // reload setelah increment agar lifetime_points up-to-date

        // Update tier
        $newTier = $this->calculateTier($loyaltyPoint->lifetime_points, $program->id);
        $loyaltyPoint->update(['tier' => $newTier, 'tier_updated_at' => now()]);

        LoyaltyTransaction::create([
            'tenant_id'          => $this->tenantId,
            'customer_id'        => $customer->id,
            'program_id'         => $program->id,
            'type'               => 'earn',
            'points'             => $points,
            'transaction_amount' => $args['transaction_amount'],
            'reference'          => $args['reference'] ?? null,
        ]);

        return [
            'status'       => 'success',
            'message'      => "**{$customer->name}** mendapat **{$points} poin** dari transaksi Rp "
                . number_format($args['transaction_amount'], 0, ',', '.') . ".\n"
                . "Total poin: **{$loyaltyPoint->total_points}** | Tier: **{$newTier}**",
        ];
    }

    public function redeemLoyaltyPoints(array $args): array
    {
        $customer = Customer::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['customer_name']}%")
            ->first();

        if (!$customer) {
            return ['status' => 'error', 'message' => "Pelanggan '{$args['customer_name']}' tidak ditemukan."];
        }

        $program = LoyaltyProgram::where('tenant_id', $this->tenantId)->where('is_active', true)->first();
        if (!$program) {
            return ['status' => 'error', 'message' => 'Program loyalitas belum disetup.'];
        }

        $loyaltyPoint = LoyaltyPoint::where('tenant_id', $this->tenantId)
            ->where('customer_id', $customer->id)
            ->where('program_id', $program->id)
            ->first();

        if (!$loyaltyPoint || $loyaltyPoint->total_points < $args['points']) {
            return ['status' => 'error', 'message' => "Poin tidak cukup. Saldo: " . ($loyaltyPoint?->total_points ?? 0) . " poin."];
        }

        if ($args['points'] < $program->min_redeem_points) {
            return ['status' => 'error', 'message' => "Minimum redeem {$program->min_redeem_points} poin."];
        }

        $discountValue = $args['points'] * $program->idr_per_point;
        $loyaltyPoint->decrement('total_points', $args['points']);

        LoyaltyTransaction::create([
            'tenant_id'  => $this->tenantId,
            'customer_id'=> $customer->id,
            'program_id' => $program->id,
            'type'       => 'redeem',
            'points'     => -$args['points'],
            'reference'  => $args['reference'] ?? null,
        ]);

        return [
            'status'         => 'success',
            'discount_value' => 'Rp ' . number_format($discountValue, 0, ',', '.'),
            'message'        => "**{$args['points']} poin** {$customer->name} berhasil ditukar senilai **Rp "
                . number_format($discountValue, 0, ',', '.') . "**.\nSisa poin: **{$loyaltyPoint->total_points}**",
        ];
    }

    public function getCustomerPoints(array $args): array
    {
        $program = LoyaltyProgram::where('tenant_id', $this->tenantId)->where('is_active', true)->first();
        if (!$program) {
            return ['status' => 'error', 'message' => 'Program loyalitas belum disetup.'];
        }

        if (!empty($args['customer_name'])) {
            $customer = Customer::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['customer_name']}%")
                ->first();

            if (!$customer) {
                return ['status' => 'error', 'message' => "Pelanggan '{$args['customer_name']}' tidak ditemukan."];
            }

            $lp = LoyaltyPoint::where('tenant_id', $this->tenantId)
                ->where('customer_id', $customer->id)
                ->where('program_id', $program->id)
                ->first();

            return [
                'status' => 'success',
                'data'   => [
                    'pelanggan'      => $customer->name,
                    'poin_aktif'     => $lp?->total_points ?? 0,
                    'lifetime_poin'  => $lp?->lifetime_points ?? 0,
                    'tier'           => $lp?->tier ?? 'Bronze',
                    'nilai_poin'     => 'Rp ' . number_format(($lp?->total_points ?? 0) * $program->idr_per_point, 0, ',', '.'),
                ],
            ];
        }

        // Top customers by points
        $topPoints = LoyaltyPoint::where('tenant_id', $this->tenantId)
            ->where('program_id', $program->id)
            ->with('customer')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        return [
            'status' => 'success',
            'data'   => $topPoints->map(fn($lp) => [
                'pelanggan'  => $lp->customer->name,
                'poin'       => $lp->total_points,
                'tier'       => $lp->tier,
                'nilai'      => 'Rp ' . number_format($lp->total_points * $program->idr_per_point, 0, ',', '.'),
            ])->toArray(),
        ];
    }

    private function calculateTier(int $lifetimePoints, int $programId): string
    {
        $tiers = LoyaltyTier::where('program_id', $programId)
            ->orderByDesc('min_points')
            ->get();

        foreach ($tiers as $tier) {
            if ($lifetimePoints >= $tier->min_points) {
                return $tier->name;
            }
        }

        return 'Bronze';
    }
}
