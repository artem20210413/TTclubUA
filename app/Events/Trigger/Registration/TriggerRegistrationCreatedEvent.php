<?php

namespace App\Events\Trigger\Registration;

use App\Models\Registration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TriggerRegistrationCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Registration $model) {}

}
