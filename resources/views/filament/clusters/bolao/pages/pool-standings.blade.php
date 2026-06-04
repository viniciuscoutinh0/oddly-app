<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Jogador</th>
                    <th class="px-4 py-3 text-right">Pontos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->standings() as $index => $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">{{ $row['user']->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $row['points'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                            Nenhum participante ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
