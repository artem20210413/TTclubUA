<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Enum\EnumTelegramEvents;
use App\Enum\EnumTelegramLoggerDirection;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\FileUpload\InputFile;

class TelegramBot
{

    protected Api $telegram;

    private array $telegramIds = [];

    /**
     * @throws TelegramSDKException
     */
    public function __construct(readonly EnumTelegramEvents $enumTelegramEvents)
    {
        $this->telegram = new Api(config('services.telegram.token'));
    }


    public function sendMessage(?string $message, array $buttons = [])
    {
        if (!$message) return;
        $res = [];
        foreach ($this->enumTelegramEvents->getIds(chantIds: $this->telegramIds) as $chatId) {
            try {
                $params = [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ];

                if (!empty($buttons)) {
                    $inlineKeyboard = [];
                    foreach ($buttons as $text => $url) {
                        $inlineKeyboard[] = [
                            [
                                'text' => $text,
                                'url' => $url
                            ]
                        ];
                    }
                    $params['reply_markup'] = json_encode([
                        'inline_keyboard' => $inlineKeyboard
                    ]);
                }

                $res[] = $this->telegram->sendMessage($params);
                TelegramLoggerEloquent::createOut($params);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            return $res;
        }
    }

    public function sendPhotoAndDescription(string $imgPath, ?string $description)
    {
        foreach ($this->enumTelegramEvents->getIds() as $chatId) {
            try {
                $params = [
                    'chat_id' => $chatId,
                    'photo' => fopen($imgPath, 'r'),
//                'photo' => fopen("https://tt.tishchenko.kiev.ua/storage/9/profile_image.webp", 'r'),
//                'photo' => fopen($file->getRealPath(), 'r'),
                    'caption' => $description ?? '',
                    'parse_mode' => 'HTML',
                ];

                $this->telegram->sendPhoto($params);
                TelegramLoggerEloquent::createOut($params);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function sendDocumentWithCaption(string $filePath, string $description = null): void
    {
        foreach ($this->enumTelegramEvents->getIds() as $chatId) {
            try {
                $params = [
                    'chat_id' => $chatId,
                    'document' => fopen($filePath, 'r'),
                    'caption' => $description ?? '',
                    'parse_mode' => 'HTML',
                ];

                $this->telegram->sendDocument($params);
                TelegramLoggerEloquent::createOut($params);
            } catch (\Exception $e) {
                Log::error('Telegram sendDocument error: ' . $e->getMessage());
            }
        }
    }

    public function sendPhotosWithDescription(array $imgPaths, ?string $description = null): void
    {
        foreach ($this->enumTelegramEvents->getIds() as $chatId) {
            try {
                $media = [];

                foreach ($imgPaths as $index => $path) {
                    $item = [
                        'type' => 'photo',
                        'media' => $path,//'https://upload.wikimedia.org/wikipedia/commons/9/99/Sample_User_Icon.png', //
                    ];

                    if ($index === 0 && $description) {
                        $item['caption'] = htmlspecialchars($description);
                        $item['parse_mode'] = 'HTML';
                    }

                    $media[] = $item;
                }
                $params = [
                    'chat_id' => $chatId,
                    'media' => json_encode($media),
                ];

                $this->telegram->sendMediaGroup($params);
                TelegramLoggerEloquent::createOut($params);
            } catch (\Exception $e) {
                Log::error("Telegram send error: " . $e->getMessage());
            }
        }
    }

    /**
     * Sends a group of photos from local file paths or URLs.
     */
    public function sendPhotosPathOrUrlWithDescription(array $imgPathsOrUrls, ?string $description = null): void
    {
        foreach ($this->enumTelegramEvents->getIds() as $chatId) {
            try {
                $media = [];
                $params = [
                    'chat_id' => $chatId,
                ];

                foreach ($imgPathsOrUrls as $index => $path) {
                    $attachmentName = 'photo' . $index;
                    $mediaItem = [
                        'type' => 'photo',
                    ];

                    if (filter_var($path, FILTER_VALIDATE_URL)) {
                        $mediaItem['media'] = $path;
                    } else {
                        $mediaItem['media'] = 'attach://' . $attachmentName;
                        $params[$attachmentName] = InputFile::create($path);
                    }

                    if ($index === 0 && $description) {
                        $mediaItem['caption'] = $description;
                        $mediaItem['parse_mode'] = 'HTML';
                    }

                    $media[] = $mediaItem;
                }

                $params['media'] = json_encode($media);

                $this->telegram->sendMediaGroup($params);
                TelegramLoggerEloquent::createOut($params);
            } catch (\Exception $e) {
                Log::error("Telegram send error: " . $e->getMessage());
            }
        }
    }


    public function test()
    {
        $response = $this->telegram->getMe();
        dd($response);
    }

    public function getTelegram(): Api
    {
        return $this->telegram;
    }

    public function setTelegramIds(...$telegramIds): void
    {
        $this->telegramIds = $telegramIds;
    }


}
