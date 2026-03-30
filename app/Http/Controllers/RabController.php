<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\RabItem;
use Illuminate\Http\Request;

class RabController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    /**
     * RAB page for a project.
     */
    public function index(Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $tree = RabItem::tree($project->id);

        $summary = [
            'total_rab'    => RabItem::where('project_id', $project->id)->whereNull('parent_id')->sum('subtotal'),
            'total_actual' => RabItem::where('project_id', $project->id)->where('type', 'item')->sum('actual_cost'),
            'item_count'   => RabItem::where('project_id', $project->id)->where('type', 'item')->count(),
            'group_count'  => RabItem::where('project_id', $project->id)->where('type', 'group')->count(),
        ];

        // Category breakdown
        $byCategory = RabItem::where('project_id', $project->id)
            ->where('type', 'item')
            ->selectRaw("COALESCE(category, 'lainnya') as cat, SUM(subtotal) as total_rab, SUM(actual_cost) as total_actual")
            ->groupBy('cat')
            ->get();

        return view('projects.rab', compact('project', 'tree', 'summary', 'byCategory'));
    }

    /**
     * Add a group or item.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'parent_id'   => 'nullable|exists:rab_items,id',
            'code'        => 'nullable|string|max:30',
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:group,item',
            'category'    => 'nullable|string|max:100',
            'volume'      => 'nullable|numeric|min:0',
            'unit'        => 'nullable|string|max:30',
            'unit_price'  => 'nullable|numeric|min:0',
            'coefficient' => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $maxSort = RabItem::where('project_id', $project->id)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order') ?? 0;

        RabItem::create([
            'project_id'  => $project->id,
            'tenant_id'   => $this->tid(),
            'parent_id'   => $data['parent_id'] ?? null,
            'code'        => $data['code'] ?? null,
            'name'        => $data['name'],
            'type'        => $data['type'],
            'category'    => $data['category'] ?? null,
            'volume'      => (float) ($data['volume'] ?? 0),
            'unit'        => $data['unit'] ?? null,
            'unit_price'  => (float) ($data['unit_price'] ?? 0),
            'coefficient' => (float) ($data['coefficient'] ?? 1),
            'sort_order'  => $maxSort + 1,
            'notes'       => $data['notes'] ?? null,
        ]);

        RabItem::recalculateProject($project->id);

        ActivityLog::record('rab_item_added', "RAB item ditambah: {$data['name']} di proyek {$project->name}");

        return back()->with('success', 'Item RAB berhasil ditambahkan.');
    }

    /**
     * Update an item.
     */
    public function update(Request $request, RabItem $rabItem)
    {
        abort_unless($rabItem->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'code'        => 'nullable|string|max:30',
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'volume'      => 'nullable|numeric|min:0',
            'unit'        => 'nullable|string|max:30',
            'unit_price'  => 'nullable|numeric|min:0',
            'coefficient' => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $rabItem->update([
            'code'        => $data['code'] ?? $rabItem->code,
            'name'        => $data['name'],
            'category'    => $data['category'] ?? $rabItem->category,
            'volume'      => (float) ($data['volume'] ?? $rabItem->volume),
            'unit'        => $data['unit'] ?? $rabItem->unit,
            'unit_price'  => (float) ($data['unit_price'] ?? $rabItem->unit_price),
            'coefficient' => (float) ($data['coefficient'] ?? $rabItem->coefficient),
            'notes'       => $data['notes'] ?? $rabItem->notes,
        ]);

        RabItem::recalculateProject($rabItem->project_id);

        return back()->with('success', 'Item RAB berhasil diperbarui.');
    }

    /**
     * Record actual cost/volume realization.
     */
    public function recordActual(Request $request, RabItem $rabItem)
    {
        abort_unless($rabItem->tenant_id === $this->tid(), 403);
        abort_if($rabItem->type !== 'item', 422, 'Hanya item yang bisa dicatat realisasinya.');

        $data = $request->validate([
            'actual_cost'   => 'nullable|numeric|min:0',
            'actual_volume' => 'nullable|numeric|min:0',
        ]);

        $rabItem->update([
            'actual_cost'   => (float) ($data['actual_cost'] ?? $rabItem->actual_cost),
            'actual_volume' => (float) ($data['actual_volume'] ?? $rabItem->actual_volume),
        ]);

        RabItem::recalculateProject($rabItem->project_id);

        // Also update project actual_cost
        $project = $rabItem->project;
        $project->recalculateActualCost();

        return back()->with('success', 'Realisasi berhasil dicatat.');
    }

    /**
     * Delete an item or group (cascade children).
     */
    public function destroy(RabItem $rabItem)
    {
        abort_unless($rabItem->tenant_id === $this->tid(), 403);

        $projectId = $rabItem->project_id;
        $name = $rabItem->name;

        // Delete children first
        $rabItem->children()->delete();
        $rabItem->delete();

        RabItem::recalculateProject($projectId);

        ActivityLog::record('rab_item_deleted', "RAB item dihapus: {$name}");

        return back()->with('success', 'Item RAB berhasil dihapus.');
    }

    /**
     * Export RAB as CSV.
     */
    public function export(Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $items = RabItem::where('project_id', $project->id)
            ->orderBy('sort_order')
            ->get();

        $csv = "\xEF\xBB\xBF"; // BOM
        $csv .= "Kode,Uraian Pekerjaan,Tipe,Kategori,Volume,Satuan,Harga Satuan,Koefisien,Jumlah (RAB),Realisasi Biaya,Realisasi Volume,Catatan\n";

        foreach ($this->flattenTree(RabItem::tree($project->id)) as $item) {
            $indent = $item->type === 'group' ? '' : '  ';
            $csv .= implode(',', [
                $item->code ?? '',
                '"' . $indent . str_replace('"', '""', $item->name) . '"',
                $item->type,
                $item->category ?? '',
                $item->type === 'item' ? $item->volume : '',
                $item->unit ?? '',
                $item->type === 'item' ? $item->unit_price : '',
                $item->type === 'item' ? $item->coefficient : '',
                $item->subtotal,
                $item->actual_cost,
                $item->type === 'item' ? $item->actual_volume : '',
                '"' . str_replace('"', '""', $item->notes ?? '') . '"',
            ]) . "\n";
        }

        $filename = "RAB-{$project->number}-" . date('Ymd') . ".csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import RAB from CSV.
     */
    public function import(Request $request, Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $file = $request->file('file');
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($handle);
            while (($row = fgetcsv($handle)) !== false) $rows[] = $row;
            fclose($handle);
        }

        if (count($rows) < 2) {
            return back()->with('error', 'File kosong atau format tidak valid.');
        }

        $headers = array_map(fn ($h) => strtolower(trim($h)), $rows[0]);
        $created = 0;
        $parentStack = []; // track hierarchy by code depth

        foreach (array_slice($rows, 1) as $row) {
            if (count($row) < 2 || empty(trim($row[1] ?? ''))) continue;

            $data = array_combine($headers, array_pad(array_map('trim', $row), count($headers), ''));

            $code      = $data['kode'] ?? $data['code'] ?? '';
            $name      = $data['uraian pekerjaan'] ?? $data['name'] ?? $data['uraian'] ?? '';
            $type      = strtolower($data['tipe'] ?? $data['type'] ?? 'item');
            $category  = $data['kategori'] ?? $data['category'] ?? null;
            $volume    = (float) ($data['volume'] ?? 0);
            $unit      = $data['satuan'] ?? $data['unit'] ?? null;
            $unitPrice = (float) ($data['harga satuan'] ?? $data['unit_price'] ?? 0);
            $coeff     = (float) ($data['koefisien'] ?? $data['coefficient'] ?? 1);
            $notes     = $data['catatan'] ?? $data['notes'] ?? null;

            if (!$name) continue;
            $type = in_array($type, ['group', 'grup', 'header']) ? 'group' : 'item';

            // Determine parent from code hierarchy
            $parentId = null;
            $depth = substr_count($code, '.');
            if ($depth > 0 && !empty($parentStack)) {
                $parentId = $parentStack[$depth - 1] ?? end($parentStack);
            }

            $item = RabItem::create([
                'project_id'  => $project->id,
                'tenant_id'   => $this->tid(),
                'parent_id'   => $parentId,
                'code'        => $code ?: null,
                'name'        => $name,
                'type'        => $type,
                'category'    => $category ?: null,
                'volume'      => $volume,
                'unit'        => $unit ?: null,
                'unit_price'  => $unitPrice,
                'coefficient' => $coeff ?: 1,
                'sort_order'  => $created,
                'notes'       => $notes ?: null,
            ]);

            if ($type === 'group') {
                $parentStack[$depth] = $item->id;
            }

            $created++;
        }

        RabItem::recalculateProject($project->id);

        ActivityLog::record('rab_imported', "RAB diimport: {$created} item ke proyek {$project->name}");

        return back()->with('success', "{$created} item RAB berhasil diimport.");
    }

    /**
     * Flatten tree for export.
     */
    private function flattenTree($items, array &$result = []): array
    {
        foreach ($items as $item) {
            $result[] = $item;
            if ($item->children->isNotEmpty()) {
                $this->flattenTree($item->children, $result);
            }
        }
        return $result;
    }
}
