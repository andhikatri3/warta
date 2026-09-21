<?php

namespace App\Services\Berita;

use RuntimeException;

/**
 * Penulisan berita gagal karena sebab yang bisa dijelaskan ke pemakai:
 * kunci API belum diisi, layanan menolak, atau jawabannya tidak berbentuk
 * naskah yang bisa dipakai.
 */
class GagalMenulis extends RuntimeException {}
