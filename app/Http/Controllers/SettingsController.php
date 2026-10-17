<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::getSettings();
        $user = Auth::user();
        return view('settings.index', compact('settings', 'user'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:150',
            'business_phone' => 'required|string|max:30',
            'business_email' => 'nullable|email|max:100',
            'business_address' => 'nullable|string',
            'upi_id' => 'nullable|string|max:100',
            'bill_title' => 'required|string|max:100',
            'bill_footer' => 'nullable|string',
            'currency_symbol' => 'required|string|max:10',
        ]);

        $settings = AppSetting::getSettings();
        $settings->update($validated);

        return back()->with('success', 'Business settings updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Admin password changed successfully.');
    }
}
