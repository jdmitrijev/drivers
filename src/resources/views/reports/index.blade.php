<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        table { width: 100%; }
        th, td { text-align: left; }
        th.num, td.num { text-align: right; white-space: nowrap; }
    </style>
    <script>
        function onTripChange(sel){
            const id = sel.value;
            if(!id){ window.location.href = "{{ route('reports.index') }}"; return; }
            window.location.href = "{{ url('reports') }}/" + id;
        }
    </script>
    </head>
<body>
<main class="container">
    <header style="display:flex;gap:1rem;align-items:center;justify-content:space-between;flex-wrap:wrap;">
        <h2 style="margin:0;">Reports</h2>
        <label style="margin:0;">
            <span>Trip</span>
            <select onchange="onTripChange(this)">
                <option value="">Select a trip…</option>
                @foreach($trips as $trip)
                    <option value="{{ $trip->id }}" @selected(optional($selectedTrip)->id === $trip->id)>
                        {{ $trip->from }} → {{ $trip->to }} ({{ $trip->start_date }})
                    </option>
                @endforeach
            </select>
        </label>
    </header>

    @if($selectedTrip)
    <article>
        <h4 style="margin-bottom:0.5rem;">Trip: {{ $selectedTrip->from }} → {{ $selectedTrip->to }}</h4>
        <small>{{ $selectedTrip->start_date }} — {{ $selectedTrip->end_date }}</small>

        <table role="grid">
            <thead>
                <tr>
                    <th>Expense/Driver</th>
                    <th class="num">Amount, $</th>
                    @foreach($drivers as $driver)
                        <th class="num">{{ $driver->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="num">{{ number_format($row['amount'], 2) }}</td>
                        @foreach($drivers as $driver)
                            <td class="num">{{ number_format($row['perDriver'][$driver->id] ?? 0, 2) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 2 + count($drivers) }}">No expenses yet.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th>Total:</th>
                    <th class="num">{{ number_format($totals['total'] ?? 0, 2) }}</th>
                    @foreach($drivers as $driver)
                        <th class="num">{{ number_format($totals['perDriver'][$driver->id] ?? 0, 2) }}</th>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </article>
    @endif
</main>
</body>
</html>


