<?php

namespace App\Exports;

use App\Models\Pasien;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PasienExport implements FromCollection, WithHeadings
{
    protected array $columns;

    protected ?string $tanggalDari;

    protected ?string $tanggalSampai;

    protected array $columnLabels = [
        'nama_pasien' => 'Nama Pasien',
        'username' => 'Username',
        'email' => 'Email Akun',
        'nik' => 'NIK',
        'no_bpjs' => 'No. BPJS',
        'no_emr' => 'Nomor EMR',
        'alamat' => 'Alamat',
        'no_hp_pasien' => 'No HP',
        'tanggal_lahir' => 'Tanggal Lahir',
        'jenis_kelamin' => 'Jenis Kelamin',
        'golongan_darah' => 'Golongan Darah',
        'status_perkawinan' => 'Status Perkawinan',
        'pekerjaan' => 'Pekerjaan',
        'nama_penanggung_jawab' => 'Nama Penanggung Jawab',
        'no_hp_penanggung_jawab' => 'No HP Penanggung Jawab',
        'alergi' => 'Alergi',
        'barcode_pasien' => 'Barcode Pasien',
        'created_at' => 'Tanggal Dibuat',
    ];

    public function __construct(array $columns, ?string $tanggalDari = null, ?string $tanggalSampai = null)
    {
        $this->columns = $columns;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function collection()
    {
        $query = Pasien::with('user')->orderBy('id', 'asc');

        if ($this->tanggalDari) {
            $query->whereDate('created_at', '>=', $this->tanggalDari);
        }

        if ($this->tanggalSampai) {
            $query->whereDate('created_at', '<=', $this->tanggalSampai);
        }

        $pasienList = $query->get();

        return $pasienList->map(function ($pasien) {
            $row = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'username':
                        $row[] = $pasien->user->username ?? '';
                        break;

                    case 'email':
                        $row[] = $pasien->user->email ?? '';
                        break;

                    case 'tanggal_lahir':
                        $row[] = $pasien->tanggal_lahir
                            ? Carbon::parse($pasien->tanggal_lahir)->format('d-m-Y')
                            : '';
                        break;

                    case 'created_at':
                        $row[] = $pasien->created_at
                            ? $pasien->created_at->format('d-m-Y H:i:s')
                            : '';
                        break;

                    default:
                        $row[] = $pasien->{$column} ?? '';
                        break;
                }
            }

            return $row;
        });
    }

    public function headings(): array
    {
        return collect($this->columns)
            ->map(fn ($column) => $this->columnLabels[$column] ?? ucfirst(str_replace('_', ' ', $column)))
            ->toArray();
    }
}
