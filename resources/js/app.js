import { domToBlob } from 'modern-screenshot'

/*
 * Kanvas thumbnail hidup di DOM pada ukuran piksel sebenarnya — 1200x630 dan
 * seterusnya — lalu dikecilkan dengan transform hanya supaya muat dipandang.
 * Berkas akhirnya ditangkap dari DOM itu juga, di peramban, tanpa Chromium di
 * sisi server: server ini cuma perlu PHP.
 */

/* ------------------------------------------------------------------ *
 * Skala pratinjau
 * ------------------------------------------------------------------ */

function hitungSkala(wadah) {
    const kanvas = wadah.querySelector('[data-kanvas]')
    if (!kanvas) return

    const lebar = wadah.clientWidth
    if (!lebar) return

    // Tingginya sendiri diurus aspect-ratio di CSS, jadi di sini cukup
    // skalanya — satu sumber kebenaran.
    const skala = String(lebar / Number(kanvas.dataset.lebar))

    // Menulis hanya kalau nilainya memang berbeda. Ini yang menghentikan
    // pengamat DOM di bawah: menyetel style adalah perubahan atribut, yang
    // membangunkan pengamat itu lagi — tanpa penjaga ini keduanya saling
    // memanggil tanpa henti.
    if (wadah.style.getPropertyValue('--skala') === skala) return

    wadah.style.setProperty('--skala', skala)
}

const pengamatUkuran = new ResizeObserver((entri) => {
    for (const { target } of entri) hitungSkala(target)
})

/*
 * Skalanya dihitung langsung di sini, bukan diserahkan sepenuhnya ke
 * ResizeObserver.
 *
 * Livewire memperbarui halaman dengan mencocokkan DOM ke HTML kiriman server,
 * dan atribut style wadah ikut dikembalikan ke bentuk aslinya — --skala yang
 * ditulis JavaScript terhapus setiap kali pemakai mengetik judul atau menambah
 * foto. ResizeObserver tidak menyelamatkan keadaan itu: lebar wadahnya tidak
 * berubah sedikit pun, jadi ia tidak pernah berbunyi, dan observe() pada
 * elemen yang sudah diamati hanyalah no-op. Akibatnya kanvas kembali ke ukuran
 * asli 1200 piksel di dalam wadah setengahnya dan tampak terpotong — padahal
 * berkas yang diunduh tetap benar, karena penangkapan memakai ukuran aslinya.
 */
function pasangPratinjau() {
    document.querySelectorAll('[data-pratinjau]').forEach((wadah) => {
        hitungSkala(wadah)
        pengamatUkuran.observe(wadah)
    })
}

/* ------------------------------------------------------------------ *
 * Penangkapan
 * ------------------------------------------------------------------ */

async function tungguGambar(el) {
    const gambar = [...el.querySelectorAll('img')]

    await Promise.all(
        gambar.map((img) =>
            img.complete && img.naturalWidth > 0
                ? Promise.resolve()
                : new Promise((selesai) => {
                      img.addEventListener('load', selesai, { once: true })
                      img.addEventListener('error', selesai, { once: true })
                  }),
        ),
    )
}

async function tangkap(kanvas) {
    // Tanpa ini, judul bisa tertangkap dengan huruf cadangan sistem: metrik
    // hurufnya berbeda, jadi pemenggalan barisnya pun ikut bergeser dari
    // yang terlihat di pratinjau.
    if (document.fonts?.ready) await document.fonts.ready
    await tungguGambar(kanvas)

    return domToBlob(kanvas, {
        width: Number(kanvas.dataset.lebar),
        height: Number(kanvas.dataset.tinggi),
        scale: 1,
        type: 'image/png',
        backgroundColor: '#ffffff',
        // Kanvas sedang dikecilkan oleh transform milik wadah pratinjau, dan
        // modern-screenshot menyalin gaya terhitung apa adanya ke klonanya.
        // Tanpa penawar ini, berkas yang terunduh ikut mengecil sesuai skala
        // layar — 1200x630 berisi gambar 420 piksel di pojok kiri atas.
        style: { transform: 'none', transformOrigin: 'top left' },
    })
}

function unduhBlob(blob, nama) {
    const url = URL.createObjectURL(blob)
    const tautan = document.createElement('a')

    tautan.href = url
    tautan.download = nama
    tautan.style.display = 'none'
    document.body.appendChild(tautan)
    tautan.click()

    // Anchor dan URL blob dilepas lama sesudah diklik, bukan seketika.
    // Menekan tombol hanya memulai unduhan — peramban masih membaca dari URL
    // blob ini sesudahnya, dan PNG Story 1080x1920 tidak selalu selesai dalam
    // sekejap. Jangka selebar ini murni kehati-hatian; blob-nya toh ikut
    // terlepas sendiri saat halaman ditinggalkan.
    setTimeout(() => {
        tautan.remove()
        URL.revokeObjectURL(url)
    }, 60000)
}

const jeda = (ms) => new Promise((r) => setTimeout(r, ms))

async function unduhUkuran(ukuran, dasar) {
    const kanvas = document.querySelector(`[data-kanvas="${ukuran}"]`)
    if (!kanvas) return

    const blob = await tangkap(kanvas)
    const { lebar, tinggi } = kanvas.dataset

    unduhBlob(blob, `${dasar}-${ukuran}-${lebar}x${tinggi}.png`)
}

/* ------------------------------------------------------------------ *
 * Tombol unduh
 * ------------------------------------------------------------------ */

document.addEventListener('click', async (peristiwa) => {
    const tombol = peristiwa.target.closest('[data-unduh]')
    if (!tombol || tombol.disabled) return

    peristiwa.preventDefault()

    const { unduh, nama } = tombol.dataset
    const dasar = nama || 'thumbnail'
    const teksAwal = tombol.textContent

    tombol.disabled = true
    tombol.textContent = 'Menyiapkan…'

    try {
        if (unduh === 'semua') {
            const semua = [...document.querySelectorAll('[data-kanvas]')]

            for (const kanvas of semua) {
                await unduhUkuran(kanvas.dataset.kanvas, dasar)
                // Peramban membatalkan unduhan beruntun yang terlalu rapat;
                // jeda pendek membuat ketiganya benar-benar sampai.
                await jeda(400)
            }
        } else {
            await unduhUkuran(unduh, dasar)
        }
    } catch (galat) {
        console.error(galat)
        alert('Gagal membuat gambar. Coba muat ulang halaman.')
    } finally {
        tombol.disabled = false
        tombol.textContent = teksAwal
    }
})

/* ------------------------------------------------------------------ *
 * Titik fokus
 * ------------------------------------------------------------------ */

document.addEventListener('click', (peristiwa) => {
    const bidang = peristiwa.target.closest('[data-fokus]')
    if (!bidang) return

    const kotak = bidang.getBoundingClientRect()
    const x = ((peristiwa.clientX - kotak.left) / kotak.width) * 100
    const y = ((peristiwa.clientY - kotak.top) / kotak.height) * 100

    window.Livewire?.find(bidang.closest('[wire\\:id]')?.getAttribute('wire:id'))
        ?.call('aturFokus', Number(bidang.dataset.fokus), x, y)
})

/* ------------------------------------------------------------------ *
 * Pemasangan
 * ------------------------------------------------------------------ */

pasangPratinjau()
document.addEventListener('livewire:navigated', pasangPratinjau)

/*
 * Atribut style ikut diawasi, bukan hanya susunan simpulnya.
 *
 * Pembaruan Livewire mengembalikan atribut style wadah ke bentuk kiriman
 * server, dan itu menghapus --skala yang ditulis di sini. Perubahan itu murni
 * perubahan atribut — tidak ada simpul yang ditambah atau dibuang — jadi
 * pengawasan childList saja tidak pernah melihatnya, dan pratinjau tertinggal
 * dalam keadaan terpotong sampai jendela kebetulan diubah ukurannya.
 */
new MutationObserver(pasangPratinjau).observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['style'],
})
