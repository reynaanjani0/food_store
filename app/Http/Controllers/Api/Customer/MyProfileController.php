<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\HasMiddleware;

class MyProfileController extends Controller
{
    /**
     * addMiddleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            'auth:sanctum'
        ];
    }

    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $profile = Customer::query()
            ->where('id', auth()->guard('api')->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail Profil',
            'customer' => $profile
        ]);
    }

    /**
     * update
     *
     * @param mixed $request
     * @return void
     */
    public function update(Request $request)
    {
        //test validation
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:customers,email,' . auth()->guard('api')->user()->id,
            'password' => 'nullable|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //ambil profil customer yang login
        $profile = Customer::query()
            ->where('id', auth()->guard('api')->user()->id)
            ->firstOrFail();

        // Jika ada upload gambar, simpan dan update
        if ($request->hasFile('image')) {

            // Hapus gambar lama jika ada
            if ($profile->image) {
                Storage::disk('public')->delete('avatars/' . $profile->image);
            }

            // upload gambar baru
            $image = $request->file('image');
            $image->storeAs('avatars', $image->hashName());

            // Simpan nama file ke database
            $profile->image = $image->hashName();
        }

        // update data profil
        $profile->name = $request->name;
        $profile->email = $request->email;

        // update password jika diberikan
        if ($request->filled('password')) {
            $profile->password = bcrypt($request->password);
        }

        // Simpan perubahan
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Update Profil Berhasil!',
            'data' => $profile
        ]);
    }
}