<?php

namespace App\Notifications;

use App\Models\DocumentAssignment;
use Illuminate\Notifications\Notification;

class AssignmentReceivedNotification extends Notification
{
    /**
     * On passe l'assignation au constructeur pour avoir
     * accès à toutes ses données dans le message
     */
    public function __construct(
        public DocumentAssignment $assignment
    ) {}

    /**
     * Canal de livraison : 'database' = stocké en table notifications
     * On pourrait ajouter 'mail' ici pour envoyer aussi un email
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Contenu stocké dans la colonne `data` (JSON) de la table notifications
     * C'est ce que Vue.js recevra et affichera
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'assignment_id'    => $this->assignment->id,
            'depot_request_id' => $this->assignment->depot_request_id,
            'reference_title'  => $this->assignment->depotRequest->reference->title ?? 'Document sans titre',
            'assigned_by_name' => $this->assignment->assignedBy->name ?? 'Administrateur',
            'instructions'     => $this->assignment->instructions,
            'message'          => 'Une nouvelle référence vous a été assignée pour vérification.',
            'assigned_at'      => $this->assignment->created_at->format('d/m/Y à H:i'),
        ];
    }
}