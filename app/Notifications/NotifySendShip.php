<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifySendShip extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $shipment;
    public $type_shipment;
    public $user;
    public $images;

    public function __construct($shipment,$type_shipment,$user,$images)
    {
        $this->shipment = $shipment;
        $this->type_shipment = $type_shipment;
        $this->user = $user;
        $this->images = $images;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $notification = (new MailMessage)
                    ->subject('Nuevo envio recibido')
                    ->line('Se ha registrado un nuevo envio')
                    ->line('Nombre del chofer: '.$this->user->first_name.' '.$this->user->last_name)
                    ->line('Tipo de envio: '.$this->type_shipment->name);

        if ($this->shipment->latitude != null && $this->shipment->longitude != null) {
            $notification->line('Coordenadas')

                ->line(new \Illuminate\Support\HtmlString('<a href="https://www.google.com/maps/search/?api=1&query='.$this->shipment->latitude.','.$this->shipment->longitude.'&zoom=20">Localización:</a>'))
                ->line('Latitud: '.$this->shipment->latitude)
                ->line('Longitud: '.$this->shipment->longitude);
        //https://www.google.com/maps/search/?api=1&query=19.10711285128303,-98.27150480793455&zoom=20
        }
        
        foreach ($this->images as $image) {
            $notification->attach(storage_path('app/'.$image->path));
        }

        return $notification->greeting('Nuevo envio recibido')
                            ->salutation('Gracias por usar nuestra aplicación');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
