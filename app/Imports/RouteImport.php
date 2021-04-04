<?php

namespace App\Imports;

use App\Models\Route;
use App\Models\Fleet;
use Indonesia;
use Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

// WithBatchInserts, WithChunkReading,
class RouteImport implements WithHeadingRow, WithValidation, OnEachRow
{
    use RemembersRowNumber, Importable;

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();
        // dd(gettype($row['kota_asal']));

        $armada = collect(Fleet::all());
        $armada = $armada->where('slug', strtolower($row['armada']))->first();

        $existRoute = Route::where([
            ['fleet_id','=',$armada['id']],
            ['origin','=',strtoupper($row['kota_asal'])],
            ['destination_island','=',strtoupper($row['pulau_tujuan'])],
            ['destination_city','=',strtoupper($row['kota_tujuan'])],
            ['destination_district','=',strtoupper($row['kecamatan_tujuan'])]
        ])->first();

        if ($existRoute) {
            Log::warning('exist route index '.$rowIndex);
            Log::warning('data => '.$existRoute);
            return null;
        }
        $route = new Route;
        $route->fleet_id = $armada['id'];
        $route->origin = strtoupper($row['kota_asal']);
        $route->destination_island = strtoupper($row['pulau_tujuan']);
        $route->destination_city = strtoupper($row['kota_tujuan']);
        $route->destination_district = strtoupper($row['kecamatan_tujuan']);
        $route->price = $row['biaya_barang'];
        $route->minimum_weight = $row['berat_min'];
        $route->price_car = $row['biaya_mobil'];
        $route->price_motorcycle = $row['biaya_motor'];
        return $route->save();
    }

    // public function model(array $row)
    // {
    //     $currentRowNumber = $this->getRowNumber();
    //     return new Route([
    //         'fleet_id'              => $row['armada'],
    //         'origin'                => $row['kota_asal'],
    //         'destination_island'    => $row['pulau_tujuan'],
    //         'destination_city'      => $row['kota_tujuan'],
    //         'destination_district'  => $row['kecamatan_tujuan'],
    //         'price'                 => $row['biaya_barang'],
    //         'minimum_weight'        => $row['berat_min'],
    //         'price_car'             => $row['biaya_mobil'],
    //         'price_motorcycle'      => $row['biaya_motor']
    //     ]);
    // }

    public function rules(): array
    {
        return [
            'kota_asal' => function($attribute, $value, $onFailure) {
                $result = Indonesia::search($value)->allCities();
                if (count($result) <= 0) {
                    $onFailure('Kota asal tidak ditemukan');
                }
            },
            'pulau_tujuan' => Rule::in(['SUMATERA','JAWA','BALI','KALIMANTAN','SULAWESI','MALUKU','RIAU','MENTAWAI','HALMAHERA','TIMOR','MADURA','BIAK','SUNDA']),
            'kota_tujuan' => function($attribute, $value, $onFailure) {
                $result = Indonesia::search($value)->allCities();
                if (count($result) <= 0) {
                    $onFailure('Kota tujuan tidak ditemukan');
                }
            },
            'kecamatan_tujuan' => function($attribute, $value, $onFailure) {
                $result = Indonesia::search($value)->allDistricts();
                if (count($result) <= 0) {
                    $onFailure('Kecamatan tujuan tidak ditemukan');
                }
            },
            'biaya_barang' => 'numeric',
            'berat_min' => 'numeric',
            'biaya_motor' => 'numeric',
            'biaya_mobil' => 'numeric'
        ];
    }

    public function customValidationAttributes()
    {
        return [
            'kota_asal' => 'Kota asal',
            'pulau_tujuan' => 'Pulau tujuan',
            'kota_tujuan' => 'Kota tujuan',
            'kecamatan_tujuan' => 'Kecamatan tujuan',
            'biaya_barang' => 'Biaya barang standar',
            'berat_min' => 'Berat minimal',
            'biaya_motor' => 'Biaya motor',
            'biaya_mobil' => 'Biaya mobil',
        ];
    }

    // public function batchSize(): int
    // {
    //     return 20;
    // }

    // public function chunkSize(): int
    // {
    //     return 20;
    // }
}
