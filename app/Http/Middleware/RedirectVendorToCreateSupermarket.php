<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification; // استيراد كلاس Notification

class RedirectVendorToCreateSupermarket
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->hasRole('vendor')) {
            Log::info("User {$user->id} (vendor) access check: has supermarket = " . ($user->supermarket ? 'yes' : 'no'));

            // إضافة إشعار باستخدام Filament
            if (!$user->supermarket) {
                Notification::make()
                ->title('تنبيه')
                ->body('يرجى إنشاء متجر لتتمكن من استخدام جميع الميزات.')
                ->warning()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('create_supermarket')
                        ->label('إنشاء متجر')
                        ->url(route('filament.resources.supermarkets.create'))
                        ->button(),
                ])
                ->send();
            }
        }

        return $next($request);
    }
}
