# Xseller.id - Trade Promotion Program System

Aplikasi Trade Promotion Program berbasis E-Commerce, Affiliasi, dan Komunitas dengan sistem **Binary 2 Kaki** dan **Multi-Tier Bonus (Generasi 1-15)** yang dibangun menggunakan **Laravel 11**, **Vue 3 (Inertia.js)**, **Tailwind CSS**, dan **Spatie Permission**.

Official Website: [https://xseller.id](https://xseller.id)

---

## ⚡ Ringkasan Model Bisnis & PRD 2026

### 1. Konsep & Terminologi
- **Beli Voucher (= Beli Produk):** Transaksi kemitraan dan produk menggunakan istilah **Voucher** alih-alih PIN.
- **Struktur Kemitraan:** Binary System Standard 2 Kaki (Leg Kiri & Leg Kanan).
- **Tier (Generasi):** Level kedalaman bonus generasi dari Tier 1 s/d Tier 15.
- **Upgrade Paket:** Berlaku sistem **Overwrite** (ditambah penambahan generasi via **Steping** untuk semua paket).

### 2. Paket Join (Membership)
| Nama Paket | Nominal | Direct Referral (Gen 1) | Max Tier Base | Team Poin | Fitur Tambahan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Seller** | Rp 125.000 | Rp 20.000 | Tier 3 (Up to 15 via Steping) | 0 Poin | Non-TPR |
| **Star Seller** | Rp 550.000 | Rp 100.000 | Tier 5 (Up to 15 via Steping) | 1 Poin | Alokasi Gen 1: 100k, Gen 2-15: 5k |
| **Affiliate** | Rp 2.100.000 | Rp 300.000 | Tier 8 (Up to 15 via Steping) | 4 Poin | Alokasi Gen 1: 300k, Gen 2-15: 15k |
| **Business** | Rp 4.300.000 | Rp 600.000 | Tier 12 (Up to 15 via Steping) | 8 Poin | Alokasi Gen 1: 600k, Gen 2-15: 30k + Qualified TPR |
| **Partner** | Rp 10.500.000 | Rp 1.500.000 | Tier 15 (Maksimal) | 12 Poin | Alokasi Gen 1: 1.5jt, Gen 2-15: 100k + Qualified TPR |

### 3. Fitur Utama Sistem
- **Mekanisme Steping:** Membuka Tier kedalaman (Generasi 4 s/d 15) untuk semua Paket Join berdasarkan akumulasi Sponsor Direct Paket Rp 125.000.
- **Team Poin Redemption:** Akumulasi poin redemption dari pembelanjaan downline (35 Poin s/d 5.000 Poin).
- **Repeat Order (RO) Paket Rp 125k:** Memberikan 1 Poin RO + Bonus Sponsor Rp 20.000 + Matching Bonus 20%.
- **Purchase Order (PO) & Personal Poin:** Pembelian produk tambahan dengan alokasi 15 Generasi dan akumulasi Personal Poin Reward.
- **TPR Fitur (Titip Produk / Trade Promotion Rate):** Sharing profit 7% & 9% selama 3 bulan dengan Rebate Sponsor 20% & 40%.

---

## 🚀 Panduan Pengoperasian Lokal

### 1. Install Dependensi Backend & Frontend
```bash
composer install
npm install
```

### 2. Environment Setup & Migration
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### 3. Jalankan Application Dev Server
```bash
# Terminal 1: Frontend Asset Builder
npm run dev

# Terminal 2: Backend Development Server
php artisan serve
```

---

## 👨‍💻 Owner & Development Team
- **Owner**: [@hasanarofid.site](https://hasanarofid.site)
- **Website**: [https://xseller.id](https://xseller.id)
