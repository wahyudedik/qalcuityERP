<?php

namespace App\Services\ERP;

use App\Models\Reminder;
use Carbon\Carbon;

class ReminderTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'set_reminder',
                'description' => 'Buat pengingat/reminder untuk user. Gunakan untuk: '
                    . '"ingatkan saya bayar hutang ke PT X besok", "jadwalkan follow-up Budi 3 hari lagi", '
                    . '"reminder meeting Senin jam 9", "ingatkan cek stok minggu depan", '
                    . '"set alarm tagihan jatuh tempo tanggal 25".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'title'        => ['type' => 'string', 'description' => 'Judul reminder (wajib)'],
                        'notes'        => ['type' => 'string', 'description' => 'Catatan tambahan (opsional)'],
                        'remind_at'    => ['type' => 'string', 'description' => 'Waktu pengingat: "besok", "3 hari lagi", "Senin", "2026-03-25 09:00", "25 Maret jam 10"'],
                        'channel'      => ['type' => 'string', 'description' => 'Saluran: in_app, email, both (default: both)'],
                        'related_type' => ['type' => 'string', 'description' => 'Tipe terkait: payable, lead, customer, invoice (opsional)'],
                    ],
                    'required' => ['title', 'remind_at'],
                ],
            ],
            [
                'name'        => 'list_reminders',
                'description' => 'Tampilkan daftar reminder yang aktif/pending. Gunakan untuk: '
                    . '"reminder saya apa saja?", "jadwal pengingat minggu ini", "ada reminder apa?".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'filter' => ['type' => 'string', 'description' => 'Filter: upcoming (default), today, all'],
                    ],
                ],
            ],
            [
                'name'        => 'dismiss_reminder',
                'description' => 'Tandai reminder sebagai selesai/dismiss. Gunakan untuk: "hapus reminder X", "reminder sudah selesai".',
                'parameters' => [
                    'type'       => 'object',
                    'properties' => [
                        'reminder_id' => ['type' => 'integer', 'description' => 'ID reminder yang akan di-dismiss'],
                        'title'       => ['type' => 'string', 'description' => 'Judul reminder (alternatif jika tidak tahu ID)'],
                    ],
                ],
            ],
        ];
    }

    public function setReminder(array $args): array
    {
        $remindAt = $this->parseRemindAt($args['remind_at'] ?? 'besok');

        $reminder = Reminder::create([
            'tenant_id'    => $this->tenantId,
            'user_id'      => $this->userId,
            'title'        => $args['title'],
            'notes'        => $args['notes'] ?? null,
            'remind_at'    => $remindAt,
            'channel'      => $args['channel'] ?? 'both',
            'related_type' => $args['related_type'] ?? null,
            'status'       => 'pending',
        ]);

        return [
            'status'     => 'success',
            'message'    => "Reminder \"{$reminder->title}\" berhasil dibuat.",
            'id'         => $reminder->id,
            'title'      => $reminder->title,
            'remind_at'  => $remindAt->format('d M Y, H:i'),
            'channel'    => $reminder->channel,
        ];
    }

    public function listReminders(array $args): array
    {
        $filter = $args['filter'] ?? 'upcoming';

        $query = Reminder::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('status', 'pending')
            ->orderBy('remind_at');

        if ($filter === 'today') {
            $query->whereDate('remind_at', today());
        } elseif ($filter === 'upcoming') {
            $query->where('remind_at', '>=', now());
        }

        $reminders = $query->limit(20)->get();

        if ($reminders->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada reminder aktif.', 'data' => []];
        }

        return [
            'status' => 'success',
            'data'   => $reminders->map(fn($r) => [
                'id'        => $r->id,
                'title'     => $r->title,
                'notes'     => $r->notes,
                'remind_at' => $r->remind_at->format('d M Y, H:i'),
                'channel'   => $r->channel,
                'overdue'   => $r->remind_at->isPast() ? 'Ya' : 'Tidak',
            ])->toArray(),
        ];
    }

    public function dismissReminder(array $args): array
    {
        $query = Reminder::where('tenant_id', $this->tenantId)->where('user_id', $this->userId);

        if (!empty($args['reminder_id'])) {
            $reminder = $query->find($args['reminder_id']);
        } else {
            $reminder = $query->where('title', 'like', '%' . ($args['title'] ?? '') . '%')->first();
        }

        if (!$reminder) {
            return ['status' => 'error', 'message' => 'Reminder tidak ditemukan.'];
        }

        $reminder->update(['status' => 'dismissed']);

        return ['status' => 'success', 'message' => "Reminder \"{$reminder->title}\" telah diselesaikan."];
    }

    private function parseRemindAt(string $input): Carbon
    {
        $input = strtolower(trim($input));

        // Natural language parsing
        if (str_contains($input, 'besok')) return now()->addDay()->setTime(9, 0);
        if (str_contains($input, 'lusa')) return now()->addDays(2)->setTime(9, 0);
        if (preg_match('/(\d+)\s*hari\s*lagi/', $input, $m)) return now()->addDays((int)$m[1])->setTime(9, 0);
        if (preg_match('/(\d+)\s*minggu\s*lagi/', $input, $m)) return now()->addWeeks((int)$m[1])->setTime(9, 0);
        if (preg_match('/(\d+)\s*jam\s*lagi/', $input, $m)) return now()->addHours((int)$m[1]);

        // Day names
        $days = ['senin'=>1,'selasa'=>2,'rabu'=>3,'kamis'=>4,'jumat'=>5,'sabtu'=>6,'minggu'=>0];
        foreach ($days as $name => $dow) {
            if (str_contains($input, $name)) {
                $next = now()->next($dow);
                // Extract time if present
                if (preg_match('/jam\s*(\d+)(?::(\d+))?/', $input, $m)) {
                    $next->setTime((int)$m[1], isset($m[2]) ? (int)$m[2] : 0);
                } else {
                    $next->setTime(9, 0);
                }
                return $next;
            }
        }

        // Try Carbon parse
        try {
            return Carbon::parse($input);
        } catch (\Throwable) {
            return now()->addDay()->setTime(9, 0);
        }
    }
}
