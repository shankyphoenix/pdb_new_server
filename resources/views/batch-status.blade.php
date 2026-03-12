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
                <p class="text-sm text-gray-600 mt-1">Pattern: <code class="bg-gray-100 px-1 py-0.5 rounded text-red-500 font-mono">batch:lastest:*</code></p>
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

            <form action="{{ url('/batch-status') }}" method="POST" class="flex items-end gap-4">
                @csrf
                <div class="flex-grow">
                    <label for="system_ids" class="block text-sm font-medium text-gray-700 mb-1">System IDs to Sync (comma separated)</label>
                    <input type="text" name="system_ids" id="system_ids" value="9054,12407" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition shadow-sm">
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
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Key</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data as $key => $value)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-mono">
                                    {{ $key }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 break-all font-mono">
                                    {{ $value }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</body>
</html>