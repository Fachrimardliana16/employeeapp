<div class="space-y-4">
    @php
        $properties = $getState();
        $attributes = $properties['attributes'] ?? [];
        $old = $properties['old'] ?? [];
        $hasChanges = !empty($attributes) || !empty($old);
    @endphp

    @if($hasChanges)
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Field</th>
                        @if(!empty($old))
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Old Value</th>
                        @endif
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">New Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($attributes as $key => $value)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 capitalize">
                                {{ str_replace('_', ' ', $key) }}
                            </td>
                            @if(!empty($old))
                                <td class="px-4 py-3 text-red-600 dark:text-red-400">
                                    <span class="rounded bg-red-50 dark:bg-red-900/30 px-2 py-1 line-through decoration-red-500/50">
                                        {{ is_array($old[$key] ?? null) ? json_encode($old[$key]) : ($old[$key] ?? '-') }}
                                    </span>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-green-600 dark:text-green-400">
                                <span class="rounded bg-green-50 dark:bg-green-900/30 px-2 py-1">
                                    {{ is_array($value) ? json_encode($value) : $value }}
                                </span>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Handle cases where only 'old' exists (like deleting) --}}
                    @if(empty($attributes) && !empty($old))
                        @foreach($old as $key => $value)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 capitalize">
                                    {{ str_replace('_', ' ', $key) }}
                                </td>
                                <td class="px-4 py-3 text-red-600 dark:text-red-400">
                                    <span class="rounded bg-red-50 dark:bg-red-900/30 px-2 py-1 line-through decoration-red-500/50">
                                        {{ is_array($value) ? json_encode($value) : $value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-400 italic">Deleted</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="flex items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 text-gray-500 italic text-sm border border-dashed border-gray-200 dark:border-gray-700">
            <x-filament::icon
                icon="heroicon-m-information-circle"
                class="w-5 h-5 text-gray-400"
            />
            No detailed property changes recorded for this activity.
        </div>
    @endif
</div>
