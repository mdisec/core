<?php

// Auth Routes
require_once(app_path('Http/Controllers/Auth/_routes.php'));

Route::group(['middleware' => ['auth','permissions']],function () {

// Extension Routes

require_once(app_path('Http/Controllers/Extension/_routes.php'));

// Notification Routes

require_once(app_path('Http/Controllers/Notification/_routes.php'));

// Permission Routes

require_once(app_path('Http/Controllers/Permission/_routes.php'));

// Server Routes

require_once(app_path('Http/Controllers/Server/_routes.php'));

// Certificate Routes

require_once(app_path('Http/Controllers/Certificate/_routes.php'));

// Server Routes

require_once(app_path('Http/Controllers/Settings/_routes.php'));

// Widgets Routes

require_once(app_path('Http/Controllers/Widgets/_routes.php'));

// Role Routes

require_once(app_path('Http/Controllers/Roles/_routes.php'));

// Change the language
Route::get('/locale', 'HomeController@setLocale')->name('set_locale');

// Change the language
Route::post('/theme', 'HomeController@setTheme')->name('set_theme');

// Set Collapse

Route::post('/collapse','HomeController@collapse')->name('set_collapse');

// Home Route

Route::get('/', 'HomeController@index')->name('home');

// Vault Route

Route::get('/kasa', 'UserController@userKeyList')->name('keys');

Route::post('/onbellek_temizle', 'UserController@cleanSessions')->name('clean_sessions');

// Add Key Route
Route::post('/kasa/ekle', 'UserController@addKey')->name('key_add');

// User Details Route

Route::get('/kullanici/{user_id}', 'UserController@one')->name('user');

// My Requests Route

Route::get('/taleplerim', 'HomeController@all')->name('request_permission');

// Send LimanRequest Route

Route::post('/talep', 'HomeController@request')->name('request_send');

// Search Page

Route::post('/arama/','SearchController@index')->name('search');

// Log View Route
Route::view('/logs/{log_id}','logs.one');

// User Add
Route::post('/kullanici/ekle','UserController@add')->name('user_add')->middleware('admin');

// User Remove
Route::post('/kullanici/sil','UserController@remove')->name('user_remove')->middleware('admin');

// User Remove
Route::post('/kullanici/parola/sifirla','UserController@passwordReset')->name('user_password_reset')->middleware('admin');

Route::view('/logs/{log_id}','logs.one');

Route::view('/profil','user.self')->name('my_profile');

Route::post('/profil','UserController@selfUpdate')->name('profile_update');

Route::post('/user/update','UserController@adminUpdate')->name('update_user')->middleware('admin');

Route::post('/user/setting/delete','UserController@removeSetting')->name('user_setting_remove');

Route::post('/user/setting/update','UserController@updateSetting')->name('user_setting_update');
});

Route::post('/lmn/private/extensionApi','Extension\OneController@internalExtensionApi')->middleware(['auth', 'permissions']);

Route::post('/lmn/private/runCommandApi','Extension\OneController@internalRunCommandApi')->middleware(['auth', 'permissions']);

Route::post('/lmn/private/putFileApi','Extension\OneController@internalPutFileApi')->middleware(['auth', 'permissions']);

Route::post('/lmn/private/getFileApi','Extension\OneController@internalGetFileApi')->middleware(['auth', 'permissions']);

Route::post('/lmn/private/runScriptApi','Extension\OneController@internalRunScriptApi')->middleware(['auth', 'permissions']);

Route::post('/lmn/private/putSession','Extension\OneController@internalPutSessionApi')->middleware(['auth', 'permissions']);

Route::post('/lmn/private/reverseProxyRequest','Extension\OneController@internalAddProxyConfig')->middleware(['auth', 'permissions']);

Route::any('/upload/{any?}', function () {
    $server = app('tus-server');
    $extension_id = request("extension_id");
    $extension = \App\Extension::find($extension_id);
    if($extension){
        $path = env('EXTENSIONS_PATH') . strtolower($extension->name);
        if (!file_exists($path."/uploads")) {
            mkdir($path."/uploads");
        }
        $server->setUploadDir($path."/uploads");
    }
    $response = $server->serve();
    return $response->send();
})->where('any', '.*')->middleware(['auth', 'permissions']);

Route::post('/upload_info', function(){
    request()->validate([
        'key' => 'required'
    ]);
    $key = request('key');
    $server = app('tus-server');
    $info = $server->getCache()->get($key);
    $extension_path = explode("/uploads/", $info['file_path'], 2)[0];
    $info['file_path'] = str_replace($extension_path, '', $info['file_path']);
    return $info;
})->middleware(['auth', 'permissions']);