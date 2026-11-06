@extends('layouts.app')
@section('title', 'Cek NJKB — BAPENDA NTT')

@push('styles')
<style>
:root {
    --primary:      #2563eb;
    --primary-dark: #1d4ed8;
    --primary-soft: #eff6ff;
    --success:      #059669;
    --success-soft: #ecfdf5;
    --border:       #e2e8f0;
    --muted:        #64748b;
    --radius:       20px;
    --shadow:       0 12px 40px rgba(15,23,42,.07);
}

/* ---- HERO ---- */
.njkb-hero {
    margin-bottom: 28px;
}
.njkb-hero h1 {
    font-size: clamp(24px,4vw,32px);
    font-weight: 800;
    letter-spacing: -.5px;
    margin-bottom: 6px;
}
.njkb-hero p { color: var(--muted); font-size: 15px; line-height: 1.6; }

/* ---- CARD ---- */
.card {
    background: rgba(255,255,255,.96);
    border: 1px solid rgba(226,232,240,.9);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 20px;
    position: relative;
}
.card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), #60a5fa);
}

.card-inner { padding: 26px; }

/* ---- FORM ---- */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group { display: flex; flex-direction: column; gap: 7px; }

.form-label {
    font-size: 12px; font-weight: 700;
    color: #334155;
    letter-spacing: .4px;
    text-transform: uppercase;
    display: flex; align-items: center; gap: 5px;
}

.input-wrap { position: relative; }
.input-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8; pointer-events: none; font-size: 15px;
}
.input-wrap input { padding-left: 40px; }

input[type="text"],
select {
    width: 100%; height: 48px;
    padding: 0 14px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    background: #f8fafc;
    color: #0f172a;
    font-family: inherit; font-size: 14px; font-weight: 500;
    transition: .2s ease; outline: none;
    appearance: none;
}
input:hover, select:hover { border-color: #cbd5e1; background: #fff; }
input:focus, select:focus {
    background: #fff;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,.10);
}

.btn-search {
    grid-column: 1/-1;
    height: 50px; border: none; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
    font-family: inherit; font-size: 14px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 8px 18px rgba(37,99,235,.18);
    transition: .2s ease;
}
.btn-search:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(37,99,235,.25); }
.btn-search:active { transform: translateY(0); }

/* ---- RESULT HEADER ---- */
.result-head {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    flex-wrap: wrap;
}
.result-title-row { display: flex; align-items: center; gap: 10px; }
.result-icon {
    width: 38px; height: 38px; border-radius: 11px;
    background: var(--success-soft); color: var(--success);
    display: flex; align-items: center; justify-content: center; font-size: 17px;
}
.result-title  { font-size: 15px; font-weight: 800; }
.result-sub    { font-size: 12px; color: var(--muted); margin-top: 1px; }
.count-badge {
    padding: 5px 12px; border-radius: 999px;
    background: var(--primary-soft); color: var(--primary);
    font-size: 12px; font-weight: 700;
}

/* ---- TABLE ---- */
.table-wrap { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; min-width: 500px; }
th {
    padding: 12px 18px;
    background: #f8fafc;
    text-align: left;
    font-size: 11px; font-weight: 800; color: var(--muted);
    text-transform: uppercase; letter-spacing: .5px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
td {
    padding: 13px 18px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px; vertical-align: middle;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fafbff; }

.year-badge {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 6px;
    background: #f1f5f9; color: #475569;
    font-size: 12px; font-weight: 700;
}
.merk-text  { font-weight: 700; color: #0f172a; }
.tipe-text  { color: var(--muted); font-size: 13px; margin-top: 1px; }
.njkb-val   { font-weight: 800; color: #0f172a; font-size: 15px; }
.bobot-val  { color: var(--muted); font-size: 13px; margin-top: 1px; }

/* ---- EMPTY STATE ---- */
.empty-state {
    padding: 52px 24px;
    text-align: center;
}
.empty-icon { font-size: 44px; margin-bottom: 14px; }
.empty-title { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
.empty-desc  { color: var(--muted); font-size: 14px; line-height: 1.6; }

/* ---- RESPONSIVE ---- */
@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
    .result-head { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@section('content')
<div class="njkb-hero">
    <h1>🔍 Cek Data NJKB</h1>
    <p>Temukan Nilai Jual Kendaraan Bermotor berdasarkan merek, tipe, dan tahun pembuatan dari database resmi BAPENDA NTT.</p>
</div>

{{-- SEARCH CARD --}}
<div class="card">
    <div class="card-inner">
        <form id="search-form" method="GET" action="{{ route('cek-njkb') }}">
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="merek">🏭 Merek Kendaraan</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔎</span>
                        <input type="text"
                               id="merek"
                               name="merek"
                               value="{{ $merekInput }}"
                               placeholder="Contoh: HONDA, TOYOTA ..."
                               list="list-merk"
                               autocomplete="off">
                    </div>
                    <datalist id="list-merk">
                        @foreach($listMerk as $merk)
                            <option value="{{ $merk }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipe">🚗 Tipe / Model</label>
                    <div class="input-wrap">
                        <span class="input-icon">📝</span>
                        <input type="text"
                               id="tipe"
                               name="tipe"
                               value="{{ $tipeInput }}"
                               placeholder="Contoh: BEAT, AVANZA ...">
                    </div>
                </div>

                <button type="submit" class="btn-search" id="search-btn">
                    <span>🔍</span>
                    <span>Cari Data NJKB</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- RESULT CARD --}}
@if($pencarianDilakukan)
<div class="card">
    <div class="result-head">
        <div class="result-title-row">
            <div class="result-icon">📊</div>
            <div>
                <div class="result-title">Hasil Pencarian</div>
                <div class="result-sub">
                    @if($merekInput || $tipeInput)
                        Query: {{ implode(' ', array_filter([$merekInput, $tipeInput])) }}
                    @endif
                </div>
            </div>
        </div>
        <span class="count-badge">{{ count($hasilPencarian) }} data ditemukan</span>
    </div>

    @if(count($hasilPencarian) > 0)
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tahun</th>
                    <th>Merek & Tipe</th>
                    <th>NJKB (Rp)</th>
                    <th>Bobot</th>
                    <th>Dasar Pengenaan PKB</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hasilPencarian as $row)
                <tr>
                    <td><span class="year-badge">{{ $row->tahun_buat }}</span></td>
                    <td>
                        <div class="merk-text">{{ $row->merk }}</div>
                        <div class="tipe-text">{{ $row->tipe }}</div>
                    </td>
                    <td>
                        <div class="njkb-val">Rp {{ number_format($row->njkb, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div class="bobot-val">{{ number_format($row->bobot, 4) }}</div>
                    </td>
                    <td>
                        <div class="njkb-val" style="color: var(--primary);">
                            Rp {{ number_format($row->njkb * $row->bobot, 0, ',', '.') }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">🚗</div>
        <div class="empty-title">Data Tidak Ditemukan</div>
        <div class="empty-desc">
            Tidak ada data NJKB yang cocok dengan pencarian Anda.<br>
            Coba gunakan kata kunci yang lebih umum atau periksa ejaan.
        </div>
    </div>
    @endif
</div>
@elseif(!$merekInput && !$tipeInput)
<div class="card">
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <div class="empty-title">Mulai Pencarian NJKB</div>
        <div class="empty-desc">
            Masukkan merek atau tipe kendaraan di form di atas<br>
            untuk menampilkan data Nilai Jual Kendaraan Bermotor.
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.getElementById('search-form').addEventListener('submit', function () {
    const btn = document.getElementById('search-btn');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span><span>Mencari...</span>';
});
</script>
@endpush
