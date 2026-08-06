<?php

namespace App\Jobs;

use App\Eloquent\MentionEloquent;
use App\Enum\EnumTelegramEvents;
use App\Enum\EnumTypeMedia;
use App\Models\Car;
use App\Models\User;
use App\Notifications\MentionNotification;
use App\Notifications\Support\TelegramRecipients;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SandMention implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    /**
     * @param  UploadedFile|UploadedFile[]  $request
     */
    public function __construct(
        public readonly Car $car,
        public readonly string $path,
        public readonly ?string $description,
        public readonly User $user,
        public readonly Carbon $time
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $isEmptyFile = $this->path == 'nane';
        if (! $isEmptyFile) {
            $storagePath = storage_path('app/private/'.$this->path);
            $file = new UploadedFile($storagePath, basename($storagePath), mime_content_type($storagePath), null, true);
        }
        $mention = MentionEloquent::create($this->car, $this->user, $this->description, $file ?? null);

        if (! $isEmptyFile) {
            Storage::delete($this->path);
        }

        $imageUrl = $mention->getFirstMedia(EnumTypeMedia::PHOTO_MENTION->value)?->getPath();

        Notification::send(
            TelegramRecipients::routes(EnumTelegramEvents::FA_FA->getIds()),
            new MentionNotification($this->user, $this->car, $this->description, $this->time, $imageUrl)
        );
    }
}
