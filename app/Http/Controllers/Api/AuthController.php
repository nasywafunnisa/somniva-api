<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function daftar(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'target_tidur_jam' => ['nullable', 'integer', 'in:7,8,9'],
        ]);

        $user = User::create([
            'name' => $data['nama'],
            'email' => $data['email'],
            'password' => $data['password'], // di-hash otomatis oleh model
            'target_tidur_jam' => $data['target_tidur_jam'] ?? 8,
        ]);

        return response()->json([
            'token' => $user->createToken('somniva')->plainTextToken,
            'pengguna' => $this->bentuk($user),
        ], 201);
    }

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi belum cocok.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('somniva')->plainTextToken,
            'pengguna' => $this->bentuk($user),
        ]);
    }

    public function keluar(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['pesan' => 'Berhasil keluar.']);
    }

    public function saya(Request $request)
    {
        return response()->json(['pengguna' => $this->bentuk($request->user())]);
    }

    private function bentuk(User $user): array
    {
        return [
            'id' => $user->id,
            'nama' => $user->name,
            'email' => $user->email,
            'peran' => $user->peran,
            'target_tidur_jam' => $user->target_tidur_jam,
            'jam_bangun' => $user->jam_bangun,
        ];
    }
}