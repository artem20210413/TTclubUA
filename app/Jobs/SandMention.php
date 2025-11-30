<?php

namespace App\Jobs;

use App\Eloquent\MentionEloquent;
use App\Enum\EnumTelegramEvents;
use App\Enum\EnumTypeMedia;
use App\Http\Requests\MentionRequest;
use App\Models\Car;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SandMention implements ShouldQueue
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
        readonly Car     $car,
        readonly string  $path,
        readonly ?string $description,
        readonly User    $user,
        readonly Carbon  $time
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $isEmptyFile = $this->path == 'nane';
        if (!$isEmptyFile) {
            $storagePath = storage_path('app/private/' . $this->path);
            $file = new UploadedFile($storagePath, basename($storagePath), mime_content_type($storagePath), null, true);
        }
        $mention = MentionEloquent::create($this->car, $this->user, $this->description, $file ?? null);

        if (!$isEmptyFile)
            Storage::delete($this->path);

        $imageUrl = $mention->getFirstMedia(EnumTypeMedia::PHOTO_MENTION->value)?->getPath();
        $text = TelegramBotHelpers::generationTextMention($this->user, $this->car, $this->description, $this->time);
        $bot = new TelegramBot(EnumTelegramEvents::FA_FA);

        if ($imageUrl) {
            $bot->sendPhotoAndDescription($imageUrl, $text);
        } else {
            $bot->sendMessage($text);
        }
    }
}
