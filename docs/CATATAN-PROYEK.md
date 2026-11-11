# Catatan Proyek: Rental Kendaraan (Showroom)

Website etalase rental kendaraan. Pengunjung melihat unit, harga, dan status, lalu bertanya lewat WhatsApp.
**Tidak ada** pemesanan, pembayaran, atau kalender ketersediaan: admin mengatur status unit secara manual.

**Teknologi:** Laravel 13, Filament 5 (admin di `/admin`), Blade + Tailwind 4 + Alpine.js, MySQL.

## Halaman publik

| Alamat | Isi |
|---|---|
| `/` | Beranda: hero, pencarian, unit unggulan, kategori, motto, keunggulan, paket wisata unggulan, testimoni, FAQ |
| `/rental-mobil`, `/rental-mobil/{slug}` | Katalog (filter dan urutan harga) dan detail unit (galeri, tarif, form rencana sewa, syarat) |
| `/rental-motor`, `/rental-motor/{slug}` | Sama, khusus kategori Motor. Aktif jika fitur dinyalakan |
| `/paket-wisata`, `/paket-wisata/{slug}` | Paket wisata. Aktif jika fitur dinyalakan |
| `/travel`, `/travel/{slug}` | Layanan travel. Aktif jika fitur dinyalakan |
| `/syarat-sewa`, `/kontak` | Syarat sewa dan kontak (alamat, telepon, peta) |
| `/sitemap.xml`, `/robots.txt` | Dibuat otomatis (lihat bagian SEO) |

Form rencana sewa di halaman detail unit tidak menyimpan data apa pun: isiannya disusun menjadi pesan
dan pengunjung diarahkan ke `wa.me` dengan teks siap kirim. Dibatasi 6 kiriman per menit per IP dan
ada kolom jebakan bot.

## Menu admin (`/admin`)

- **Konten:** Syarat sewa, Motto, Keunggulan, FAQ, Testimoni
- **Katalog:** Kategori kendaraan, Kendaraan, Biaya tambahan
- **Layanan:** Paket wisata, Layanan travel
- **Website:** Pengaturan website (nama, logo, WhatsApp, kontak, peta, sosmed, teks beranda, saklar fitur)
- **Pengaturan:** Admin (pengguna)

Urutan mengisi untuk website baru: Pengaturan website (terutama nomor WhatsApp) -> Kategori -> Kendaraan
(foto dan tarif) -> Syarat sewa -> FAQ, motto, keunggulan -> testimoni asli -> wisata/travel jika dipakai.

Aturan tampil: hanya unit yang dipublikasikan dengan kategori aktif yang muncul. Status unit
(tersedia, disewa, perawatan, tidak tersedia) hanya menjadi lencana dan tidak menyembunyikan unit.

## Perintah yang sering dipakai

```
php artisan serve              # server lokal
npm run dev                    # Vite untuk pengembangan (terminal terpisah)
npm run build                  # build aset produksi
php artisan test               # 155 tes
php artisan optimize:clear     # bersihkan cache jika tampilan tidak berubah
php artisan storage:link       # sekali saja, agar foto unit tampil
php artisan db:seed --class=DemoDataSeeder   # data contoh minimal, hanya untuk lokal
php artisan db:seed --class=ShowcaseSeeder   # data contoh lengkap untuk tangkapan layar (lihat di bawah)
```

## Data contoh untuk tangkapan layar

`php artisan db:seed --class=ShowcaseSeeder` mengisi website dengan data contoh yang lengkap: 14 unit
(mobil, premium, minibus, motor) dengan tarif dan status beragam, biaya tambahan, syarat sewa, motto,
keunggulan, 7 FAQ, 6 testimoni, 5 paket wisata, 4 layanan travel, dan menyalakan ketiga fitur opsional.

- Aman dijalankan berulang (memperbarui data berdasarkan slug/judul) dan tidak menyentuh unit yang Anda buat sendiri.
- Unit "Contoh ..." dari `DemoDataSeeder` disembunyikan agar tidak bercampur.
- Foto berupa ilustrasi SVG berlabel "Foto contoh" (disimpan di `storage/app/public/.../showcase`). Ganti dengan foto asli lewat admin.
- Testimoni diberi label sumber "Contoh testimoni" dan nama "Contoh Pelanggan". Jangan dipakai sebagai testimoni asli.
- Menolak berjalan di production. Pastikan `php artisan storage:link` sudah dijalankan.

## Variabel `.env` yang penting

| Variabel | Keterangan |
|---|---|
| `APP_URL` | Harus sama dengan alamat website. Dipakai untuk URL foto, sitemap, dan kanonikal |
| `APP_TIMEZONE` | `Asia/Jakarta` (validasi tanggal "hari ini" mengikuti ini) |
| `DB_*` | Koneksi MySQL |
| `SEO_INDEXABLE` | Opsional, bawaan `true`. Isi `false` di server percobaan: `robots.txt` memblokir semua dan setiap halaman memakai `noindex` |

## SEO

- **Sitemap** (`/sitemap.xml`): beranda, halaman utama, dan semua unit/paket/layanan yang dipublikasikan. Fitur yang mati tidak masuk.
- **robots.txt** dibuat dinamis dan menyebut alamat sitemap. File statis `public/robots.txt` bawaan Laravel
  **harus dihapus** karena akan mengalahkan rute ini.
- **Data terstruktur (JSON-LD):** bisnis (AutoRental), situs, FAQ (hanya jika ada FAQ), Product untuk unit
  (rentang harga dan ketersediaan sesuai status), TouristTrip untuk paket wisata, Service untuk travel, dan breadcrumb.
  Sengaja tidak ada rating atau ulasan terstruktur karena testimoni ditulis sendiri oleh pemilik.
- **Meta:** judul dan deskripsi per halaman (bisa diisi di tab SEO tiap unit/paket), Open Graph, kanonikal.
  Halaman hasil filter memakai `noindex,follow` dan kanonikal ke daftar utama; halaman 2 dan seterusnya punya kanonikal sendiri.
- Lihat hasilnya dengan uji "Rich Results Test" Google setelah website online.

## Hasil QA

- 155 tes otomatis (skema, admin, katalog, form WhatsApp, konten, wisata/travel, SEO) lolos.
- Tes memastikan jumlah query halaman tidak bertambah seiring jumlah data (tidak ada N+1).
- Audit aksesibilitas otomatis (axe-core, WCAG 2.1 AA) pada 11 halaman publik di layar desktop dan ponsel: tidak ada pelanggaran.
- Header keamanan dasar di halaman publik (nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS saat HTTPS).
  CSP tidak dipasang karena Alpine.js dan Livewire memerlukan eval.
- Halaman error 404, 419, 429, 500, dan 503 berbahasa Indonesia.

## Batasan yang disengaja

- Tidak ada booking, pembayaran, atau kalender ketersediaan.
- Motto dan keunggulan tampil maksimal 3 dan semua yang aktif; FAQ tampil maksimal 8 di beranda.
- Foto unit disimpan di disk publik lokal (`storage/app/public`). Untuk traffic besar, pindahkan ke object storage.
- Galeri umum (tabel sudah ada) belum punya halaman admin maupun halaman publik.

## Sebelum dipublikasikan (ringkas)

1. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` benar, kredensial database, kunci aplikasi (`php artisan key:generate`).
2. Ganti kata sandi akun admin awal dan hapus akun contoh.
3. Isi nomor WhatsApp asli, nama bisnis, dan kontak di Pengaturan website. Jangan memakai data demo.
4. Hapus `public/robots.txt`, jalankan `php artisan storage:link`, `npm run build`, lalu `php artisan optimize`.
5. Cek `/sitemap.xml` dan `/robots.txt`, lalu kirim sitemap ke Google Search Console.
6. Jangan jalankan `DemoDataSeeder` maupun `ShowcaseSeeder` di server produksi.
