<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Models\User;
use App\Notifications\AssignmentReceivedNotification;

class SendAssignmentNotification
{
    /**
     * Laravel 12 : on déclare ici à quel event ce Listener est branché.
     * Plus besoin de EventServiceProvider — Laravel le détecte tout seul.
     */
    public function handle(AssignmentCreated $event): void
    {
        $gestionnaire = User::find($event->assignment->assigned_to);

        if (!$gestionnaire) {
            return;
        }

        $gestionnaire->notify(
            new AssignmentReceivedNotification($event->assignment)
        );
    }
}