<?php

namespace App\Livewire\Berita;

use App\Models\Berita;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Arsip berita')]
class DaftarBerita extends Component
{
    use WithPagination;

    public string $cari = '';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
        Berita::whereKey($id)->delete();

        session()->flash('sukses', 'Berita dihapus.');
    }

    public function render()
    {
        $daftar = Berita::query()
            ->when($this->cari !== '', function ($kueri) {
                $kueri->where(fn ($q) => $q
                    ->where('judul', 'like', '%'.$this->cari.'%')
                    ->orWhere('lead', 'like', '%'.$this->cari.'%'));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.berita.daftar-berita', ['daftar' => $daftar]);
    }
}
