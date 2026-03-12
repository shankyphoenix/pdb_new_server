<?php

namespace App\Utilities;

class ResponseParserUtility
{
    /**
     * Parse the response body and extract/sum the 'count' from run_select_sql results.
     */
    public static function parseSelectCount(array $data): int
    {

        \Log::info("data.............",[$data]);
        // Navigation path: result -> run_select_sql -> result -> [array of rows]
        $rows = $data['result']['run_select_sql']['result'] ?? [];

        if (! is_array($rows)) {
            return 0;
        }

        $sum = 0;
        foreach ($rows as $row) {
            if (isset($row['count']) && is_numeric($row['count'])) {
                $sum += (int) $row['count'];
            }
        }

        return $sum;
    }
}