<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ExternalCar
 *
 * @property int $id
 * @property int $external_id ID оголошення на AUTO.RIA
 * @property string|null $plate_number Номерний знак
 * @property string|null $vin VIN-код автомобіля
 * @property string|null $title Заголовок оголошення
 * @property float|null $price_usd Ціна в доларах
 * @property string|null $city_name Назва городу
 * @property string|null $mark_name Марка (Audi)
 * @property string|null $model_name Модель (TT)
 * @property string|null $sub_category Тип кузова (Купе, і т.д.)
 * @property string|null $color_hex Код кольору в HEX (#ffffff)
 * @property int|null $year Рік випуску
 * @property bool $is_active Чи активне оголошення
 * @property bool $is_sold Чи продано авто
 * @property int|null $user_id Зв'язок з користувачем системи
 * @property array|null $raw_data Весь інший JSON з RIA
 * @property \Carbon\Carbon|null $created_at Дата додавання в нашу БД
 * @property \Carbon\Carbon|null $updated_at Дата оновлення в нашій БД
 * * @property-read \App\Models\User|null $user
 */
class ExternalCar extends Model
{

    /**
     * Атрибути, які можна масово призначати (Mass Assignment).
     */
    protected $fillable = [
        'external_id',
        'plate_number',
        'vin',
        'title',
        'price_usd',
        'city_name',
        'mark_name',
        'model_name',
        'sub_category',
        'color_hex',
        'year',
        'is_active',
        'is_sold',
        'user_id',
        'raw_data',
        'synced_at',
    ];

    /**
     * Перетворення типів атрибутів (Casting).
     */
    protected $casts = [
        'external_id' => 'integer',
        'price_usd' => 'decimal:2',
        'year' => 'integer',
        'is_active' => 'boolean',
        'is_sold' => 'boolean',
        'raw_data' => 'array', // Автоматично перетворює JSON у масив PHP і навпаки
        'synced_at' => 'datetime'
    ];

    /**
     * Зв'язок з користувачем вашої системи.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Аксесор для зручного отримання головного фото з JSON (приклад).
     */
    public function getMainPhotoAttribute(): ?string
    {
        return $this->raw_data['photoData']['seoLinkM'] ?? null;
    }

    /**
     * Отримує масив посилань на всі фото автомобіля у високій якості (формат f - full).
     * * @return array
     */
    public function getAllPhotos(): array
    {
        $data = $this->raw_data;

        // Перевіряємо, чи є взагалі дані про фото
        if (!isset($data['photoData']['all']) || !is_array($data['photoData']['all'])) {
            return [];
        }

        // Витягуємо назви марки та моделі для формування URL (якщо їх немає, беремо з полів моделі)
        $mark = $data['markNameEng'] ?? $this->mark_name ?? 'auto';
        $model = $data['modelNameEng'] ?? $this->model_name ?? 'car';

        // Очищуємо назви (замінюємо пробіли на дефіси, якщо раптом вони там є)
        $mark = strtolower(str_replace(' ', '-', $mark));
        $model = strtolower(str_replace(' ', '-', $model));

        $photos = [];
        foreach ($data['photoData']['all'] as $photoId) {
            // Формат 'f' зазвичай означає Full Size (велике фото)
            // Можна також використовувати 'm' (medium) або 'b' (big)
            $photos[] = [
                'id' => $photoId,
                'url' => "https://cdn0.riastatic.com/photosnew/auto/photo/{$mark}_{$model}__{$photoId}b.jpg",
            ];;
        }

        return $photos;
    }

    /**
     * Скоуп (Scope) для отримання лише активних оголошень, які не продані.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_sold', false);
    }

    public function scopeStale(Builder $query, int $days = 3): Builder
    {
        return $query->where('synced_at', '<', now()->subDays($days));
    }
}
