# Rencana Implementasi: Perhitungan PKB Multi-Tarif

## Latar Belakang

Per **5 Januari 2025**, berlaku aturan tarif PKB baru beserta pengenaan Opsen.
Kendaraan yang memiliki tunggakan pajak melewati tanggal tersebut harus dihitung
menggunakan **dua skema sekaligus**.

---

## Aturan Tarif

### Tarif Lama (periode < 5 Jan 2025)
| Penggunaan | Tarif |
|---|---|
| Pribadi | **1.5%** |
| Angkutan Umum | **1.0%** |
| Pemerintah / Sosial / Keagamaan | **0.5%** |

**Opsen PKB: TIDAK dipungut**

### Tarif Baru (periode ≥ 5 Jan 2025)
| Penggunaan | Tarif |
|---|---|
| Pribadi | **1.2%** |
| Selain Pribadi | **0.5%** |

**Opsen PKB: 66% × PKB**

---

## Open Questions

> [!IMPORTANT]
> 1. **Penentuan K_GUNA untuk Pemerintah/Sosial**: Nilai K_GUNA apa yang dikembalikan API untuk kendaraan pemerintah atau sosial? (Contoh: "PEMERINTAH", "DINAS", dll.) Saat ini saya hanya mengetahui "PRIBADI" dan "UMUM".
> 2. **Tunggakan tanpa SD_NOTICE**: Jika API tidak mengembalikan SD_NOTICE yang valid, bagaimana asumsinya? Saya akan asumsikan 1 tahun berjalan dengan tarif baru.

---

## Logika Algoritma (Per Tahun)

```
cutoff = 2025-01-05
period_start = SD_NOTICE  ← akhir periode TERBAYAR terakhir

LOOP (selama period_start < hari_ini):
  period_end = period_start + 1 tahun

  JIKA period_start >= cutoff:
    → Tarif baru (1.2% atau 0.5%)
    → PKB_baru += dpPkb × tarifBaru
    → Opsen += 66% × PKB_baru_tahun_ini
    → Jika period_end < hari_ini:
        denda_pkb_baru += bulan_terlambat × 1% × PKB_baru_tahun_ini
        denda_opsen    += bulan_terlambat × 1% × Opsen_tahun_ini

  JIKA period_start < cutoff:
    → Tarif lama (1.5%, 1%, atau 0.5%)
    → PKB_lama += dpPkb × tarifLama
    → TIDAK ada opsen
    → Jika period_end < hari_ini:
        denda_pkb_lama += bulan_terlambat × 1% × PKB_lama_tahun_ini

  period_start = period_end
```

**Denda dihitung dari `period_end` ke `hari_ini`, bukan dari `SD_NOTICE`.**
Ini karena setiap tahun punya jatuh tempo sendiri.

---

## Perubahan File

### `tax_helper.php`
- **HAPUS** `hitungDendaPkb()` → digantikan oleh fungsi baru
- **TAMBAH** `getTarifPkb(string $kGuna, bool $tarifBaru): float`
- **TAMBAH** `hitungPkbLengkap(float $dpPkb, string $kGuna, string $sdNotice): array`
  - Return: `pkb_lama`, `pkb_baru`, `pkb_total`, `opsen_pkb`, `denda_pkb_lama`, `denda_pkb_baru`, `denda_pkb_total`, `denda_opsen`, `tahun_lama`, `tahun_baru`

### `calculate_tax.php`
- Ganti blok PKB + denda dengan satu pemanggilan `hitungPkbLengkap()`
- Update response JSON dengan field baru

### `index.php`
- Tambahkan baris breakdown "PKB Lama (X tahun)" dan "PKB Baru (Y tahun)" — **hanya tampil** jika keduanya > 0
- Update field denda menggunakan `denda_pkb_total` dan `denda_opsen`

