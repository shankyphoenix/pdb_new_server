<?php
namespace App\Http\Controllers\Pos;

use App\Models\Company;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use App\Services\DuckService;
use App\Http\Resources\ListResources;

class ServiceController extends Controller
{
    public function  list(DuckService $duck){

      $filepath = config('pdb.duckdb_file');

        $combined = $duck->query("
            SELECT ANY_VALUE(customer_name) as customer_name,any_value(sic) as sic, ANY_VALUE(city) as city, ANY_VALUE(state) as state, ANY_VALUE(zip) as zip, ANY_VALUE(cust_unique_key) as cust_unique_key, sum(total_sales_value) as tot_sales FROM '$filepath' WHERE system_id = 12441 and invoice_date between '2025-03-01' and '2026-02-28' group by cust_unique_key,total_sales_value  order by sum(total_sales_value) desc limit 100
        ");   

        $combinedKeys = [];

        if ($combined->isNotEmpty()) {
            $combinedKeys = array_keys((array) $combined->first());
        }

        ListResources::bindKeys($combinedKeys);

        return ListResources::collection($combined)->additional([
            'keys' => $combinedKeys,
        ]);

    }
}