<?php

use App\Http\Controllers\Admin\Api\DocDemoController;
use App\Http\Controllers\Admin\Api\MenuController as ApiMenuController;
use App\Http\Controllers\Admin\Api\OperationLogController;
use App\Http\Controllers\Admin\Api\PermissionOptionsController;
use App\Http\Controllers\Admin\Api\RecycleBinItemController;
use App\Http\Controllers\Admin\Api\ResourceController as ApiResourceController;
use App\Http\Controllers\Admin\Api\RoleController as ApiRoleController;
use App\Http\Controllers\Admin\Api\SidebarController;
use App\Http\Controllers\Admin\Api\UploadController;
use App\Http\Controllers\Admin\Api\UserController as ApiUserController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CaptchaController;
use App\Http\Controllers\Admin\Content\DashboardController as ContentDashboardController;
use App\Http\Controllers\Admin\Content\DocumentationController;
use App\Http\Controllers\Admin\Content\MenuManageController;
use App\Http\Controllers\Admin\Content\OperationLogPageController;
use App\Http\Controllers\Admin\Content\ProfileController as ContentProfileController;
use App\Http\Controllers\Admin\Content\RecycleBinManageController;
use App\Http\Controllers\Admin\Content\ResourceManageController;
use App\Http\Controllers\Admin\Content\RoleManageController;
use App\Http\Controllers\Admin\Content\UserManageController;
use App\Http\Controllers\Admin\ShellController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('captcha', [CaptchaController::class, 'image'])->name('captcha');

    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.post');
        Route::get('login/2fa', [LoginController::class, 'showTwoFactorForm'])->name('login.2fa');
        Route::post('login/2fa', [LoginController::class, 'verifyTwoFactor'])->name('login.2fa.post');
    });

    Route::middleware(['admin.auth'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('/', [ShellController::class, 'index'])->name('home');

        Route::prefix('content')->name('content.')->group(function () {
            Route::get('dashboard', ContentDashboardController::class)
                ->middleware('admin.perm:dashboard:view')
                ->name('dashboard');
            Route::get('profile', ContentProfileController::class)->name('profile');

            Route::get('doc/usage', [DocumentationController::class, 'usage'])
                ->middleware('admin.perm:doc:usage')
                ->name('doc.usage');
            Route::get('doc/charts', [DocumentationController::class, 'charts'])
                ->middleware('admin.perm:doc:charts')
                ->name('doc.charts');
            Route::get('doc/sysinfo', [DocumentationController::class, 'sysinfo'])
                ->middleware('admin.perm:doc:sysinfo')
                ->name('doc.sysinfo');
            Route::get('doc/richtext', [DocumentationController::class, 'richtext'])
                ->middleware('admin.perm:doc:richtext')
                ->name('doc.richtext');
            Route::get('doc/upload-demo', [DocumentationController::class, 'uploadDemo'])
                ->middleware('admin.perm:doc:upload_demo')
                ->name('doc.upload_demo');
            Route::get('doc/table-demo', [DocumentationController::class, 'tableDemo'])
                ->middleware('admin.perm:doc:table_demo')
                ->name('doc.table_demo');

            Route::get('users', UserManageController::class)
                ->middleware('admin.perm:user:list')
                ->name('users');
            Route::get('roles', RoleManageController::class)
                ->middleware('admin.perm:role:list')
                ->name('roles');
            Route::get('menus', MenuManageController::class)
                ->middleware('admin.perm:menu:list')
                ->name('menus');
            Route::get('logs', OperationLogPageController::class)
                ->middleware('admin.perm:log:list')
                ->name('logs');

            Route::get('resources', ResourceManageController::class)
                ->middleware('admin.perm:resource:list')
                ->name('resources');

            Route::get('recycle-bin', RecycleBinManageController::class)
                ->middleware('admin.perm:recycle_bin:list')
                ->name('recycle_bin');
        });

        Route::prefix('api')->name('api.')->middleware(['admin.log'])->group(function () {
            Route::get('me', [SidebarController::class, 'me'])->name('me')->withoutMiddleware(['admin.log']);
            Route::put('me', [SidebarController::class, 'updateProfile'])->name('me.update')->withoutMiddleware(['admin.log']);
            Route::get('sidebar/menus', [SidebarController::class, 'menus'])->name('sidebar.menus')->withoutMiddleware(['admin.log']);

            Route::get('roles/options', [ApiRoleController::class, 'options'])->name('roles.options')->withoutMiddleware(['admin.log']);
            Route::get('permissions/options', [PermissionOptionsController::class, 'options'])->name('permissions.options')->withoutMiddleware(['admin.log']);

            Route::post('upload', [UploadController::class, 'store'])->name('upload.store')->withoutMiddleware(['admin.log']);

            Route::get('doc/table-demo', [DocDemoController::class, 'tableDemo'])
                ->middleware('admin.perm:doc:table_demo')
                ->name('doc.table_demo.data');
            Route::post('doc/table-demo/patch', [DocDemoController::class, 'patchDemoRow'])
                ->middleware('admin.perm:doc:table_demo')
                ->name('doc.table_demo.patch')
                ->withoutMiddleware(['admin.log']);

            Route::get('users', [ApiUserController::class, 'index'])->middleware('admin.perm:user:list')->name('users.index');
            Route::get('users/{id}', [ApiUserController::class, 'show'])->whereNumber('id')->middleware('admin.perm:user:view')->name('users.show');
            Route::post('users', [ApiUserController::class, 'store'])->middleware('admin.perm:user:create')->name('users.store');
            Route::put('users/{id}', [ApiUserController::class, 'update'])->whereNumber('id')->middleware('admin.perm:user:edit')->name('users.update');
            Route::delete('users/{id}', [ApiUserController::class, 'destroy'])->whereNumber('id')->middleware('admin.perm:user:delete')->name('users.destroy');
            Route::post('users/{id}/reset-password', [ApiUserController::class, 'resetPassword'])->whereNumber('id')->middleware('admin.perm:user:reset_password')->name('users.reset_password');
            Route::post('users/{id}/enable-google2fa', [ApiUserController::class, 'enableGoogle2fa'])->whereNumber('id')->middleware('admin.perm:user:google2fa_enable')->name('users.enable_google2fa');
            Route::post('users/{id}/disable-google2fa', [ApiUserController::class, 'disableGoogle2fa'])->whereNumber('id')->middleware('admin.perm:user:google2fa_disable')->name('users.disable_google2fa');
            Route::post('users/{id}/unlock-google2fa', [ApiUserController::class, 'unlockGoogle2fa'])->whereNumber('id')->middleware('admin.perm:user:google2fa_unlock')->name('users.unlock_google2fa');

            Route::get('roles', [ApiRoleController::class, 'index'])->middleware('admin.perm:role:list')->name('roles.index');
            Route::get('roles/{id}', [ApiRoleController::class, 'show'])->whereNumber('id')->middleware('admin.perm:role:view')->name('roles.show');
            Route::post('roles', [ApiRoleController::class, 'store'])->middleware('admin.perm:role:create')->name('roles.store');
            Route::put('roles/{id}', [ApiRoleController::class, 'update'])->whereNumber('id')->middleware('admin.perm:role:edit')->name('roles.update');
            Route::delete('roles/{id}', [ApiRoleController::class, 'destroy'])->whereNumber('id')->middleware('admin.perm:role:delete')->name('roles.destroy');
            Route::post('roles/{id}/assign-menus', [ApiRoleController::class, 'assignMenus'])->whereNumber('id')->middleware('admin.perm:role:assign_menu')->name('roles.assign_menus');

            Route::get('menus/tree', [ApiMenuController::class, 'tree'])->middleware('admin.perm:menu:list')->name('menus.tree');
            Route::post('menus', [ApiMenuController::class, 'store'])->middleware('admin.perm:menu:create')->name('menus.store');
            Route::put('menus/{id}', [ApiMenuController::class, 'update'])->whereNumber('id')->middleware('admin.perm:menu:edit')->name('menus.update');
            Route::delete('menus/{id}', [ApiMenuController::class, 'destroy'])->whereNumber('id')->middleware('admin.perm:menu:delete')->name('menus.destroy');

            Route::get('operation-logs', [OperationLogController::class, 'index'])->middleware('admin.perm:log:list')->name('operation_logs.index');
            Route::post('operation-logs/batch-delete', [OperationLogController::class, 'batchDestroy'])->middleware('admin.perm:log:delete')->name('operation_logs.batch_delete');

            Route::get('recycle-bin/items', [RecycleBinItemController::class, 'index'])->middleware('admin.perm:recycle_bin:list')->name('recycle_bin.items.index');
            Route::post('recycle-bin/items/{id}/restore', [RecycleBinItemController::class, 'restore'])->whereNumber('id')->middleware('admin.perm:recycle_bin:restore')->name('recycle_bin.items.restore');
            Route::delete('recycle-bin/items/{id}', [RecycleBinItemController::class, 'purge'])->whereNumber('id')->middleware('admin.perm:recycle_bin:purge')->name('recycle_bin.items.purge');

            Route::get('resources', [ApiResourceController::class, 'index'])->middleware('admin.perm:resource:list')->name('resources.index');
            Route::get('resources/{id}', [ApiResourceController::class, 'show'])->whereNumber('id')->middleware('admin.perm:resource:view')->name('resources.show');
            Route::put('resources/{id}', [ApiResourceController::class, 'update'])->whereNumber('id')->middleware('admin.perm:resource:edit')->name('resources.update');
            Route::delete('resources/{id}', [ApiResourceController::class, 'destroy'])->whereNumber('id')->middleware('admin.perm:resource:delete')->name('resources.destroy');
        });
    });
});
