<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    private const DEFAULTS = [
        'ticket_created'   => true,
        'ticket_assigned'  => true,
        'ticket_replied'   => true,
        'ticket_closed'    => false,
        'sla_breach'       => true,
        'dashboard'        => true,
        'mail_from_name'   => 'Support',
        'mail_from_address'=> 'support@example.com',
    ];

    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return Inertia::render('Settings/Notifications', [
            'settings' => Setting::getGroup('notif', self::DEFAULTS),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'ticket_created'    => ['boolean'],
            'ticket_assigned'   => ['boolean'],
            'ticket_replied'    => ['boolean'],
            'ticket_closed'     => ['boolean'],
            'sla_breach'        => ['boolean'],
            'dashboard'         => ['boolean'],
            'mail_from_name'    => ['required', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set("notif.{$key}", $value);
        }

        return redirect()->back()->with('success', 'Notification settings saved.');
    }
}
