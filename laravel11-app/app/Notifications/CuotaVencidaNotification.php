<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class CuotaVencidaNotification extends Notification
{
    /** @param Collection $items Cada item: ['label' => string, 'monto' => int] */
    public function __construct(private Collection $items, private int $totalPendiente)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mensaje = (new MailMessage())
            ->subject('Tienes cuotas pendientes en '.config('app.name'))
            ->greeting('Hola '.$notifiable->name.',')
            ->line('Detectamos que tienes cuotas vencidas sin pagar:');

        foreach ($this->items as $item) {
            $mensaje->line('• '.$item['label'].' — $'.number_format($item['monto'], 0, ',', '.'));
        }

        return $mensaje
            ->line('Total pendiente: $'.number_format($this->totalPendiente, 0, ',', '.'))
            ->action('Ir a Mis Cuotas', url('/v2/mis-cuotas'))
            ->line('Si ya realizaste el pago, ingresa a la plataforma para adjuntar tu comprobante.');
    }
}
