<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the Corporate & Administrator / Member Profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin') || $user->username === 'admin' || $user->email === 'admin@xseller.id';
        $settings = Setting::all()->pluck('value', 'key');

        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);

        return Inertia::render('Profile/Edit', [
            'is_admin' => $isAdmin,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'bank_name' => $user->bank_name ?? 'Bank BRI',
                'bank_account_number' => $user->bank_account_number ?? '',
                'bank_account_name' => $user->bank_account_name ?? '',
            ],
            'admin_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? 'admin',
                'email' => $user->email,
                'phone' => $user->phone ?? '081234567890',
            ],
            'company_profile' => [
                'name' => $settings['company_name'] ?? 'PT.Xseller Punya Kita',
                'owner' => $settings['company_owner'] ?? 'PT.Xseller Punya Kita',
                'copyright' => $settings['company_copyright'] ?? 'PT.Xseller Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.',
                'logo_url' => !empty($settings['site_logo']) ? Storage::url($settings['site_logo']) : null,
                'banks' => is_array($companyBanks) && count($companyBanks) > 0 ? $companyBanks : [
                    [
                        'type' => 'bank',
                        'bank_name' => 'Bank BRI',
                        'account_number' => '806401000095564',
                        'account_name' => 'PT.Xseller Punya Kita',
                    ],
                    [
                        'type' => 'ewallet',
                        'bank_name' => 'DANA (E-Wallet)',
                        'account_number' => '081234567890',
                        'account_name' => 'PT.Xseller Punya Kita',
                    ]
                ],
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Update Profile Credentials & Bank Accounts.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin') || $user->username === 'admin' || $user->email === 'admin@xseller.id';

        $rules = [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ];

        if ($isAdmin) {
            $rules['company_name'] = 'required|string|max:100';
            $rules['company_owner'] = 'required|string|max:100';
            $rules['company_copyright'] = 'required|string|max:255';
            $rules['site_logo'] = 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048';
        } else {
            $rules['bank_name'] = 'nullable|string|max:100';
            $rules['bank_account_number'] = 'nullable|string|max:100';
            $rules['bank_account_name'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        if ($isAdmin) {
            Setting::setValue('company_name', $validated['company_name'] ?? 'PT.Xseller Punya Kita', 'text');
            Setting::setValue('company_owner', $validated['company_owner'] ?? 'PT.Xseller Punya Kita', 'text');
            Setting::setValue('company_copyright', $validated['company_copyright'] ?? 'PT.Xseller Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.', 'text');

            if ($request->hasFile('site_logo')) {
                $path = $request->file('site_logo')->store('settings', 'public');
                Setting::setValue('site_logo', $path, 'image');
            }
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (array_key_exists('bank_name', $validated)) {
            $user->bank_name = $validated['bank_name'];
        }
        if (array_key_exists('bank_account_number', $validated)) {
            $user->bank_account_number = $validated['bank_account_number'];
        }
        if (array_key_exists('bank_account_name', $validated)) {
            $user->bank_account_name = $validated['bank_account_name'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Add or delete company bank account & virtual wallet.
     */
    public function updateBanks(Request $request): RedirectResponse
    {
        $banks = $request->input('banks', []);
        Setting::setValue('company_banks', json_encode($banks), 'json');

        return Redirect::route('profile.edit')->with('success', 'Daftar rekening bank & virtual wallet perusahaan berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
