<?php

namespace App\Events\Trigger\Registration;

use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Models\Registration;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TriggerRegistrationCreatedEvent
{

    use Dispatchable, SerializesModels;


    public function __construct(readonly Registration $model)
    {

    }



}
