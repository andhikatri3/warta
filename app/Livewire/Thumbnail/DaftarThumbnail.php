<?php

namespace App\Livewire\Thumbnail;

use App\Models\Thumbnail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Riwayat thumbnail')]
class DaftarThumbnail extends Component
{
    use WithPagination;

    public function hapus(int $id): void
    {
        // Berkas gambarnya ikut terhapus lewat kait deleting di model, jadi
        // penghapusan harus lewat instansi, bukan kueri massal.
        Thumbnail::find($id)?->delete();

        session()->flash('sukses', 'Thumbnail dihapus.');
    }

    public function render()
    {
        return view('livewire.thumbnail.daftar-thumbnail', [
            'daftar' => Thumbnail::with('foto')->latest()->paginate(9),
        ]);
    }
}
