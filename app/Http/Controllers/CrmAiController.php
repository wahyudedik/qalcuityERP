<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Services\CrmAiService;

class CrmAiController extends Controller
{
    public function __construct(private CrmAiService $ai) {}

    // tenantId() inherited from parent Controller

    public function scoreLead(CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);
        $lead->load('activities');

        return response()->json($this->ai->scoreLead($lead));
    }

    public function followUp(CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);
        $lead->load('activities');

        return response()->json($this->ai->suggestFollowUp($lead));
    }

    public function scoreAll()
    {
        return response()->json($this->ai->scoreAll($this->tenantId()));
    }
}
