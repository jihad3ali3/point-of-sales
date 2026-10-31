# Unit Conversion (Multi-Satuan)

Kembali ke indeks dokumentasi: [`docs/README.md`](../README.md)

## Tujuan
Mengelola produk dengan berbagai satuan (misalnya: pcs, box, karton, kg) dengan konversi stok otomatis dan harga berbeda per satuan.

## Modul
**Nama Modul**: Unit Conversion

## Satuan (Units)
**Satuan Dasar (Base Unit)**: Satuan utama untuk penyimpanan stok (contoh: PCS).
**Satuan Tambahan (Additional Units)**: Satuan lain yang digunakan dalam transaksi (contoh: BOX, KARTON, KG, LITER, METER, PAK, DUS).

**Daftar Nama Satuan (Default)**:
- PCS
- BOX
- KARTON
- KG
- LITER
- METER
- PAK
- DUS

## Tabel Database
1. **`units`** (Master Satuan)
   - Kolom: `code`, `name`, `symbol`
2. **`product_units`** (Pivot Produk-Satuan)
   - Kolom: `product_id`, `unit_id`, `is_base`, `conversion_factor`, `buy_price`, `sell_price`, `barcode`
3. **`carts`**
   - Kolom: `unit_id`, `conversion_factor`
4. **`transaction_details`**
   - Kolom: `unit_id`, `conversion_factor`

## Operasi (Operations)
### Metode di `UnitConversionService`
| Metode | Fungsi |
|--------|--------|
| `toBaseUnit(product, unitId, qty)` | Mengonversi kuantitas dari satuan tertentu ke satuan dasar (base unit) |
| `fromBaseUnit(product, unitId, baseQty)` | Mengonversi stok dasar ke kuantitas di satuan tertentu |
| `getPrice(product, unitId, type)` | Mendapatkan harga (beli/jual) untuk satuan tertentu |
| `getUnitLabel(product, unitId)` | Mendapatkan label satuan untuk tampilan |

### Alur Operasional
1. **Admin Setup**: Menentukan satuan dasar + satuan tambahan per produk (melalui seeder atau insert langsung).
2. **POS**: Produk dengan satuan ganda — pengguna memilih satuan di dropdown, harga otomatis berubah.
3. **Checkout**: Kuantitas dikonversi ke satuan dasar untuk pengecekan stok dan pengurangan stok.
4. **Mutasi Stok**: Selalu dilakukan dalam satuan dasar.

## Kendala / Batasan (Constraints)
- **Produk Existing**: Dianggap memiliki satuan dasar PCS dengan faktor konversi 1.
- **Penghapusan Satuan**: Satuan tidak dapat dihapus jika masih digunakan oleh produk.
- **Harga**: Harga per satuan disimpan langsung di tabel pivot (`product_units`), bukan dihitung dari harga dasar dikalikan faktor konversi.
- **Konversi Stok**: Stok dikelola di satuan dasar; konversi otomatis dilakukan saat checkout.
- **Barcode**: Memiliki barcode spesifik per satuan.
- **POS Checkout**: Menggunakan kuantitas satuan dasar untuk pengecekan stok.

---
*Dokumentasi diperbarui pada: 2026-10-31*
