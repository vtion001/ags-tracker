<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TotpController extends Controller
{
    public function showSetup(Request $request)
    {
        $user = $request->user();
        $user->generateTotpSecret();

        $qrCodeUrl = (new Google2FA())->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->totp_secret
        );

        return view('auth.totp-setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $user->totp_secret,
        ]);
    }

    public function setup(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();

        if ($user->enableTotp($request->code)) {
            return redirect()->route('dashboard')->with('success', 'Google Authenticator enabled successfully!');
        }

        return back()->with('error', 'Invalid verification code. Please try again.');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();

        if ($user->verifyTotp($request->code)) {
            $user->disableTotp();
            return redirect()->route('dashboard')->with('success', 'Google Authenticator disabled.');
        }

        return back()->with('error', 'Invalid code. Please try again.');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if ($user && $user->verifyTotp($request->code)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid verification code']);
    }
}
