<div>
    <div id="divComprobante">
        <div class="bg-white rounded-lg shadow-lg px-8 py-10 md:w-3/4 sm:w-auto mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <img class="h-28 w-32 mr-2" src="/storage/logoBomba.png"
                         alt="Logo"/>
                </div>
                <div class="text-gray-700">
                    <div class="font-bold text-xl mb-2">CUERPO DE BOMBEROS DE ÑUÑOA</div>
                    <div class="text-sm">SÉPTIMA COMPAÑIA</div>
                    <div class="text-sm">"BOMBA MACUL"</div>
                </div>
                <div class="text-gray-700">
                    <div class="font-bold text-xl mb-2">RECIBO DE DINERO</div>
                    <div class="text-sm">Fecha: {{\Carbon\Carbon::parse($cuota->FechaPago)->format('d/m/Y') ?? ''}}</div>
                    <div class="text-sm">Comprobante #: {{$documento->Nombre ?? ''}}</div>
                </div>
            </div>
            <div class="border-b-2 border-gray-300 pb-8 mb-8">
                <h2 class="text-2xl font-bold mb-4">Recibo del señor(a):</h2>
                <div class="text-gray-700 mb-2">{{$user->name ?? ''}}</div>
                <div class="text-gray-700 mb-2">{{$user->persona->Direccion ?? ''}}</div>
                <div class="text-gray-700 mb-2">{{$user->persona->Comuna ?? ''}}</div>
                <div class="text-gray-700">{{$user->email ?? ''}}</div>
            </div>
            <table class="w-full text-left mb-8">
                <thead>
                <tr>
                    <th class="text-gray-700 font-bold uppercase py-2">Concepto</th>
                    <th class="text-gray-700 font-bold uppercase py-2">Fecha de Pago</th>
                    <th class="text-gray-700 font-bold uppercase py-2">Monto</th>
                </tr>
                </thead>
                <tbody>
                @foreach($records as $cuotaItem)
                <tr>
                    <td class="py-4 text-gray-700">{{$cuotaItem->TipoCuota ?? ''}}</td>
                    <td class="py-4 text-gray-700">{{\Carbon\Carbon::parse($cuotaItem->FechaPago)->format('d/m/Y') ?? ''}}</td>
                    <td class="py-4 text-gray-700">${{$cuotaItem->Recaudado ?? ''}}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            {{--<div class="flex justify-end mb-8">
                <div class="text-gray-700 mr-2">Subtotal:</div>
                <div class="text-gray-700">$425.00</div>
            </div>
            <div class="text-right mb-8">
                <div class="text-gray-700 mr-2">Impuesto:</div>
                <div class="text-gray-700">$25.50</div>

            </div>--}}
            <div class="flex justify-end mb-8">
                <div class="text-gray-700 mr-2">Total:</div>
                <div class="text-gray-700 font-bold text-xl">${{$records->sum('Recaudado') ?? ''}}</div>
            </div>

            @if($aprobador)
            <div class="border-t2 border-gray-100 mb-8 md:w-1/2 sm:w-3/4">
                <div class="text-gray-700 mb-2">Firma del tesorero: ___________________________</div>
                <div class="text-gray-700 mb-2 right text-center">{{$aprobador->name ?? ''}}</div>
            </div>
            @endif

            {{--<div class="border-t-2 border-gray-300 pt-8 mb-8">
                <div class="text-gray-700 mb-2">Pago por concepto de cuota mensual, o cuota extraordinaria</div>
                <div class="text-gray-700 mb-2">Cualquier duda, comunicarse con el tesorero</div>
                <div class="text-gray-700">

                    © bomba macul e-mail: correo@bombamacul.cl- fono: +56 22 2839660

                </div>
            </div>--}}
        </div>
    </div>

</div>
<script type="text/javascript">
    window.print();
</script>

