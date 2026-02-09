<?php

use App\Http\Controllers\Api\DrawController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\PrizeController;
use App\Http\Controllers\AppConfigController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Car\CarController;
use App\Http\Controllers\Car\MentionController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Partner\PartnerController;
use App\Http\Controllers\Partner\PromotionController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/login/tg/send-code', [AuthController::class, 'sendCode'])->middleware('params.throttle:15');
Route::post('/login/tg/verify', [AuthController::class, 'verifyCode'])->middleware('throttle:5,2');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::delete('/delete-account', [UserController::class, 'deleteAccount'])->middleware(['auth:sanctum']);

Route::get('/app-config/{platform}', [AppConfigController::class, 'check'])->middleware(['auth:sanctum']);

Route::get('/homepage-data', [HomePageController::class, 'homepageData'])->middleware(['auth:sanctum']);
Route::get('/system/user-stats', [SystemController::class, 'systemUserStats'])->middleware(['auth:sanctum', 'role:admin']);

Route::get('/user/export', [UserController::class, 'export'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/user/profile-picture', [MediaController::class, 'updateProfilePicture'])->middleware('auth:sanctum');
Route::post('/user/{user}/profile-picture', [MediaController::class, 'updateProfilePictureById'])->middleware('auth:sanctum');
Route::get('/user/search', [UserController::class, 'search'])->middleware('auth:sanctum');
Route::delete('/user/profile-picture', [MediaController::class, 'deleteProfilePicture'])->middleware('auth:sanctum');
Route::get('/user/my-cars', [UserController::class, 'myCars'])->middleware('auth:sanctum');
Route::get('/user/all', [UserController::class, 'all'])->middleware('auth:sanctum');
Route::post('/user', [UserController::class, 'update'])->middleware(['auth:sanctum']);
Route::get('/user', [UserController::class, 'user'])->middleware('auth:sanctum');
Route::post('/user/{user}/change-active', [UserController::class, 'userChangeActive'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/user/{id}/update', [UserController::class, 'updateById'])->middleware(['auth:sanctum', 'role:admin']);
Route::get('/user/{user}', [UserController::class, 'getUser'])->middleware(['auth:sanctum']);
Route::post('/user/{id}/change-password', [AuthController::class, 'changePasswordByUser'])->middleware(['auth:sanctum', 'role:admin']);

Route::post('/finance/user/{user}', [\App\Http\Controllers\FinanceController::class, 'set'])->middleware(['auth:sanctum', 'role:admin']);
Route::delete('/finance/{finance}', [\App\Http\Controllers\FinanceController::class, 'delete'])->middleware(['auth:sanctum', 'role:admin']);
Route::get('/finance/user/{user}/statistics', [\App\Http\Controllers\FinanceController::class, 'statistics'])->middleware(['auth:sanctum']);
Route::get('/finance/user/{user}', [\App\Http\Controllers\FinanceController::class, 'list'])->middleware(['auth:sanctum']);
Route::get('finance/jar-monobank', [\App\Http\Controllers\FinanceController::class, 'redirectJarMonobank']);

Route::get('/costs', [\App\Http\Controllers\CostsController::class, 'list'])->middleware(['auth:sanctum']);
Route::post('/costs', [\App\Http\Controllers\CostsController::class, 'set'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/costs/{costs}', [\App\Http\Controllers\CostsController::class, 'edit'])->middleware(['auth:sanctum', 'role:admin']);
Route::delete('/costs/{costs}', [\App\Http\Controllers\CostsController::class, 'delete'])->middleware(['auth:sanctum', 'role:admin']);

Route::get('/registration/list', [\App\Http\Controllers\RegistrationController::class, 'list'])->middleware(['auth:sanctum', 'role:admin']);
Route::get('/registration/count', [\App\Http\Controllers\RegistrationController::class, 'count'])->middleware(['auth:sanctum', 'role:admin']);
Route::get('/registration/{registration}/validator', [\App\Http\Controllers\RegistrationController::class, 'validator'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/registration/{registration}/approve', [\App\Http\Controllers\RegistrationController::class, 'approve'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/registration/{registration}/change-active', [\App\Http\Controllers\RegistrationController::class, 'changeActive'])->middleware(['auth:sanctum', 'role:admin']);

Route::post('/car/create', [CarController::class, 'create'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/car/{id}', [CarController::class, 'update'])->middleware(['auth:sanctum']);
Route::get('/car', [CarController::class, 'all'])->middleware(['auth:sanctum']);
Route::get('/car/search', [CarController::class, 'search'])->middleware(['auth:sanctum']);
Route::get('/car/my', [CarController::class, 'myCars'])->middleware(['auth:sanctum']);
Route::get('/car/{id}', [CarController::class, 'find'])->middleware(['auth:sanctum']);
Route::delete('/car/{id}', [CarController::class, 'delete'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/car/{id}/collections', [CarController::class, 'addCollections'])->middleware(['auth:sanctum']);
Route::post('/car/{car}/change-active', [CarController::class, 'changeActive'])->middleware(['auth:sanctum'])->middleware(['auth:sanctum', 'role:admin']);
Route::delete('/car/{id}/collections/{mediaId}', [CarController::class, 'deleteCollections'])->middleware(['auth:sanctum']);


// ... остальной код файла

Route::group(['prefix' => 'mention', 'middleware' => ['auth:sanctum']], static function () {
    Route::post('/car/{car}', [MentionController::class, 'mention']);

    Route::get('/user/{user}/sent/count', [MentionController::class, 'getSentMentionsCount']);
    Route::get('/user/{user}/sent', [MentionController::class, 'getSentMentions']);
    Route::get('/user/{user}/received/count', [MentionController::class, 'getReceivedMentionsCount']);
    Route::get('/user/{user}/received', [MentionController::class, 'getReceivedMentions']);

});

Route::post('suggestions/send', [SuggestionsController::class, 'send'])->middleware(['auth:sanctum']);

Route::group(['prefix' => 'partners', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [PartnerController::class, 'index']);
    Route::post('/', [PartnerController::class, 'store'])->middleware('role:admin');
    Route::post('/{partner}', [PartnerController::class, 'update'])->middleware('role:admin');
    Route::post('/{partner}/photos', [PartnerController::class, 'addPhotos'])->middleware('role:admin');
    Route::delete('/{partner}/photos/{mediaId}', [PartnerController::class, 'deletePhoto'])->middleware('role:admin');

    // Promotions
    Route::group(['prefix' => '/{partner}/promotions'], function () {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/{promotion}', [PromotionController::class, 'show']);
        Route::post('/', [PromotionController::class, 'store'])->middleware('role:admin');
        Route::post('/{promotion}', [PromotionController::class, 'update'])->middleware('role:admin');
//        Route::delete('/{promotion}', [PromotionController::class, 'destroy'])->middleware('role:admin');
        Route::post('/{promotion}/photos', [PromotionController::class, 'addPhotos'])->middleware('role:admin');
        Route::delete('/{promotion}/photos/{mediaId}', [PromotionController::class, 'deletePhoto'])->middleware('role:admin');
    });
});

Route::get('/calendar', [CalendarController::class, 'list'])->middleware(['auth:sanctum']);
Route::get('/calendar/{event}/description', [CalendarController::class, 'calendarDescription'])->middleware(['auth:sanctum']);
Route::group(['prefix' => 'event', 'middleware' => ['auth:sanctum']], static function () {
    Route::get('/', [EventController::class, 'list']);
    Route::post('/', [EventController::class, 'create'])->middleware(['role:admin']);
    Route::put('/{event}', [EventController::class, 'update'])->middleware(['role:admin']);
    Route::put('/{event}/active/{active}', [EventController::class, 'changeActive'])->whereIn('active', [0, 1])->middleware(['role:admin']);
    Route::post('/{event}/image', [EventController::class, 'eventAddImage'])->middleware(['role:admin']);
    Route::delete('/{event}/image', [EventController::class, 'eventDeleteImages'])->middleware(['role:admin']);
    Route::delete('/{event}/collections/{mediaId}', [EventController::class, 'eventDeleteImage'])->middleware(['role:admin']);

    Route::get('/type', [EventController::class, 'type']);
    Route::post('/type/{eventType}/image', [EventController::class, 'eventTypeAddImage'])->middleware(['role:admin']);
    Route::delete('/type/{eventType}/image', [EventController::class, 'eventTypeDeleteImages'])->middleware(['role:admin']);
});

Route::group(['prefix' => 'goods', 'middleware' => ['auth:sanctum']], static function () {
    Route::get('/', [GoodsController::class, 'lists']);
    Route::post('/', [GoodsController::class, 'create'])->middleware(['role:admin']);
    Route::put('/{goods}', [GoodsController::class, 'update'])->middleware(['role:admin']);
    Route::patch('/{goods}/active/{active}', [GoodsController::class, 'changeActive'])->middleware(['role:admin']);

    Route::post('/{goods}/images', [GoodsController::class, 'eventAddImage'])->middleware(['role:admin']);
    Route::delete('/{goods}/images/{mediaId}', [GoodsController::class, 'eventDeleteImage'])->middleware(['role:admin']);
    Route::delete('/{goods}/images', [GoodsController::class, 'eventDeleteImages'])->middleware(['role:admin']);
});

Route::get('/genes', [CarController::class, 'genes'])->middleware(['auth:sanctum']);
Route::get('/models', [CarController::class, 'models'])->middleware(['auth:sanctum']);
Route::get('/colors', [CarController::class, 'colors'])->middleware(['auth:sanctum']);

Route::get('/cities', [CityController::class, 'all'])->middleware(['auth:sanctum']);

Route::post('/import', [ImportController::class, 'importMain'])->middleware(['auth:sanctum', 'role:admin']);

//Route::post('/test/fa-fa', [TestController::class, 'fafa'])->middleware(['auth:sanctum', 'role:admin']);
Route::get('/test', [TestController::class, 'test']);//->middleware(['auth:sanctum', 'role:admin']);


Route::get('webhook/monobank', [\App\Http\Controllers\FinanceController::class, 'webhookMonobank']);
Route::post('webhook/monobank', [\App\Http\Controllers\FinanceController::class, 'webhookMonobank']);

Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])->middleware('telegram.webhook');
Route::get('/telegram/test', [TelegramController::class, 'test']);

Route::group(['prefix' => 'draws', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [DrawController::class, 'index']);
    Route::get('/{draw}', [DrawController::class, 'show']);
    Route::post('/', [DrawController::class, 'store'])->middleware('role:admin');
    Route::post('/{draw}', [DrawController::class, 'update'])->middleware('role:admin');
    Route::post('/{draw}/deactivate', [DrawController::class, 'deactivate'])->middleware('role:admin');

    // Prizes routes nested under draws
    Route::group(['prefix' => '/{draw}/prizes'], function () {
        Route::get('/', [PrizeController::class, 'index']);
        Route::post('/', [PrizeController::class, 'store'])->middleware('role:admin');
        Route::post('/{prize}', [PrizeController::class, 'update'])->middleware('role:admin');
        Route::delete('/{prize}', [PrizeController::class, 'destroy'])->middleware('role:admin');
    });

    // Participants routes nested under draws
    Route::group(['prefix' => '/{draw}/participants'], function () {
        Route::get('/', [ParticipantController::class, 'index']);
        Route::post('/register', [ParticipantController::class, 'registerSelf']);
        Route::post('/register-manual', [ParticipantController::class, 'registerByAdmin'])->middleware('role:admin');
        Route::post('/{participant}', [ParticipantController::class, 'update'])->middleware('role:admin');
        Route::delete('/{participant}', [ParticipantController::class, 'destroy'])->middleware('role:admin');
    });
});

//->middleware('auth:sanctum') Проверяет аутентификацию с использованием Laravel Sanctum, который предоставляет возможность защищать API с помощью токенов.
//->middleware('guest') Проверяет, что пользователь не аутентифицирован (т.е., “гость”).
//->middleware('verified') Проверяет, что пользователь подтвердил свой email.
//->middleware('role:admin') Проверяет, что пользователь обладает определенной ролью, используя Spatie Permissions.
//->middleware('permission:view-dashboard') Проверяет, что у пользователя есть конкретное разрешение для действия.
//Route::group(['middleware' => ['permission:edit articles|publish articles']], function () {}); // маршруты для пользователей, у которых есть либо "edit articles", либо "publish articles"
