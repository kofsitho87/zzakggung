<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
{

    /**
     * @param array $row
     *
     * @return Product|null
     */
    public function model(array $row)
    {
        //$user = Auth::user();
        return new \App\Model\Product([
            'model_id' => $row[0],
            'name' => $row[1],
        ]);
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
}
