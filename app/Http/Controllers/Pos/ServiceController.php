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

        /* $combined = $duck->query("
            SELECT ANY_VALUE(customer_name) as customer_name,any_value(sic) as sic, ANY_VALUE(city) as city, ANY_VALUE(state) as state, ANY_VALUE(zip) as zip, ANY_VALUE(cust_unique_key) as cust_unique_key, sum(total_cost_value) as tot_sales FROM '$filepath' WHERE system_id = 9689 and invoice_date between '2025-02-01' and '2026-01-31' group by total_cost_value  order by sum(total_cost_value) desc limit 100
        "); */

        /*$combined = $duck->query("
            SELECT ANY_VALUE(assigned_manager_company) as customer_name,concat(any_value(city),' ',any_value(state)) as city_state, any_value(zip) as postal_code,any_value(country) as country,count(assigned_manager_company) as c, format('{:t,}', sum(total_cost_value)::BIGINT) as tot_cost,any_value(sic_name) as sic_name,round(sum(if(invoice_date between '2026-01-01' and '2026-01-31',total_cost_value,0))) as invoice_count, round(sum(if(invoice_date between '2025-10-01' and '2025-12-31',total_cost_value,0))/3) as a3_month_avg,round(sum(total_cost_value)/12) as a12_month_avg,a3_month_avg - a12_month_avg as diff,count(Distinct product_id) as sku,count(distinct cust_unique_key) as unique_cust FROM '$filepath' WHERE system_id = 9689 and invoice_date between '2025-02-01' and '2026-01-31' group by distributor_id,assigned_manager_company  order by assigned_manager_company asc limit 100
        ");*/
        

        $combined = $duck->query("
        WITH BaseData AS (
                SELECT 
                    distributor_id,                    
                    cust_unique_key,
                    SUM(total_cost_value) as customer_total_sales
                FROM '$filepath'
                WHERE system_id = 9689 
                AND invoice_date BETWEEN '2025-02-01' AND '2026-01-31'
                GROUP BY 1, 2
            ),
            ParetoCalc AS (
                SELECT 
                    distributor_id,                    
                    cust_unique_key,
                    customer_total_sales,
                    SUM(customer_total_sales) OVER (PARTITION BY distributor_id) as group_total,
                    SUM(customer_total_sales) OVER (PARTITION BY distributor_id ORDER BY customer_total_sales DESC) / group_total as running_pct
                FROM BaseData
            ),
            Top80Count AS (
                SELECT 
                    distributor_id,                      
                    COUNT(*) as customer_count_with_80_percent_sale
                FROM ParetoCalc
                WHERE running_pct <= 0.80 OR (running_pct > 0.80 AND running_pct - (customer_total_sales/group_total) < 0.80)
                GROUP BY 1
            )

            SELECT ANY_VALUE(m.assigned_manager_company) as customer_name,concat(any_value(m.city),' ',any_value(m.state)) as city_state, any_value(m.zip) as postal_code,any_value(m.country) as country,count(m.assigned_manager_company) as c, format('{:t,}', sum(m.total_cost_value)::BIGINT) as tot_cost,any_value(m.sic_name) as sic_name,round(sum(if(m.invoice_date between '2026-01-01' and '2026-01-31',m.total_cost_value,0))) as invoice_count, round(sum(if(m.invoice_date between '2025-10-01' and '2025-12-31',m.total_cost_value,0))/3) as a3_month_avg,round(sum(m.total_cost_value)/12) as a12_month_avg,a3_month_avg - a12_month_avg as diff,count(Distinct m.product_id) as sku,count(distinct m.cust_unique_key) as unique_cust,any_value(p.customer_count_with_80_percent_sale) as key_customer,round((key_customer/unique_cust)*100,2) as dep FROM '$filepath' m LEFT JOIN Top80Count p ON m.distributor_id = p.distributor_id WHERE m.system_id = 9689 and m.invoice_date between '2025-02-01' and '2026-01-31' group by m.distributor_id  order by ANY_VALUE(m.assigned_manager_company) asc limit 100
        ");
        
        
        /* $combined = $duck->query("
            SELECT customer_name,invoice_date,total_sales_value,total_cost_value  FROM '$filepath' where system_id = 9689 and  invoice_date between '2026-01-01' and '2026-01-31' order by invoice_date desc,customer_name asc limit 100
        "); */

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