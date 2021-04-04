<?php

namespace App\Exports;

use App\Models\Route;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

class RouteExport implements FromCollection, WithHeadings, Responsable
{
    use Exportable;

    private $fileName = 'route.xlsx';

    private $writerType = Excel::XLSX;

    private $headers = [
        'Content-Type' => 'text/xlsx',
    ];

    public function collection()
    {
        $route = Route::with('fleet')->get()->take(5)->makeHidden(['id','fleet_id']);
        $route = $route->map(function($q) {
            $fleet = $q->fleet->slug;
            $q->fleet_slug = $fleet;
            return $q;
        });
        return $route;
    }

    public function headings(): array
    {
        return [
            'Asal Kota',
            'Pulau Tujuan',
            'Kota Tujuan',
            'Kecamatan Tujuan',
            'Biaya Barang',
            'Berat Min',
            'Biaya Mobil',
            'Biaya Motor',
            'Armada',
        ];
    }
}
