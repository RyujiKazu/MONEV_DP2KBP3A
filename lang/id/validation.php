<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'numeric' => ':attribute harus berupa angka.',
    'boolean' => ':attribute harus bernilai benar atau salah.',
    'array' => ':attribute harus berupa daftar.',
    'between' => [
        'numeric' => ':attribute harus berada antara :min dan :max.',
        'string' => ':attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal terdiri dari :min karakter.',
    ],
    'max' => [
        'numeric' => ':attribute maksimal :max.',
        'string' => ':attribute maksimal terdiri dari :max karakter.',
    ],
    'in' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'unique' => ':attribute sudah digunakan.',
    'decimal' => ':attribute harus memiliki :decimal angka di belakang koma.',

    'attributes' => [
        'kode_kecamatan' => 'kecamatan',
        'kode_kelurahan' => 'kode kelurahan',
        'nama_kecamatan' => 'nama kecamatan',
        'nama_kelurahan' => 'nama kelurahan',
        'nama_lengkap' => 'nama lengkap',
        'username' => 'username',
        'password' => 'kata sandi',
        'role' => 'peran',
        'tahun' => 'tahun',
    ],
];
