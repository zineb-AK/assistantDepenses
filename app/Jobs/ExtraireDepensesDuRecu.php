<?php

namespace App\Jobs;

use App\Models\Recu;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtraireDepensesDuRecu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Recu $recu;

    public function __construct(Recu $recu)
    {
        $this->recu = $recu;
    }

    public function handle(): void
    {
        // AI extraction will be implemented in a separate change.
        // This stub marks the receipt as processed without extracting anything.
        $this->recu->update(['statut' => 'processed']);
    }
}
