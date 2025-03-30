<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameMatchController;
use App\Http\Controllers\NewsFeedController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\StageTypeController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Авторизация
Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Открытые маршруты (без аутентификации)
Route::prefix('guest')->group(function () {
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/teams/{id}', [TeamController::class, 'show']);
    Route::get('/game-matches', [GameMatchController::class, 'index']);
    Route::get('/game-matches/{id}', [GameMatchController::class, 'show']);
    Route::get('/stages', [StageController::class, 'index']);
    Route::get('/stages/{id}', [StageController::class, 'show']);
    Route::get('/news-feeds', [NewsFeedController::class, 'index']);
    Route::get('/news-feeds/{id}', [NewsFeedController::class, 'show']);
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{id}', [GameController::class, 'show']);
    Route::get('/stage-type', [StageTypeController::class, 'index']);
    Route::get('/popular-tournaments', [TournamentController::class, 'popularTournaments']);
    Route::get('/participants/profile/{userId}', [ParticipantController::class, 'profile']);
    Route::middleware('auth:api')->get('participants/my-profile', [ParticipantController::class, 'myProfile']);
    Route::get('/tournaments/{id}/basket', [TournamentController::class, 'getTournamentBasket']);
    Route::get('/statistics', [TournamentController::class, 'getStatistics']);
    Route::middleware('auth:api')->get('/notifications', [NotificationController::class, 'getUserNotifications']);
    Route::middleware( 'auth:api')->post('/tournament/{id}/notify-registration', [NotificationController::class, 'notifyTournamentRegistrationOpen']);
    Route::middleware('auth:api')->get('/notifications/unread', [NotificationController::class, 'getUnreadNotifications']);
    Route::get('/teams/{id}/members', [TeamController::class, 'getTeamMembers']);
    Route::get('/users/{id}/teams', [TeamController::class, 'getUserTeams']);
    Route::middleware('auth:sanctum')->post('/leave-team', [TeamController::class, 'leaveTeam']);
    Route::middleware( 'auth:api')->post('/tournament/{id}/notify-start', [NotificationController::class, 'notifyTournamentStart']);
    Route::middleware( 'auth:api')->post('/match/{id}/notify-reschedule', [NotificationController::class, 'notifyMatchReschedule']);
    Route::middleware( 'auth:api')->post('/match/{id}/notify-result', [NotificationController::class, 'notifyMatchResult']);
    Route::middleware( 'auth:api')->post('/match/{id}/notify-next-stage', [NotificationController::class, 'notifyNextStage']);
    Route::middleware( 'auth:api')->post('/match/{id}/notify-team-elimination', [NotificationController::class, 'notifyTeamElimination']);
    Route::middleware( 'auth:api')->post('/match/{id}/notify-team-registration', [NotificationController::class, 'notifyTournamentRegistration']);
    Route::middleware( 'auth:api')->post('/match/{id}/notify-team-registration-accept', [NotificationController::class, 'acceptTeamRegistration']);
});

//Пользовательский функционал
Route::middleware(['auth:api', 'role:4'])->prefix('user')->group(function () {
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('game-matches', [GameMatchController::class, 'index']);
    Route::get('game-matches/{id}', [GameMatchController::class, 'show']);
    Route::get('stages', [GameMatchController::class, 'index']);
    Route::get('stages/{id}', [GameMatchController::class, 'show']);
    Route::get('news-feeds', [NewsFeedController::class, 'index']);
    Route::get('news-feeds/{id}', [NewsFeedController::class, 'show']);
    Route::get('games', [GameController::class, 'index']);
    Route::get('games/{id}', [GameController::class, 'show']);
    Route::get('team-invites', [TeamInviteController::class, 'index'])->middleware('auth');
    Route::post('team-invites/{inviteId}/accept', [TeamInviteController::class, 'accept'])->middleware('auth');
    Route::post('team-invites/{inviteId}/decline', [TeamInviteController::class, 'decline'])->middleware('auth');
    Route::get('notifications', [NotificationController::class, 'index'])->middleware('auth');
    Route::get('notifications/{id}', [NotificationController::class, 'show'])->middleware('auth');
    Route::get('/popular-tournaments', [TournamentController::class, 'popularTournaments']);
    Route::post('/send-invite', [TeamInviteController::class, 'sendInvite']);
    Route::post('/invite/respond', [TeamInviteController::class, 'respondInvite']);
});

Route::middleware(['auth:api', 'role:3'])->prefix('operator')->group(function () {
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('game-matches', [GameMatchController::class, 'index']);
    Route::get('game-matches/{id}', [GameMatchController::class, 'show']);
    Route::get('stages', [GameMatchController::class, 'index']);
    Route::get('stages/{id}', [GameMatchController::class, 'show']);
    Route::get('news-feeds', [NewsFeedController::class, 'index']);
    Route::get('news-feeds/{id}', [NewsFeedController::class, 'show']);
    Route::get('games', [GameController::class, 'index']);
    Route::get('games/{id}', [GameController::class, 'show']);
    Route::get('team-invites', [TeamInviteController::class, 'index'])->middleware('auth');
    Route::post('team-invites/{inviteId}/accept', [TeamInviteController::class, 'accept'])->middleware('auth');
    Route::post('team-invites/{inviteId}/decline', [TeamInviteController::class, 'decline'])->middleware('auth');
    Route::get('notifications', [NotificationController::class, 'index'])->middleware('auth');
    Route::get('notifications/{id}', [NotificationController::class, 'show'])->middleware('auth');
    Route::get('/popular-tournaments', [TournamentController::class, 'popularTournaments']);
    Route::post('/tournaments/create', [TournamentController::class, 'store']);
    Route::middleware( 'auth:api')->post('/tournament/{id}/notify-registration-closed', [NotificationController::class, 'notifyRegistrationClosed']);
});

//Админский функционал
Route::middleware(['auth:api', 'role:1'])->prefix('admin')->group(function () {
    // Просмотр списка турниров
    Route::get('/tournaments', [TournamentController::class, 'index']);
    // Просмотр турнира
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
    // Создание турнира
    Route::post('/tournaments/create', [TournamentController::class, 'store']);
    // Редактирование турнира
    Route::post('/tournaments/update/{id}', [TournamentController::class, 'update']);
    // Удаление турнира
    Route::delete('/tournaments/delete/{id}', [TournamentController::class, 'destroy']);
    // Добавление команды в турнир
    Route::post('/tournaments/addTeam/{id}', [TournamentController::class, 'addTeam']);
    // Удаление команды из турнира
    Route::delete('/tournaments/removeTeam/{tournamentId}/{teamId}', [TournamentController::class, 'removeTeam']);
    // Топ 10 популярных турниров
    Route::get('/popular-tournaments', [TournamentController::class, 'popularTournaments']);
    // Просмотр команды по ID
    Route::get('/teams/{id}', [TeamController::class, 'show']);
    // Создание новой команды
    Route::post('/teams/create', [TeamController::class, 'store']);
    // Редактирование команды
    Route::post('/teams/update/{id}', [TeamController::class, 'update']);
    // Удаление команды
    Route::delete('/teams/delete/{id}', [TeamController::class, 'destroy']);
    // Создание участника
    Route::post('/participants/create', [ParticipantController::class, 'store']);
    // Редактирование участника
    Route::post('/participants/update/{id}', [ParticipantController::class, 'update']);
    // Удаление участника
    Route::delete('/participants/delete/{id}', [ParticipantController::class, 'destroy']);
    // Список игровых матчей
    Route::get('game-matches', [GameMatchController::class, 'index']);
    // Просмотр игрового матча
    Route::get('game-matches/{id}', [GameMatchController::class, 'show']);
    // Создания игрового матча
    Route::post('game-matches/create', [GameMatchController::class, 'store']);
    // Обновление игрового матча
    Route::post('game-matches/update/{id}', [GameMatchController::class, 'update']);
    // Удаление игрового матча
    Route::delete('game-matches/delete/{id}', [GameMatchController::class, 'destroy']);
    // Список этапов
    Route::get('stages', [StageController::class, 'index']);
    // Просмотр этапа
    Route::get('stages/{id}', [StageController::class, 'show']);
    // Создания этапа
    Route::post('stages/create', [StageController::class, 'store']);
    // Обновление этапа
    Route::post('stages/update/{id}', [StageController::class, 'update']);
    // Удаление этапа
    Route::delete('stages/delete/{id}', [StageController::class, 'destroy']);
    // Создание типа этапа
    Route::post('stage-types/create', [StageTypeController::class, 'store']);
    // Обновление типа этапа
    Route::post('stage-types/update/{id}', [StageTypeController::class, 'update']);
    // Удаление типа этапа
    Route::delete('stage-types/delete/{id}', [StageTypeController::class, 'destroy']);
    // Просмотр списка новостей
    Route::get('news-feeds', [NewsFeedController::class, 'index']);
    // Просмотр новости
    Route::get('news-feeds/{id}', [NewsFeedController::class, 'show']);
    // Создания новости
    Route::post('news-feeds/create', [NewsFeedController::class, 'store']);
    // Обновление новости
    Route::post('news-feeds/update/{id}', [NewsFeedController::class, 'update']);
    // Удаление новости
    Route::delete('news-feeds/delete/{id}', [NewsFeedController::class, 'destroy']);
    // Просмотр списка игр
    Route::get('games', [GameController::class, 'index']);
    // Просмотр игры
    Route::get('games/{id}', [GameController::class, 'show']);
    // Создания игры
    Route::post('games/create', [GameController::class, 'store']);
    // Обновление игры
    Route::post('games/update/{id}', [GameController::class, 'update']);
    // Удаление игры
    Route::delete('games/delete/{id}', [GameController::class, 'destroy']);
    //Список приглашений для пользователя
    Route::get('team-invites', [TeamInviteController::class, 'index'])->middleware('auth');
    // Создание приглашения
    Route::post('team-invites/create', [TeamInviteController::class, 'store'])->middleware('auth');
    // Принятие приглашения
    Route::post('team-invites/{inviteId}/accept', [TeamInviteController::class, 'accept'])->middleware('auth');
    // Отклонение приглашения
    Route::post('team-invites/{inviteId}/decline', [TeamInviteController::class, 'decline'])->middleware('auth');
    // Удаление приглашения
    Route::delete('team-invites/{inviteId}', [TeamInviteController::class, 'destroy'])->middleware('auth');
    // Получение всех уведомлений для текущего пользователя
    Route::post('/tournaments/add-match', [TournamentController::class, 'addMatchToTournament']);
    // Роут для обновления результата матча
    Route::post('/tournaments/{tournamentId}/matches/{matchId}/update-result', [TournamentController::class, 'updateMatchResult']);
    // Роут для удаления матча
    Route::delete('/tournaments/{tournamentId}/matches/{matchId}', [TournamentController::class, 'removeMatchFromTournament']);
    // Роут для отправки уведомлений (Обновление статуса турнира)
    Route::post('/tournaments/{tournamentId}/status', [TournamentController::class, 'updateTournamentStatus']);
    // Роут для обновления турнирной сетки
    Route::post('/basket/update', [TournamentController::class, 'updateBasketResults']);
    // Роут для создания стадии
    Route::post('/basket/create-stage', [TournamentController::class, 'createStage']);
    // Роут для просмотра статистики
    Route::get('/stats/overview', [StatController::class, 'overview']);
    // Статистика по турнирам
    Route::get('/stats/tournaments', [StatController::class, 'tournamentStats']);
    // Роут для добавления матча в турнир
    Route::post('/tournaments/add-match', [TournamentController::class, 'addMatchToTournament']);
});
