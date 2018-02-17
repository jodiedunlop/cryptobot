<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Coin Rank</title>

    <!-- Fonts -->
    <link href="/css/app.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet" type="text/css">
    <style>
        body, td, p {
            font-family: "Source Sans Pro", sans-serif;
        }

        .mr-3 {
            margin-right: 1em;
        }

        .green {
            color: #38C172;
        }

        .red {
            color: #EB0D8C;
        }
    </style>
</head>
<body>
<div class="container">

    <table class="table table-striped table-dark">
        <thead>
        <tr>
            <th scope="col">Rank</th>
            <th scope="col">Name</th>
            <th scope="col">BTC</th>
            <th scope="col">USD</th>
            <th scope="col">24h %</th>
            <th scope="col" class="text-right">Total Supply</th>
            <th scope="col" class="text-right">Market Cap (USD)</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($coins as $coin)
            <tr>
                <th scope="row">{{ $coin->rank }}</th>
                <td><img src="{{ $coin->image_url }}" height="32" class="mr-3"> {{ $coin->full_name }}</td>
                <td>{{ \App\Util\PriceUtil::formatDecimal($coin->priceFor('btc')) }}</td>
                <td>${{ \App\Util\PriceUtil::formatDecimal($coin->priceFor('usd')) }}</td>
                <td>
                    @if ($coin->percent_change_24h != 0)
                        @if ($coin->percent_change_24h < 0)
                            <span class="red">&#x25bc;</span>
                        @else
                            <span class="green">&#x25b2;</span>
                        @endif
                        {{ \App\Util\PriceUtil::formatPercentage($coin->percent_change_24h) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ \App\Util\PriceUtil::formatLargeAmount($coin->total_supply) }}</td>
                <td class="text-right">${{ \App\Util\PriceUtil::formatLargeAmount($coin->market_cap_usd) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $coins->links() }}
</div>
</body>
</html>