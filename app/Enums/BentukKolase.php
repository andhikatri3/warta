<?php

namespace App\Enums;

/**
 * Bentuk area kolase, bukan bentuk kanvasnya.
 *
 * Dipisahkan dari UkuranThumbnail supaya aturan tata letak ditulis sekali
 * untuk tiap bentuk, bukan sekali untuk tiap ukuran. Kalau nanti ditambah
 * ukuran baru, ia cukup jatuh ke salah satu bentuk yang sudah ada.
 */
enum BentukKolase
{
    case Lebar;
    case Kotak;
    case Tinggi;
}
