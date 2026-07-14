<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprobante #{{ $documento->Nombre ?? '' }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#222; background:#fff; }
    .page { padding:40px 48px; }

    /* Header */
    .header-table { width:100%; }
    .header-table td { vertical-align:middle; }
    .header-logo img { max-width:170px; max-height:85px; }
    .header-right { text-align:right; }
    .inv-title { font-size:22px; font-weight:700; color:#1a6b2e; text-transform:uppercase; letter-spacing:1px; }
    .inv-meta { font-size:12px; color:#555; margin-top:3px; }

    /* Divider */
    .divider { border:none; border-top:2.5px solid #1a6b2e; margin:20px 0; }

    /* Billing */
    .billing-table { width:100%; margin-bottom:24px; }
    .billing-table td { vertical-align:top; width:50%; }
    .section-label { font-size:9px; font-weight:700; text-transform:uppercase; color:#1a6b2e; letter-spacing:1px; margin-bottom:5px; }
    .billing-table p { font-size:12px; color:#333; line-height:1.6; }
    .bold-name { font-weight:700; font-size:13px; }
    .text-right { text-align:right; }

    /* Items table */
    .items-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    .items-table thead tr { background:#1a6b2e; }
    .items-table thead th { color:#fff; padding:8px 10px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:.5px; }
    .items-table thead th.right { text-align:right; }
    .items-table tbody tr { border-bottom:1px solid #e0e0e0; }
    .items-table tbody tr.even { background:#f8fdf9; }
    .items-table tbody td { padding:9px 10px; font-size:12px; color:#333; }
    .items-table tbody td.right { text-align:right; }

    /* Total */
    .total-outer { text-align:right; margin-bottom:28px; }
    .total-inner { display:inline-block; width:220px; border-top:2px solid #1a6b2e; padding-top:6px; margin-top:4px; }
    .total-row-table { width:100%; border-collapse:collapse; }
    .total-row-table td { padding:4px 6px; font-size:14px; font-weight:700; color:#1a6b2e; }
    .total-row-table .tl { text-align:left; }
    .total-row-table .tr { text-align:right; }

    /* Signature */
    .signature-section { border-top:1px solid #ddd; padding-top:18px; margin-bottom:18px; }
    .sig-spacer { height:40px; }
    .sig-line { border-bottom:1px solid #444; width:220px; }
    .sig-name { font-size:12px; color:#444; margin-top:3px; }
    .sig-role { font-size:10px; color:#888; }

    /* Footer */
    .footer { border-top:1px solid #e0e0e0; padding-top:12px; text-align:center; font-size:10px; color:#888; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Florida Runners">
                @endif
            </td>
            <td class="header-right">
                <div class="inv-title">Comprobante de Pago</div>
                <div class="inv-meta">N° {{ $documento->Nombre ?? 'S/N' }}</div>
                <div class="inv-meta">Fecha: {{ \Carbon\Carbon::parse($cuota->FechaPago)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- Billing --}}
    <table class="billing-table">
        <tr>
            <td>
                <div class="section-label">Emitido a</div>
                <p class="bold-name">{{ $user->name ?? '' }}</p>
                @if($user->persona->Direccion ?? false)
                    <p>{{ $user->persona->Direccion }}</p>
                @endif
                @if($user->persona->Comuna ?? false)
                    <p>{{ $user->persona->Comuna }}</p>
                @endif
                @if($user->email ?? false)
                    <p>{{ $user->email }}</p>
                @endif
            </td>
            <td class="text-right">
                <div class="section-label">Emitido por</div>
                <p style="font-weight:700;">Florida Runners</p>
                <p>Departamento de Tesorería</p>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Período</th>
                <th>Fecha de Pago</th>
                <th class="right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $item)
            <tr class="{{ $i % 2 === 0 ? '' : 'even' }}">
                <td>{{ $item->TipoCuota ?? 'Cuota' }}</td>
                <td>{{ $item->FechaPeriodo ? \Carbon\Carbon::parse($item->FechaPeriodo)->translatedFormat('F Y') : '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->FechaPago)->format('d/m/Y') }}</td>
                <td class="right">$ {{ number_format($item->Recaudado ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total --}}
    <div class="total-outer">
        <div class="total-inner">
            <table class="total-row-table">
                <tr>
                    <td class="tl">Total pagado</td>
                    <td class="tr">$ {{ number_format($records->sum('Recaudado'), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Signature --}}
    @if($aprobador)
    <div class="signature-section">
        <div class="section-label">Autorizado por</div>
        <div class="sig-spacer"></div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ $aprobadorNombre ?? ($aprobador->name ?? '') }}</div>
        <div class="sig-role">Tesorero · Florida Runners</div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Florida Runners &mdash; Este comprobante es válido como recibo de pago oficial.
    </div>

</div>
</body>
</html>
