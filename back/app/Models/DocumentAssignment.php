<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAssignment extends Model
{
    protected $fillable = [
        'depot_request_id',
        'assigned_by',
        'assigned_at',
        'due_date',
        'processing_started_at',
        'processed_at',
        'assigned_to',
        'instructions',
    ];


    protected $casts = [

        // Date + heure
        'assigned_at' => 'datetime',

        // Date uniquement
        'due_date' => 'date',

        'processing_started_at'  => 'datetime',  // début du traitement réel

        'processed_at'           => 'datetime',  // fin du traitement réel

    ];

    // La demande concernée
    public function depotRequest()
    {
        return $this->belongsTo(DepotRequest::class);
    }

    // L'admin qui a assigné
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Le gestionnaire assigné
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getProcessingDuration(): ?array
    {
        // Garde-fou : si l'une des deux dates est absente, on ne peut pas calculer.
        // Cas possible :
        //   - processing_started_at est null → gestionnaire n'a jamais ouvert le document
        //   - processed_at est null → gestionnaire n'a pas encore pris de décision
        if (!$this->processing_started_at || !$this->processed_at) {
            return null;
        }

        // diffInSeconds() de Carbon calcule la différence absolue en secondes
        // entre les deux objets Carbon.
        // On part toujours de processing_started_at vers processed_at
        // (processed_at est toujours APRÈS processing_started_at).
        $totalSeconds = (int) $this->processing_started_at
                                   ->diffInSeconds($this->processed_at);

        // Calculs dérivés à partir du total en secondes.
        // On utilise la division entière (intdiv) pour obtenir
        // des valeurs entières propres.
        $minutes = intdiv($totalSeconds, 60);          // 1 minute = 60 secondes
        $hours   = intdiv($totalSeconds, 3600);        // 1 heure  = 3600 secondes
        $days    = intdiv($totalSeconds, 86400);       // 1 jour   = 86400 secondes

        // Secondes restantes après soustraction des minutes complètes
        // Exemple : 125 secondes → 2 minutes et 5 secondes restantes
        $remainingSeconds = $totalSeconds % 60;

        // Minutes restantes après soustraction des heures complètes
        // Exemple : 135 minutes → 2 heures et 15 minutes restantes
        $remainingMinutes = $minutes % 60;

        // Heures restantes après soustraction des jours complets
        // Exemple : 50 heures → 2 jours et 2 heures restantes
        $remainingHours = $hours % 24;

        // Construction d'un libellé lisible pour l'affichage dans l'interface.
        // On affiche la plus grande unité significative accompagnée
        // de la suivante pour plus de précision.
        if ($days >= 1) {
            // Ex : "2j 3h"
            $label = "{$days}j {$remainingHours}h";
        } elseif ($hours >= 1) {
            // Ex : "3h 15min"
            $label = "{$hours}h {$remainingMinutes}min";
        } elseif ($minutes >= 1) {
            // Ex : "15min 30s"
            $label = "{$minutes}min {$remainingSeconds}s";
        } else {
            // Ex : "45s"
            $label = "{$totalSeconds}s";
        }

        return [
            // Valeur brute en secondes — utile pour les statistiques et tris
            'total_seconds'     => $totalSeconds,

            // Valeurs converties — utiles pour affichage ou export
            'total_minutes'     => $minutes,
            'total_hours'       => $hours,
            'total_days'        => $days,

            // Valeurs "restantes" pour un affichage horloge (ex: 2h 15min 30s)
            'remaining_seconds' => $remainingSeconds,
            'remaining_minutes' => $remainingMinutes,
            'remaining_hours'   => $remainingHours,

            // Libellé prêt à l'emploi pour l'interface
            'label'             => $label,
        ];
    }
}
