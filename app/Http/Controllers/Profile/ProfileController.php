<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * 11. Profile Management.
     */
    public function profile()
    {
        return view('pages.profile');
    }

    /**
     * Update Profile Information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile information updated successfully!',
        ]);
    }

    /**
     * Update Password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['The provided current password does not match our records.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!',
        ]);
    }

    /**
     * Update Business Profile and Settings.
     */
    public function updateBusinessSettings(Request $request)
    {
        $user = auth()->user();
        $userRole = strtolower(trim($user->role ?? ''));
        if (! $user || (! \App\Services\RolePermissionService::userHasPermission($user, 'backups_settings_manage') && ! in_array($userRole, ['super_admin', 'admin', 'administrator', 'owner', 'master']))) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Apart from Admin and Super Admin, no one can modify business settings and bank profiles.',
            ], 403);
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_subtitle' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'business_email' => 'required|email|max:255',
            'business_mobile' => 'nullable|string|max:50',
            'gstin' => ['required', 'string', 'size:15', 'regex:/^[0-9]{2}[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}[1-9A-Za-z]{1}[Zz][0-9A-Za-z]{1}$/'],
            'msme_number' => ['nullable', 'string', 'regex:/^UDYAM-[A-Za-z]{2}-[0-9]{2}-[0-9]{7}$/i'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_no' => 'required|string|min:9|max:18|regex:/^[0-9A-Za-z]+$/',
            'bank_ifsc' => ['required', 'string', 'size:11', 'regex:/^[A-Za-z]{4}[0O][0-9A-Za-z]{6}$/'],
            'invoice_prefix' => 'nullable|string|max:50',
            'order_prefix' => 'nullable|string|max:50',
            'terms_and_conditions' => 'nullable|string|max:2000',
        ], [
            'gstin.regex' => 'Please enter a valid 15-character GSTIN (e.g. 24AAAAA1111A1Z5).',
            'gstin.size' => 'GSTIN number must be exactly 15 characters long.',
            'msme_number.regex' => 'Please enter a valid MSME Udyam Registration Number (e.g. UDYAM-GJ-24-0012345).',
            'bank_ifsc.regex' => 'Please enter a valid 11-character Indian Bank IFSC Code (e.g. SBIN0001234).',
            'bank_ifsc.size' => 'IFSC Code must be exactly 11 characters long.',
            'bank_account_no.min' => 'Bank Account Number must be at least 9 digits.',
            'bank_account_no.max' => 'Bank Account Number cannot exceed 18 characters.',
        ]);

        $validated['gstin'] = strtoupper(trim($validated['gstin']));
        if (! empty($validated['msme_number'])) {
            $validated['msme_number'] = strtoupper(trim($validated['msme_number']));
        }

        $ifsc = strtoupper(trim($validated['bank_ifsc']));
        if (strlen($ifsc) === 11 && $ifsc[4] === 'O') {
            $ifsc[4] = '0';
        }
        $validated['bank_ifsc'] = $ifsc;

        try {
            Setting::set('business_name', $validated['business_name']);
            Setting::set('business_subtitle', $validated['business_subtitle']);
            Setting::set('address', $validated['address']);
            Setting::set('address_line_1', $validated['address']);
            Setting::set('address_line_2', '');
            Setting::set('business_email', strtolower(trim($validated['business_email'])));
            Setting::set('business_mobile', trim($request->input('business_mobile', '')));
            Setting::set('gstin', $validated['gstin']);
            Setting::set('msme_number', $request->input('msme_number', ''));
            Setting::set('bank_name', $validated['bank_name']);
            Setting::set('bank_account_name', $validated['bank_account_name']);
            Setting::set('bank_account_no', $validated['bank_account_no']);
            Setting::set('bank_ifsc', $validated['bank_ifsc']);
            Setting::set('invoice_prefix', strtoupper(trim($request->input('invoice_prefix') ?? '')));
            Setting::set('order_prefix', strtoupper(trim($request->input('order_prefix') ?? '')));
            Setting::set('terms_and_conditions', $request->input('terms_and_conditions', "1. All disputes are subject to Rajkot jurisdiction.\n2. Interest @18% p.a. charged on overdue payments after due date.\n3. Goods once dispatched/sold cannot be returned or exchanged."));

            if ($request->hasFile('logo')) {
                \Illuminate\Support\Facades\File::ensureDirectoryExists(public_path('uploads'));
                $file = $request->file('logo');
                $filename = 'logo_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $filename);
                Setting::set('logo_path', 'uploads/'.$filename);
            }

            if ($request->hasFile('signature')) {
                \Illuminate\Support\Facades\File::ensureDirectoryExists(public_path('uploads'));
                $file = $request->file('signature');
                $filename = 'signature_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $filename);
                Setting::set('signature_path', 'uploads/'.$filename);
            }

            return response()->json([
                'success' => true,
                'message' => 'Business settings updated successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save business settings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to save business settings. Please try again.'],
            ], 500);
        }
    }
}
