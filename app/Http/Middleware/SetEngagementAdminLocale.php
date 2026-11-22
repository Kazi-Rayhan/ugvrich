<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEngagementAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(
            'filament.admin.resources.consultancy-engagements.*',
            'filament.admin.resources.consultancy-engagement-policies.*',
            'filament.admin.resources.expert-wallets.*',
            'filament.admin.resources.wallet-withdrawals.*',
        )) {
            $locale = $request->query('language');
            if (in_array($locale, ['bn', 'en'], true)) {
                $request->session()->put('engagement_admin_locale', $locale);
            }

            app()->setLocale($request->session()->get('engagement_admin_locale', config('app.locale')));
        }

        return $next($request);
    }
}
