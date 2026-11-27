<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class LupaSandiController extends Controller
{
    public function kirimKode(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        // Jawaban selalu sama, supaya orang tidak bisa menebak email yang terdaftar.
        $respons = ['pesan' => 'Jika email terdaftar, kode 6 digit sudah dikirim.'];

        $user = User::where('email', $data['email'])->first();

        if ($user) {
            $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('reset_sandi')->updateOrInsert(
                ['email' => $user->email],
                [
                    'kode_hash' => Hash::make($kode),
                    'kadaluarsa_pada' => now()->addMinutes(15),
                    'percobaan' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Mail::raw(
                "Kode reset kata sandi Somniva: {$kode}\nBerlaku 15 menit. Abaikan email ini jika bukan kamu.",
                fn ($m) => $m->to($user->email)->subject('Kode reset kata sandi Somniva')
            );

            // KHUSUS PENGEMBANGAN: kode ikut ditampilkan agar mudah dites.
            // Hapus blok ini sebelum aplikasi dipakai orang sungguhan.
            if (app()->environment('local')) {
                $respons['kode_uji'] = $kode;
            }
        }

        return response()->json($respons);
    }

        public function cekKode(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'kode' => ['required', 'digits:6'],
        ]);

        $salah = ValidationException::withMessages([
            'kode' => ['Kode salah atau sudah kedaluwarsa.'],
        ]);

        $baris = DB::table('reset_sandi')->where('email', $data['email'])->first();

        if (! $baris || now()->greaterThan($baris->kadaluarsa_pada) || $baris->percobaan >= 5) {
            throw $salah;
        }

        if (! Hash::check($data['kode'], $baris->kode_hash)) {
            DB::table('reset_sandi')->where('email', $data['email'])->increment('percobaan');
            throw $salah;
        }

        return response()->json(['pesan' => 'Kode benar.']);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'kode' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $salah = ValidationException::withMessages([
            'kode' => ['Kode salah atau sudah kedaluwarsa.'],
        ]);

        $baris = DB::table('reset_sandi')->where('email', $data['email'])->first();

        if (! $baris || now()->greaterThan($baris->kadaluarsa_pada) || $baris->percobaan >= 5) {
            throw $salah;
        }

        if (! Hash::check($data['kode'], $baris->kode_hash)) {
            DB::table('reset_sandi')->where('email', $data['email'])->increment('percobaan');
            throw $salah;
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            throw $salah;
        }

        $user->password = $data['password']; // di-hash otomatis oleh model
        $user->save();

        $user->tokens()->delete(); // keluarkan semua sesi lama
        DB::table('reset_sandi')->where('email', $data['email'])->delete();

        return response()->json(['pesan' => 'Kata sandi berhasil diubah. Silakan masuk.']);
    }
}