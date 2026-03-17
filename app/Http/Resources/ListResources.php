<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResources extends JsonResource
{
    protected static array $boundKeys = [];

    public static function bindKeys(array $keys): void
    {
        self::$boundKeys = $keys;
    }

    
    public function toArray(Request $request): array
    {
        $source = (array) $this->resource;
        $keys = self::$boundKeys;

        if (empty($keys)) {
            $keys = array_keys($source);
        }

        $data = [];

        $labelMap = [
            'customer_name' => 'Company Name',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip',
            'tot_sales' => 'Total Sales',
            'cust_unique_key' => 'cust_unique_key',
        ];

        foreach ($keys as $key) {
            $label = $labelMap[$key] ?? $key;
            $data[$label] = $source[$key] ?? null;
        }

        return array_merge($data, [
            "next"                  => "company_customer_details",
            //"__complete_next"       => "company_client_list_".$this->id,
            "__expand-type"         => "more",
            "__view_type_final"     => "simple_listing",
            "__view_type_more"      => "new_table",
            //"sub"                   => [],
            //"sub"                   => $sub,
            //"action"     => "",
            //"__sub_view_type"       => "pdb_list"
        ]);

    }

}
