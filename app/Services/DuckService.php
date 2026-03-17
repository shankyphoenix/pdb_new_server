<?php
namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Collection;

class DuckService
{
    /**
     * Run a query and return a Laravel Collection
     */
    public function query(string $sql): Collection
    {
        $duckdbPath = config('pdb.duckdb_path');

         // Ensure the DuckDB binary exists
        // We use -json format so PHP can easily parse the result
        $result = Process::run("$duckdbPath -json -c \"$sql\"");

        if ($result->failed()) {
            throw new \Exception("DuckDB Error: " . $result->errorOutput());
        }

        return collect(json_decode($result->output()));
    }
}