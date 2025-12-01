<?php

namespace App\Services\Telegram\Dto;

class TelegramImportDto
{
//{
//"update_id": 983724378,
//"message": {
//"message_id": 619,
//"from": {
//"id": 616322991,
//"is_bot": false,
//"first_name": "Artem",
//"last_name": "Tishchenko",
//"username": "TishchenkoArt",
//"language_code": "ru"
//},
//"chat": {
//    "id": 616322991,
//                "first_name": "Artem",
//                "last_name": "Tishchenko",
//                "username": "TishchenkoArt",
//                "type": "private"
//            },
//            "date": 1764596647,
//            "text": "👍👍",
//            "photo": [
//                {
//                    "file_id": "AgACAgIAAxkBAAICa2ktm4lcu5UoO3nsUIMQ4zm2DI2HAAIrD2sbYApwSZl7cwsml-aoAQADAgADcwADNgQ",
//                    "file_unique_id": "AQADKw9rG2AKcEl4",
//                    "file_size": 1010,
//                    "width": 90,
//                    "height": 51
//                },
//                {
//                    "file_id": "AgACAgIAAxkBAAICa2ktm4lcu5UoO3nsUIMQ4zm2DI2HAAIrD2sbYApwSZl7cwsml-aoAQADAgADbQADNgQ",
//                    "file_unique_id": "AQADKw9rG2AKcEly",
//                    "file_size": 16265,
//                    "width": 320,
//                    "height": 180
//                }
//            ]
//        }
//    }

    private TelegramMessageDto $message;

    public function __construct(readonly array $json)
    {
        $this->message = new TelegramMessageDto($json['message'] ?? []);
    }

    public function getUpdateId(): ?int
    {
        return $this->json['update_id'] ?? null;
    }

    public function getMessage(): TelegramMessageDto
    {
        return $this->message;
    }
}
