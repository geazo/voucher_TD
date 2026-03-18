<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardCreateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori' => [
                'required',
                'string'
            ],
            'nik' => [
                'required',
                'integer',
            ],
            'nama_depan' => [
                'required',
                'string',
            ],
            'nama_belakang' => [
                'required',
                'string',
            ],
            'perusahaan' => [
                'required',
                'string',
            ],
            'sim' => [
                'nullable',
                'integer',
            ],
            'no_hp' => [
                'required',
                'regex:/^[0-9]+$/',
                'min:10', // Adjust minimum length as needed
                'max:15',
                'integer',
            ],
            'jam_masuk' => [
                'nullable',
                'date'
            ],
            'jam_keluar' => [
                'nullable',
                'date'
            ],
            'kepentingan' => [
                'required',
                'string',
            ],
            'lokasi_tujuan' => [
                'required',
                'string',
            ],
            'visit_location' => [
                'nullable',
                'string',
            ],
            'access_card' => [
                'nullable',
                'string',
            ],
            'foto_wajah' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                // 'max:2048',
            ],
            'foto_ktp' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                // 'max:2048',
            ],
        ];
    }
}
