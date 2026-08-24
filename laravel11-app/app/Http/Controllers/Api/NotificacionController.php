<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionController extends Controller
{
    private function serialize(DatabaseNotification $n): array
    {
        $data = $n->data;

        return [
            'id' => $n->id,
            'titulo' => $data['title'] ?? null,
            'cuerpo' => $data['body'] ?? null,
            'color' => $data['iconColor'] ?? $data['color'] ?? null,
            'acciones' => collect($data['actions'] ?? [])
                ->map(fn ($a) => ['label' => $a['label'] ?? null, 'url' => $a['url'] ?? null])
                ->filter(fn ($a) => $a['url'])
                ->values(),
            'leida' => (bool) $n->read_at,
            'creadaEn' => $n->created_at,
        ];
    }

    public function index(Request $request)
    {
        $notificaciones = $request->user()->notifications()
            ->orderByDesc('created_at')
            ->paginate($request->integer('porPagina', 20));

        return response()->json([
            'data' => collect($notificaciones->items())->map(fn ($n) => $this->serialize($n)),
            'noLeidas' => $request->user()->unreadNotifications()->count(),
            'meta' => [
                'current_page' => $notificaciones->currentPage(),
                'last_page' => $notificaciones->lastPage(),
                'total' => $notificaciones->total(),
            ],
        ]);
    }

    public function contador(Request $request)
    {
        return response()->json(['noLeidas' => $request->user()->unreadNotifications()->count()]);
    }

    public function marcarLeida(Request $request, string $id)
    {
        $notificacion = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notificacion->markAsRead();

        return response()->noContent();
    }

    public function marcarTodasLeidas(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    }
}
