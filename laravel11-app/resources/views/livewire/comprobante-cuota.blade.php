<div>
<style>
    .inv-wrap { background:#f3f4f6; min-height:100vh; padding:32px 16px; }
    .inv-actions { max-width:760px; margin:0 auto 16px; display:flex; gap:10px; }
    .inv-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 22px; border-radius:5px; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; border:none; }
    .inv-btn-primary { background:#1a6b2e; color:#fff; }
    .inv-btn-primary:hover { background:#145724; }
    .inv-btn-secondary { background:#fff; color:#1a6b2e; border:2px solid #1a6b2e; }
    .inv-btn-secondary:hover { background:#f0faf3; }
    .inv-page { background:#fff; max-width:760px; margin:0 auto; padding:48px 52px; border-radius:6px; box-shadow:0 2px 16px rgba(0,0,0,.10); }
    .inv-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
    .inv-logo img { max-width:180px; max-height:90px; }
    .inv-title-block { text-align:right; }
    .inv-title { font-size:24px; font-weight:700; color:#1a6b2e; text-transform:uppercase; letter-spacing:1px; }
    .inv-meta { font-size:13px; color:#555; margin-top:4px; }
    .inv-divider { border:none; border-top:2.5px solid #1a6b2e; margin-bottom:24px; }
    .inv-billing { display:flex; justify-content:space-between; margin-bottom:28px; }
    .inv-label { font-size:10px; font-weight:700; text-transform:uppercase; color:#1a6b2e; letter-spacing:1px; margin-bottom:6px; }
    .inv-billing-to p { font-size:13px; color:#333; line-height:1.65; }
    .inv-billing-to .inv-name { font-weight:700; font-size:14px; }
    .inv-billing-org { text-align:right; }
    .inv-billing-org p { font-size:13px; color:#333; line-height:1.65; }
    .inv-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    .inv-table thead tr { background:#1a6b2e; }
    .inv-table thead th { color:#fff; padding:10px 12px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.5px; font-weight:600; }
    .inv-table thead th.right { text-align:right; }
    .inv-table tbody tr { border-bottom:1px solid #e8e8e8; }
    .inv-table tbody tr:nth-child(even) { background:#f8fdf9; }
    .inv-table tbody td { padding:10px 12px; font-size:13px; color:#333; }
    .inv-table tbody td.right { text-align:right; }
    .inv-total { text-align:right; margin-bottom:28px; }
    .inv-total table { display:inline-table; min-width:220px; border-collapse:collapse; }
    .inv-total-row td { padding:7px 8px; font-size:15px; }
    .inv-total-row .tl { font-weight:700; color:#1a6b2e; text-align:left; border-top:2px solid #1a6b2e; }
    .inv-total-row .tr { font-weight:700; color:#1a6b2e; text-align:right; border-top:2px solid #1a6b2e; }
    .inv-signature { border-top:1px solid #ddd; padding-top:20px; margin-bottom:20px; }
    .inv-sig-line { display:inline-block; border-bottom:1px solid #555; width:220px; margin-top:32px; margin-bottom:4px; }
    .inv-sig-name { font-size:12px; color:#444; }
    .inv-sig-role { font-size:11px; color:#888; }
    .inv-footer { text-align:center; font-size:11px; color:#888; border-top:1px solid #e0e0e0; padding-top:14px; }
    @media print { .inv-wrap { background:#fff; padding:0; } .inv-actions { display:none; } .inv-page { box-shadow:none; max-width:100%; } }
</style>

<div class="inv-wrap">
    <div class="inv-actions">
        <a class="inv-btn inv-btn-primary" href="{{ route('descargar-comprobante', $documento->id ?? 0) }}">
            ↓ Descargar PDF
        </a>
        <a class="inv-btn inv-btn-secondary" href="javascript:window.print()">
            ⎙ Imprimir
        </a>
    </div>

    <div class="inv-page">
        {{-- Header --}}
        <div class="inv-header">
            <div class="inv-logo">
                <img src="{{ $logoBase64 ?? asset('img/logo.png') }}" alt="Florida Runners">
            </div>
            <div class="inv-title-block">
                <div class="inv-title">Comprobante de Pago</div>
                <div class="inv-meta">N° {{ $documento->Nombre ?? 'S/N' }}</div>
                <div class="inv-meta">Fecha: {{ \Carbon\Carbon::parse($cuota->FechaPago)->format('d/m/Y') }}</div>
            </div>
        </div>

        <hr class="inv-divider">

        {{-- Billing --}}
        <div class="inv-billing">
            <div class="inv-billing-to">
                <div class="inv-label">Emitido a</div>
                <p class="inv-name">{{ $user->name ?? '' }}</p>
                @if($user->persona->Direccion ?? false)
                    <p>{{ $user->persona->Direccion }}</p>
                @endif
                @if($user->persona->Comuna ?? false)
                    <p>{{ $user->persona->Comuna }}</p>
                @endif
                @if($user->email ?? false)
                    <p>{{ $user->email }}</p>
                @endif
            </div>
            <div class="inv-billing-org">
                <div class="inv-label">Emitido por</div>
                <p style="font-weight:700;">Florida Runners</p>
                <p>Departamento de Tesorería</p>
            </div>
        </div>

        {{-- Items --}}
        <table class="inv-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Período</th>
                    <th>Fecha de Pago</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $item)
                <tr>
                    <td>{{ $item->TipoCuota ?? 'Cuota' }}</td>
                    <td>{{ $item->FechaPeriodo ? \Carbon\Carbon::parse($item->FechaPeriodo)->translatedFormat('F Y') : '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->FechaPago)->format('d/m/Y') }}</td>
                    <td class="right">$ {{ number_format($item->Recaudado ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="inv-total">
            <table>
                <tr class="inv-total-row">
                    <td class="tl">Total pagado</td>
                    <td class="tr">$ {{ number_format($records->sum('Recaudado'), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Signature --}}
        @if($aprobador)
        <div class="inv-signature">
            <div class="inv-label">Autorizado por</div>
            <div class="inv-sig-line"></div>
            <div class="inv-sig-name">{{ $aprobador->name ?? '' }}</div>
            <div class="inv-sig-role">Tesorero · Florida Runners</div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="inv-footer">
            Florida Runners &mdash; Este comprobante es válido como recibo de pago oficial.
        </div>
    </div>
</div>
</div>
