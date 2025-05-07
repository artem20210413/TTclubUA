<?php

namespace App\Jobs;

use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Models\Car;
use App\Models\Registration;
use App\Services\Telegram\Sand\RegistrationSandToTo;
use App\Services\Telegram\TelegramBot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;

class SandRegistrationToTg implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    /**
     * @param Car $car
     * @param UploadedFile|UploadedFile[] $request
     */
    public function __construct(
        readonly Registration $registration,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        new RegistrationSandToTo($this->registration);
    }


}
