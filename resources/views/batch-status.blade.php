<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Batch Latest Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Batch Latest Status</h1>
                <p class="text-sm text-gray-600 mt-1">Pattern: <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500 font-mono">batch:latest:*</code></p>
            </div>
            <a href="{{ url()->current() }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
                Refresh
            </a>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border-l-4 border-green-500 rounded">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border-l-4 border-red-500 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ url('/batch-status') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="system_ids" class="block text-sm font-medium text-gray-700 mb-1">System IDs to Sync (comma separated)</label>
                        <input type="text" name="system_ids" id="system_ids" value="9054,12407" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label for="payload" class="block text-sm font-medium text-gray-700 mb-1">Job Payload (JSON)</label>
                        <textarea name="payload" id="payload" rows="8" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border font-mono">{{ json_encode([ 'run_update_sql' => [ 'update ~DB_PREFIX~manager set managerNAME=? where managerID = ?', ['Last Updated', 14], ], 'run_select_sql' => [ 'select count(1) as count from ~DB_PREFIX~manager', [], ], 'system_info' => ['end here'], ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="mt-4 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition shadow-sm">
                    Run Batch
                </button>
            </form>
        </div>

        <div class="p-0">
            @if(empty($data))
                <div class="p-6 bg-yellow-50 text-yellow-700 border-l-4 border-yellow-400">
                    No data found in Redis for this pattern.
                </div>
            @else
                @php
                    $formatDisplayValue = static function ($value) {
                        if (is_array($value) || is_object($value)) {
                            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        }

                        if ($value === null) {
                            return 'N/A';
                        }

                        if (is_bool($value)) {
                            return $value ? 'true' : 'false';
                        }

                        return (string) $value;
                    };
                @endphp

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Key</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $domain_result = [];        
                        @endphp
                        @foreach($data as $key => $value)


                        @if(str_contains($key, 'batch:latest:systems'))
                            @php
                                $parsed = json_decode($value, true);
                                if(isset($parsed['system_info']['domain_id']) && isset($parsed['run_select_sql']['result'])){
                                    $domain_result[$parsed['system_info']['domain_id']] = $parsed['run_select_sql']['result'];
                                }
                            @endphp
                        @endif                                                

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-mono">
                                    {{ $key }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 break-all font-mono whitespace-pre-wrap">
                                    {{ $formatDisplayValue($value) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table width="100%" class="mt-6 border-collapse border border-gray-300"> 
                <?php
                $first = true;
                $columns = [];
                    foreach($domain_result as $domain_id => $result){
                        foreach($result as $row){
                            if  ($first) {  
                                echo "<tr class='bg-gray-200'>";
                                echo "<th class='border border-gray-300 px-4 py-2'>Domain ID</th>";
                                    foreach($row as $col => $val) {
                                        echo "<th>{$col}</th>";
                                        $columns[] = $col;
                                    }
                                echo "</tr>";
                                $first = false;
                            }
                
                            echo "<tr class='hover:bg-gray-50'>";
                            echo "<td class='border border-gray-300 px-4 py-2'>$domain_id</td>";
                            
                            foreach($columns as $col) {
                                $val = $row[$col] ?? '';
                                echo "<td class='border border-gray-300 px-4 py-2'>$val</td>";
                            }
                            
                            echo "</tr>";
                        }
                    }
                 ?>               


                <table>

                    @foreach($domain_result as $domain_id => $result)
                        <tr>
                            <td>
                                Domain ID: {{ $domain_id }}
                            </td>
                            <td>
                                Result: <pre class="inline font-mono text-xs whitespace-pre-wrap">{{ $formatDisplayValue($result) }}</pre>
                            </td>
                        </tr>               
                       
                    @endforeach
                </table>


            @endif
        </div>
    </div>
   <script>
    let parsedValues = [];

    @foreach($data as $key => $value)
        @if(str_contains($key, 'batch:latest:systems'))
            parsedValues.push(JSON.parse(@json($value)));
        @endif
    @endforeach

    result = {}; 
for(a in parsedValues){
	item = parsedValues[a];
 	result[item.system_info.domain_id] = item.run_select_sql.result
	
}
console.log(result);

</script>
</body>
</html>
