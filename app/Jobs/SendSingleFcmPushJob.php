<?php

namespace App\Jobs;

use App\Enum\NotificationsPushType;
use App\Models\FcmToken;
use App\Services\Fcm\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSingleFcmPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Спроби при помилці (наприклад, мережевий збій)

    public function __construct(
        protected FcmToken $fcmToken,
        protected string $title,
        protected string $body,
        protected NotificationsPushType $type,
        protected array $data = []
    ) {}

    public function handle(): void
    {
        // Викликаємо сервіс для одного конкретного токена
        FcmService::sendPush(
            $this->fcmToken,
            $this->title,
            $this->body,
            $this->type,
            $this->data
        );
    }
}
