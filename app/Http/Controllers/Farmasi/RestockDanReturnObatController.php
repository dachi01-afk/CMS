<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RestockDanReturnObatController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('supplier')
            ->select('id', 'nama_supplier')
            ->orderBy('nama_supplier')
            ->get();

        $depots = DB::table('depot')
            ->select('id', 'nama_depot')
            ->orderBy('nama_depot')
            ->get();

        $obats = DB::table('obat')
            ->select('id', 'nama_obat', 'kode_obat')
            ->orderBy('nama_obat')
            ->get();

        return view('farmasi.restock-dan-return-obat.restock-dan-return-obat', compact(
            'suppliers',
            'depots',
            'obats'
        ));
    }

    public function getData(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');

        $baseQuery = DB::table('restock_obat as ro')
            ->leftJoin('supplier as s', 'ro.supplier_id', '=', 's.id')
            ->leftJoin('restock_obat_detail as rod', 'ro.id', '=', 'rod.restock_obat_id')
            ->leftJoin('obat as o', 'rod.obat_id', '=', 'o.id')
            ->select(
                'ro.id',
                'ro.no_faktur',
                'ro.tanggal_terima',
                'ro.created_at',
                'ro.total_tagihan',
                'ro.tanggal_jatuh_tempo',
                'ro.status_transaksi',
                DB::raw("COALESCE(s.nama_supplier, '-') as supplier_nama"),
                DB::raw("'Restock Obat' as jenis"),
                DB::raw("SUM(COALESCE(rod.qty, 0)) as jumlah_item"),
                DB::raw("GROUP_CONCAT(DISTINCT o.nama_obat ORDER BY o.nama_obat ASC SEPARATOR ', ') as nama_item")
            )
            ->groupBy(
                'ro.id',
                'ro.no_faktur',
                'ro.tanggal_terima',
                'ro.created_at',
                'ro.total_tagihan',
                'ro.tanggal_jatuh_tempo',
                'ro.status_transaksi',
                's.nama_supplier'
            );

        if (!empty($search)) {
            $baseQuery->havingRaw("
                ro.no_faktur LIKE ?
                OR COALESCE(s.nama_supplier, '-') LIKE ?
                OR COALESCE(GROUP_CONCAT(DISTINCT o.nama_obat ORDER BY o.nama_obat ASC SEPARATOR ', '), '') LIKE ?
            ", [
                "%{$search}%",
                "%{$search}%",
                "%{$search}%"
            ]);
        }

        $recordsFiltered = DB::table(DB::raw("({$baseQuery->toSql()}) as x"))
            ->mergeBindings($baseQuery)
            ->count();

        $recordsTotal = DB::table('restock_obat')->count();

        $data = DB::table(DB::raw("({$baseQuery->toSql()}) as x"))
            ->mergeBindings($baseQuery)
            ->orderByDesc('id')
            ->offset($start)
            ->limit($length)
            ->get();

        $formatted = $data->map(function ($row) {
            $kode = 'RST-' . str_pad($row->id, 5, '0', STR_PAD_LEFT);

            $badgeApprove = $row->status_transaksi === 'Final'
                ? '<span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">Sudah</span>'
                : '<span class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">Draft</span>';

            $badgeJenis = '<span class="inline-flex items-center px-2 py-1 text-xs font-medium text-sky-700 bg-sky-100 rounded-full">Restock</span>';

            $aksi = '
                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                        class="btn-detail inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-sky-600 rounded-lg hover:bg-sky-700"
                        data-id="' . $row->id . '">
                        <i class="fa-solid fa-eye"></i>
                        Detail
                    </button>
                </div>
            ';

            return [
                'kode'          => $kode,
                'no_faktur'     => $row->no_faktur ?? '-',
                'jenis'         => $badgeJenis,
                'tanggal_kirim' => $row->tanggal_terima ? date('d-m-Y', strtotime($row->tanggal_terima)) : '-',
                'tanggal_buat'  => $row->created_at ? date('d-m-Y H:i', strtotime($row->created_at)) : '-',
                'supplier'      => $row->supplier_nama ?? '-',
                'nama_item'     => $row->nama_item ?? '-',
                'jumlah'        => (int) $row->jumlah_item,
                'diapprove'     => $badgeApprove,
                'total_harga'   => 'Rp ' . number_format((float) $row->total_tagihan, 0, ',', '.'),
                'tempo'         => $row->tanggal_jatuh_tempo ? date('d-m-Y', strtotime($row->tanggal_jatuh_tempo)) : '-',
                'aksi'          => $aksi,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $formatted,
        ]);
    }

    public function storeRestock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:supplier,id',
            'depot_id' => 'required|exists:depot,id',
            'no_faktur' => 'required|string|max:255|unique:restock_obat,no_faktur',
            'tanggal_terima' => 'required|date',
            'tanggal_jatuh_tempo' => 'nullable|date',
            'status_transaksi' => 'required|in:Draft,Final',

            'items' => 'required|array|min:1',
            'items.*.obat_id' => 'required|exists:obat,id',
            'items.*.nama_batch' => 'required|string|max:255',
            'items.*.tanggal_kadaluarsa_obat' => 'nullable|date',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'items.*.diskon_type' => 'nullable|in:nominal,persen',
            'items.*.diskon_value' => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Minimal harus ada 1 item obat.',
            'items.*.obat_id.required' => 'Obat wajib dipilih.',
            'items.*.nama_batch.required' => 'Nama batch wajib diisi.',
            'items.*.qty.required' => 'Qty wajib diisi.',
            'items.*.harga_beli.required' => 'Harga beli wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $grandTotal = 0;

            $detailRows = [];

            foreach ($request->items as $item) {
                $qty = (int) $item['qty'];
                $hargaBeli = (float) $item['harga_beli'];
                $subtotal = $qty * $hargaBeli;

                $diskonType = $item['diskon_type'] ?? null;
                $diskonValue = isset($item['diskon_value']) ? (float) $item['diskon_value'] : 0;
                $diskonAmount = 0;

                if ($diskonType === 'persen') {
                    $diskonAmount = ($subtotal * $diskonValue) / 100;
                } elseif ($diskonType === 'nominal') {
                    $diskonAmount = $diskonValue;
                }

                if ($diskonAmount > $subtotal) {
                    $diskonAmount = $subtotal;
                }

                $totalSetelahDiskon = $subtotal - $diskonAmount;
                $grandTotal += $totalSetelahDiskon;

                $detailRows[] = [
                    'obat_id' => $item['obat_id'],
                    'nama_batch' => $item['nama_batch'],
                    'tanggal_kadaluarsa_obat' => $item['tanggal_kadaluarsa_obat'] ?? null,
                    'qty' => $qty,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'diskon_type' => $diskonType,
                    'diskon_value' => $diskonValue,
                    'diskon_amount' => $diskonAmount,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                ];
            }

            $restockId = DB::table('restock_obat')->insertGetId([
                'supplier_id' => $request->supplier_id,
                'depot_id' => $request->depot_id,
                'no_faktur' => $request->no_faktur,
                'tanggal_terima' => $request->tanggal_terima,
                'total_tagihan' => $grandTotal,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'status_transaksi' => $request->status_transaksi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($detailRows as $detail) {
                $batchObatId = DB::table('batch_obat')->insertGetId([
                    'obat_id' => $detail['obat_id'],
                    'nama_batch' => $detail['nama_batch'],
                    'tanggal_kadaluarsa_obat' => $detail['tanggal_kadaluarsa_obat'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('restock_obat_detail')->insert([
                    'restock_obat_id' => $restockId,
                    'obat_id' => $detail['obat_id'],
                    'batch_obat_id' => $batchObatId,
                    'qty' => $detail['qty'],
                    'harga_beli' => $detail['harga_beli'],
                    'subtotal' => $detail['subtotal'],
                    'diskon_type' => $detail['diskon_type'],
                    'diskon_value' => $detail['diskon_value'],
                    'diskon_amount' => $detail['diskon_amount'],
                    'total_setelah_diskon' => $detail['total_setelah_diskon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($request->status_transaksi === 'Final') {
                    $depotObat = DB::table('depot_obat')
                        ->where('depot_id', $request->depot_id)
                        ->where('obat_id', $detail['obat_id'])
                        ->first();

                    if ($depotObat) {
                        DB::table('depot_obat')
                            ->where('id', $depotObat->id)
                            ->update([
                                'stok_obat' => ((int) $depotObat->stok_obat) + $detail['qty'],
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('depot_obat')->insert([
                            'depot_id' => $request->depot_id,
                            'obat_id' => $detail['obat_id'],
                            'stok_obat' => $detail['qty'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('batch_obat_depot')->insert([
                        'batch_obat_id' => $batchObatId,
                        'depot_id' => $request->depot_id,
                        'stok_obat' => $detail['qty'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $depot = DB::table('depot')->where('id', $request->depot_id)->first();

                    if ($depot) {
                        DB::table('depot')
                            ->where('id', $request->depot_id)
                            ->update([
                                'jumlah_stok_depot' => ((int) $depot->jumlah_stok_depot) + $detail['qty'],
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data restock obat berhasil disimpan.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}