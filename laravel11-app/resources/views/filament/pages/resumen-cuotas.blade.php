{{--<x-filament-panels::page>--}}
<div>
    <h3>Resumen de Pagos</h3>
    <h3>{{$usuario->name}}</h3>
    <p></p>

    <div><h3>Abonos</h3>
        <table border="1" style="width: 100%; text-align: center; border: 1px;">
            <thead>
            <tr>
                <th>Fecha de Pago</th>
                <th>Monto</th>
            </tr>
            </thead>
            <tbody>
            @foreach($abonos as $key => $abono)
                <tr>
                    <td>{{ $key }}</td>
                    <td>${{ number_format($abono, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td><h5>Total</h5></td>
                <td>${{ number_format(array_sum($abonos), 2) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>

<div><h3>Cargos</h3>
    <table border="1" style="width: 100%; text-align: center;">
        <thead>
        <tr>
            <th>Periodo</th>
            <th>Ordinaria</th>
            <th>Extraordinaria</th>
        </tr>
        </thead>
        <tbody>
        @foreach($cargos as $key => $cargo)
            <tr>
                <td>{{ $key }}</td>
                <td>${{ number_format($cargo['cuota_ordinaria'] ?? 0, 2) }}</td>
                <td>${{ number_format($cargo['cuota_extraordinaria'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr style="font-weight: bold;">
            <td>Total</td>
            <td>${{ number_format(array_sum(array_column($cargos, 'cuota_ordinaria')), 2) }}</td>
            <td>${{ number_format(array_sum(array_column($cargos, 'cuota_extraordinaria')), 2) }}</td>
        </tr>
    </table>
</div>
    <p></p>
    <div>
        <h3>Resumen</h3>
        <table border="1" style="width: 100%; text-align: center;">

            <tbody>
            <tr style="font-weight: bold;">
                <td>Total Abonos</td>
                <td>${{ number_format($totalAbonos, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td>Total Cargos</td>
                <td>${{ number_format($totalCargos, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td>Total Saldo</td>
                <td>${{ number_format($totalAbonos - $totalCargos) }}</td>
            </tr>
            </tbody>
        </table>
    </div>


</div>
{{--</x-filament-panels::page>--}}
