<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\Documentos;
use App\Services\CuotaMensualService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Transbank\Webpay\WebpayPlus\Transaction;

/**
 * Pago real de cuotas vía Webpay Plus (Transbank). En ambiente de integración
 * usa las credenciales públicas de prueba de Transbank (ver config/webpay.php);
 * para producción basta con cambiar WEBPAY_* en el .env con las credenciales
 * reales que entrega Transbank al aprobar la afiliación del comercio.
 */
class WebpayController extends Controller
{
    public function __construct(private CuotaMensualService $cuotaMensualService)
    {
    }

    private function transaction(): Transaction
    {
        $apiKey = config('webpay.api_key');
        $commerceCode = config('webpay.commerce_code');

        return config('webpay.environment') === 'production'
            ? Transaction::buildForProduction($apiKey, $commerceCode)
            : Transaction::buildForIntegration($apiKey, $commerceCode);
    }

    public function iniciar(Request $request)
    {
        $data = $request->validate([
            'cuotaId' => ['nullable', 'integer'],
            'periodoKey' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        if (! empty($data['periodoKey'])) {
            $persona = $user->persona;
            abort_unless($persona, 422, 'Tu usuario no tiene un perfil de socio asociado.');
            $cuota = $this->cuotaMensualService->obtenerOcrearCuota($persona, Carbon::createFromFormat('Y-m', $data['periodoKey']));
        } else {
            $cuota = Cuota::findOrFail($data['cuotaId']);
        }

        abort_unless($cuota->idUser === $user->id, 403);
        abort_unless($cuota->Estado == 1 && $cuota->Pendiente > 0, 422, 'Esta cuota no admite pago en su estado actual.');

        $buyOrder = 'C'.$cuota->id.'-'.substr((string) time(), -8);
        $returnUrl = route('webpay.retorno');

        $respuesta = $this->transaction()->create($buyOrder, (string) $user->id, (int) $cuota->Pendiente, $returnUrl);

        // El token vive ~5-10 min en Transbank; guardamos la referencia a la cuota
        // para poder retomarla cuando el navegador vuelva con el token confirmado.
        Cache::put('webpay-token:'.$respuesta->getToken(), $cuota->id, now()->addMinutes(30));

        return response()->json(['url' => $respuesta->getUrl(), 'token' => $respuesta->getToken()]);
    }

    /** Transbank redirige aquí al terminar el pago (aprobado, rechazado o cancelado). */
    public function retorno(Request $request)
    {
        $tokenWs = $request->input('token_ws');
        $tokenAnulado = $request->input('TBK_TOKEN');

        if (! $tokenWs && $tokenAnulado) {
            return redirect(url('/v2/mis-cuotas?webpay=cancelado'));
        }

        if (! $tokenWs) {
            return redirect(url('/v2/mis-cuotas?webpay=error'));
        }

        $idCuota = Cache::pull('webpay-token:'.$tokenWs);
        $cuota = $idCuota ? Cuota::find($idCuota) : null;

        if (! $cuota) {
            return redirect(url('/v2/mis-cuotas?webpay=error'));
        }

        $resultado = $this->transaction()->commit($tokenWs);

        if (! $resultado->isApproved()) {
            return redirect(url('/v2/mis-cuotas?webpay=rechazado'));
        }

        $documento = Documentos::create([
            'TipoDocumento' => 11,
            'Nombre' => '',
            'NroDocumento' => $resultado->getAuthorizationCode(),
            'Descripcion' => 'Pago con Webpay Plus (Transbank) — tarjeta terminada en '.$resultado->getCardNumber(),
        ]);
        $documento->update(['Nombre' => str_pad((string) $documento->id, 6, '0', STR_PAD_LEFT)]);

        $cuota->update([
            'Pendiente' => 0,
            'Recaudado' => $cuota->Monto,
            'FechaPago' => now()->format('Y-m-d'),
            'Estado' => 2,
            'idDocumento' => $documento->id,
        ]);

        return redirect(url('/v2/mis-cuotas?webpay=exito'));
    }
}
