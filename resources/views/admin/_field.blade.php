@php
    $kunci     = $row['kunci'];
    $nilai     = $row['nilai'];
    $isBoolean = str_contains($kunci, 'aktif') || str_contains($kunci, 'bebas');
    $isDate    = str_contains($kunci, 'tanggal') || str_contains($kunci, 'mulai')
              || str_contains($kunci, 'selesai') || str_contains($kunci, 'berlaku');
    $isNumber  = str_contains($kunci, 'rasio') || str_contains($kunci, 'persen') || str_contains($kunci, 'diskon') || str_contains($kunci, 'maks') || str_contains($kunci, 'bobot');
    $isTime    = str_contains($kunci, 'jam');
    $isLong    = $kunci === 'api_url_samsat' || $kunci === 'api_url_backup';
    $label     = ucwords(str_replace('_', ' ', $kunci));
@endphp

<div class="bg-white border border-slate-200/90 rounded-2xl p-4 transition-all hover:border-slate-300 shadow-sm {{ $isLong ? 'sm:col-span-2' : '' }}">
    <div class="flex items-center justify-between gap-2 mb-2">
        <label class="text-xs font-bold text-slate-700 block truncate" for="{{ $kunci }}" title="{{ $label }}">
            {{ $label }}
        </label>
        <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 shrink-0">
            {{ $kunci }}
        </span>
    </div>

    @if($isBoolean)
        <div class="flex items-center gap-3 pt-1">
            <input type="hidden" name="config[{{ $kunci }}]" value="0">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       id="{{ $kunci }}"
                       name="config[{{ $kunci }}]"
                       value="1"
                       class="sr-only peer"
                       {{ (int)$nilai === 1 ? 'checked' : '' }}
                       onchange="updateToggleLabel(this)">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            </label>
            <span class="text-xs font-bold font-mono transition-colors {{ (int)$nilai === 1 ? 'text-emerald-600' : 'text-slate-400' }}" id="label-{{ $kunci }}">
                {{ (int)$nilai === 1 ? 'AKTIF' : 'NONAKTIF' }}
            </span>
        </div>
    @elseif($isDate)
        <input type="date"
               id="{{ $kunci }}"
               name="config[{{ $kunci }}]"
               value="{{ $nilai }}"
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white font-mono"
               required>
    @elseif($isTime)
        <input type="text"
               id="{{ $kunci }}"
               name="config[{{ $kunci }}]"
               value="{{ $nilai }}"
               placeholder="08:00"
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white font-mono"
               required>
    @elseif($isNumber)
        <input type="number"
               id="{{ $kunci }}"
               name="config[{{ $kunci }}]"
               value="{{ $nilai }}"
               step="0.001"
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white font-mono"
               required>
    @else
        <input type="text"
               id="{{ $kunci }}"
               name="config[{{ $kunci }}]"
               value="{{ $nilai }}"
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white font-mono"
               required>
    @endif
</div>
