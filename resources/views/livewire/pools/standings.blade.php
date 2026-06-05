<div class="space-y-6">
    <flux:heading size="xl">{{ $pool->name }} · Ranking</flux:heading>

    <flux:card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500">
                    <th class="px-3 py-2">#</th>
                    <th class="px-3 py-2">Jogador</th>
                    <th class="px-3 py-2 text-right">Pontos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($standings as $index => $row)
                    <tr class="border-t border-zinc-100 dark:border-white/10">
                        <td class="px-3 py-2">{{ $index + 1 }}</td>
                        <td class="px-3 py-2">{{ $row['user']->name }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ $row['points'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-3 py-4 text-center text-zinc-500">Sem participantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>
</div>
