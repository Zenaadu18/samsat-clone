<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function hitungPajak()
    {
        return view('hitung_pajak');
    }

    /**
     * Cek NJKB — data diambil di controller, bukan di view.
     */
    public function cekNjkb(Request $request)
    {
        $merekInput = trim($request->query('merek', ''));
        $tipeInput  = trim($request->query('tipe', ''));

        $hasilPencarian     = [];
        $pencarianDilakukan = false;

        // Ambil daftar merek unik untuk dropdown
        $listMerk = DB::select('SELECT DISTINCT merk FROM njkb_data ORDER BY merk ASC');
        $listMerk = array_column($listMerk, 'merk');

        // Jalankan query pencarian jika form disubmit
        if ($request->isMethod('GET') && ($merekInput !== '' || $tipeInput !== '')) {
            $pencarianDilakukan = true;

            $query  = 'SELECT tahun_buat, merk, tipe, njkb, bobot FROM njkb_data WHERE 1=1';
            $params = [];

            if ($merekInput !== '') {
                $query   .= ' AND merk LIKE ?';
                $params[] = '%' . $merekInput . '%';
            }
            if ($tipeInput !== '') {
                $query   .= ' AND tipe LIKE ?';
                $params[] = '%' . $tipeInput . '%';
            }

            $query .= ' ORDER BY tahun_buat ASC';

            $hasilPencarian = DB::select($query, $params);
        }

        return view('cek_njkb', compact(
            'hasilPencarian',
            'pencarianDilakukan',
            'listMerk',
            'merekInput',
            'tipeInput'
        ));
    }

    public function pajakMassal()
    {
        return view('pajak_massal');
    }
}
