# 🖥️ XAMPP Dashboard Revamped – Modern, Functional, Beautiful

> Modifikasi tampilan dashboard XAMPP agar lebih **fungsional, modern, dan interaktif**.  
> Dirancang khusus untuk developer lokal yang ingin workspace-nya terasa **profesional dan efisien**.

---

## ✨ Deskripsi

Proyek ini adalah **versi kustom dari halaman dashboard XAMPP** bawaan.  
Tujuannya adalah untuk membuat tampilan lokal proyek di `htdocs/` menjadi **lebih informatif**, **menarik**, dan **nyaman digunakan**.

Dashboard ini akan **menampilkan daftar proyek lokal** (folder di dalam `htdocs`) lengkap dengan:

- Waktu terakhir diubah  
- Ukuran folder proyek  
- Tampilan grid atau list  
- Pencarian instan  
- Sorting dinamis  
- Cache otomatis agar loading lebih cepat  
- Efek visual modern (aurora background + glassmorphism)

---

## ⚡ Fitur Utama

| Fitur | Deskripsi |
|-------|------------|
| 🧭 **Auto Scan Projects** | Menampilkan semua folder proyek di `htdocs` secara otomatis |
| ⚡ **Smart Caching** | Menggunakan cache JSON agar tidak menghitung ulang ukuran folder setiap kali |
| 🔍 **Live Search** | Cari proyek langsung tanpa reload halaman |
| 🔄 **Dynamic Sorting** | Urutkan proyek berdasarkan nama, ukuran, atau tanggal modifikasi |
| 🧱 **Grid / List View** | Ganti tampilan sesuai preferensi |
| 💾 **Folder Size Calculation** | Menampilkan ukuran folder proyek secara rekursif |
| 🌈 **Modern UI/UX** | Desain dark theme dengan efek glass dan aurora |
| ☕ **Custom Footer** | “Crafted with ☕ by [Toti Ardiansyah](https://totiard.github.io/Profile-New)” dengan efek neon ✨ |

---

## 🛠️ Cara Menggunakan

1. **Buka folder XAMPP**  
`C:\xampp\htdocs\`

2. **Backup dashboard bawaan (opsional)**  
Ganti nama folder `dashboard` menjadi misalnya `dashboard_backup`.

3. **Salin file dashboard ini** ke dalam folder `htdocs`  
Pastikan file utamanya bernama misalnya `index.php`.

4. **Jalankan XAMPP**, kemudian buka di browser:  
`http://localhost/`

5. Selesai ✅  
Kamu akan melihat dashboard baru dengan tampilan modern dan fungsional!

---

## 📁 Struktur File Utama

```plaintext
📂 htdocs
 ├── index.php              # Dashboard utama
 ├── _dashboard_cache.json  # File cache ukuran folder (dibuat otomatis)
 └── (folder proyek kamu)
```

---

## 💡 Bagaimana Cara Kerja Caching?

Sistem ini dirancang untuk kecepatan.

1.  Saat kamu membuka `localhost` untuk **pertama kali**, PHP akan memindai setiap folder proyek, menghitung ukurannya (proses yang lambat), dan menyimpan hasilnya di file `_dashboard_cache.json`.
2.  Saat kamu me-refresh atau membuka `localhost` **lain kali**, PHP akan membaca data dari `_dashboard_cache.json` (proses yang instan).
3.  Jika kamu **memodifikasi sebuah proyek** (misal, menambah file baru), PHP akan mendeteksi perubahan `mtime` (waktu modifikasi) pada folder itu dan **hanya akan memindai ulang folder itu saja**.

Hasilnya? Dashboard yang selalu *up-to-date* tanpa mengorbankan kecepatan *loading*.

---

## 🧠 Teknologi yang Digunakan

- **PHP** (tanpa framework, ringan)
- **HTML5 / CSS3**
- **JavaScript (vanilla)**
- **Font Awesome 5**
- **Google Fonts – Inter**
- **Glassmorphism + Aurora Gradient UI**

---

## 🧩 Perbedaan dari Dashboard XAMPP Asli

| Asli | Versi Modifikasi |
|------|------------------|
| Tampilan sederhana | Desain modern & interaktif |
| Tidak ada sorting atau search | Ada pencarian dan pengurutan proyek |
| Tidak menampilkan ukuran folder | Menampilkan ukuran folder & caching |
| Tidak bisa ubah tampilan | Ada mode Grid & List |
| Tidak responsif | Fully responsive (mobile friendly) |

---

## 🧑‍💻 Pengembang

**Crafted with ☕ by [Toti Ardiansyah](https://totiard.github.io/Profile-New)**  
© 2025 — Open for personal use and customization.

---

## 📜 Lisensi

Proyek ini bersifat **open-source**.  
Kamu bebas memodifikasi dan menggunakannya untuk keperluan pribadi atau pengembangan lokal.

---

### ⭐ Jika kamu suka proyek ini, jangan lupa kasih bintang di GitHub ya!
