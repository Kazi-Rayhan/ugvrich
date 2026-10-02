<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * The researcher's notification list.
 *
 * Laravel's own database notifications, read through the relation on the user,
 * so nothing here can show somebody else's.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('researcher.notifications', [
            'notifications' => $request->user()->notifications()->paginate(25),
        ]);
    }

    /** Opening one marks it read, then sends the reader where it points. */
    public function read(Request $request, string $notification)
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();

        $record->markAsRead();

        return redirect($record->data['url'] ?? route('researcher.dashboard'));
    }

    /**
     * Remember the language this researcher wants the portal in.
     *
     * The portal has no /en mirror to switch to, so the choice lives in the
     * session and SetLocale reads it back on the next request.
     */
    public function locale(Request $request)
    {
        $locale = $request->input('locale');

        if (isset(\App\Http\Middleware\SetLocale::LOCALES[$locale])) {
            $request->session()->put(\App\Http\Middleware\SetLocale::SESSION_KEY, $locale);
        }

        return back();
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('saved', __('research.notifications.marked'));
    }
}
