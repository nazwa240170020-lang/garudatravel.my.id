<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/*
 * Form Request ProfileUpdateRequest
 *
 * Mengelola otorisasi dan aturan validasi masukan untuk pembaruan profil pengguna.
 */
class ProfileUpdateRequest extends FormRequest
{
    /*
     * Aturan validasi yang berlaku untuk pembaruan profil
     *
     * Memastikan nama dan email terisi, email berformat valid,
     * serta email bersifat unik di tabel users (mengabaikan pengguna saat ini).
     *
     * @return array Aturan validasi
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
        ];
    }
}
