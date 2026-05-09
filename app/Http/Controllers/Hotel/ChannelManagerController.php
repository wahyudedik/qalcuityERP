<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ChannelManagerConfig;
use App\Models\ChannelManagerLog;
use App\Services\ChannelManagerService;
use Illuminate\Http\Request;

class ChannelManagerController extends Controller
{
    private ChannelManagerService $channelService;

    public function __construct(ChannelManagerService $channelService)
    {
        $this->channelService = $channelService;
    }

    // tenantId() inherited from parent Controller

    public function index()
    {
        $tid = $this->tenantId();

        // Get all channel configs for tenant
        $configs = ChannelManagerConfig::where('tenant_id', $tid)->get()->keyBy('channel');

        // Supported channels
        $channels = ['bookingcom', 'agoda', 'expedia', 'airbnb', 'tripadvisor', 'direct'];

        return view('hotel.channels.index', compact('configs', 'channels'));
    }

    public function configure(string $channel)
    {
        $tid = $this->tenantId();

        // Validate channel
        if (! in_array($channel, ChannelManagerService::SUPPORTED_CHANNELS)) {
            abort(404, 'Channel not supported.');
        }

        // Get existing config
        $config = $this->channelService->getConfig($tid, $channel);

        return view('hotel.channels.configure', compact('channel', 'config'));
    }

    public function updateConfig(Request $request, string $channel)
    {
        $tid = $this->tenantId();

        // Validate channel
        if (! in_array($channel, ChannelManagerService::SUPPORTED_CHANNELS)) {
            abort(404, 'Channel not supported.');
        }

        $data = $request->validate([
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'property_id' => 'required|string|max:100',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        // Create or update config
        $config = ChannelManagerConfig::updateOrCreate(
            [
                'tenant_id' => $tid,
                'channel' => $channel,
            ],
            [
                'api_key' => $data['api_key'],
                'api_secret' => $data['api_secret'] ?? null,
                'property_id' => $data['property_id'],
                'is_active' => $data['is_active'] ?? false,
                'settings' => $data['settings'] ?? null,
            ]
        );

        ActivityLog::record('channel_config_updated', "Channel config updated: {$channel}", $config);

        return back()->with('success', "Configuration for {$channel} saved successfully.");
    }

    public function sync(string $channel)
    {
        $tid = $this->tenantId();

        // Validate channel
        if (! in_array($channel, ChannelManagerService::SUPPORTED_CHANNELS)) {
            return response()->json([
                'success' => false,
                'message' => 'Channel not supported.',
            ], 400);
        }

        try {
            $result = $this->channelService->syncAll($tid, $channel);

            ActivityLog::record('channel_sync', "Channel sync: {$channel} - ".($result['success'] ? 'Success' : 'Failed'));

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function logs(Request $request)
    {
        $tid = $this->tenantId();

        $query = ChannelManagerLog::where('tenant_id', $tid)
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->channel) {
            $query->where('channel', $request->channel);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();

        // Filter options
        $channels = ChannelManagerService::SUPPORTED_CHANNELS;
        $statuses = ['success', 'failed', 'partial'];
        $actions = ['push_availability', 'push_rates', 'pull_reservations', 'sync_all', 'test_connection'];

        return view('hotel.channels.logs', compact('logs', 'channels', 'statuses', 'actions'));
    }
}
