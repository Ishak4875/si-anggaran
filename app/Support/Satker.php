<?php

namespace App\Support;

class Satker
{
    /**
     * Seluruh grup satker beserta metadata (dari config/satker.php).
     *
     * @return array<string, array{nama:string, singkatan:string, kdsatker:array<int,string>}>
     */
    public static function groups(): array
    {
        return config('satker.groups', []);
    }

    /**
     * Metadata satu grup satker berdasarkan slug, atau null jika tidak ada.
     */
    public static function find(string $slug): ?array
    {
        return static::groups()[$slug] ?? null;
    }

    /**
     * Cari slug grup dari sebuah kdsatker. Mengembalikan 'lainnya' bila
     * kode tidak dikenali sehingga data tetap tersimpan & tampil.
     */
    public static function slugForKdsatker(?string $kdsatker): string
    {
        foreach (static::groups() as $slug => $group) {
            if (in_array((string) $kdsatker, $group['kdsatker'], true)) {
                return $slug;
            }
        }

        return 'lainnya';
    }

    /**
     * Nama lengkap grup satker dari slug.
     */
    public static function nama(string $slug): string
    {
        return static::find($slug)['nama'] ?? 'Satker Lainnya';
    }

    /**
     * Singkatan grup satker dari slug.
     */
    public static function singkatan(string $slug): string
    {
        return static::find($slug)['singkatan'] ?? 'Lainnya';
    }
}
