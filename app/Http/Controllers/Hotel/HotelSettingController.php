<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HotelSetting;
use Illuminate\Http\Request;

class HotelSettingController extends Controller
{
    // tenantId() inherited from parent Controller

    public function edit()
    {
        $tid = $this->tenantId();

        $settings = HotelSetting::firstOrCreate(
            ['tenant_id' => $tid],
            [
                'hotel_name' => '',
                'check_in_time' => '14:00',
                'check_out_time' => '12:00',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'tax_rate' => 10.00,
                'deposit_required' => false,
                'deposit_amount' => 0,
                'deposit_type' => 'fixed',
            ]
        );

        // Timezone options
        $timezones = [
            'Asia/Jakarta' => 'WIB (Jakarta)',
            'Asia/Makassar' => 'WITA (Makassar)',
            'Asia/Jayapura' => 'WIT (Jayapura)',
            'UTC' => 'UTC',
        ];

        // Currency options
        $currencies = [
            'IDR' => 'Indonesian Rupiah',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'SGD' => 'Singapore Dollar',
            'MYR' => 'Malaysian Ringgit',
        ];

        return view('hotel.settings.edit', compact('settings', 'timezones', 'currencies'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hotel_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_included' => 'boolean',
            'deposit_required' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_type' => 'nullable|in:fixed,percentage',
            'cancellation_policy' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $tid = $this->tenantId();

        $settings = HotelSetting::where('tenant_id', $tid)->first();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('hotel', 'public');
            $data['logo'] = '/storage/'.$logoPath;
        }

        if ($settings) {
            $old = $settings->getOriginal();
            $settings->update($data);
            ActivityLog::record('hotel_settings_updated', 'Hotel settings updated', $settings, $old, $settings->fresh()->toArray());
        } else {
            $settings = HotelSetting::create(array_merge($data, ['tenant_id' => $tid]));
            ActivityLog::record('hotel_settings_created', 'Hotel settings created', $settings, [], $settings->toArray());
        }

        return back()->with('success', 'Hotel settings updated successfully.');
    }
}
