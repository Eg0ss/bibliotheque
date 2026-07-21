<?php

namespace App\Events;

use App\Models\DocumentAssignment;
use Illuminate\Foundation\Events\Dispatchable;

class AssignmentCreated
{
    // Dispatchable = donne à cet event la méthode statique ::dispatch()
    use Dispatchable;

    /**
     * On stocke l'assignation dans l'event
     * Le Listener y aura accès via $event->assignment
     */
    public function __construct(
        public DocumentAssignment $assignment
    ) {}
}