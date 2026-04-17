<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiModelSwitchLog;
use App\Services\AI\ModelSwitcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiModelController extends Controller
{
    public function __construct(
        private readonly ModelSwitcher $switcher,
    ) {}

    public function index(): View
    {
        $activeModel = $this->switcher->getActiveModel();
        $modelAvailability = $this->switcher->getModelAvailability();
        $switchLogs = AiModelSwitchLog::recent()->latest('switched_at')->paginate(20);

        return view('super-admin.ai-model.index', compact(
            'activeModel',
            'modelAvailability',
            'switchLogs',
        ));
    }

    public function reset(): RedirectResponse
    {
        $this->switcher->resetAll();

        return redirect()->back()->with('success', 'Semua cooldown model AI telah direset.');
    }
}
