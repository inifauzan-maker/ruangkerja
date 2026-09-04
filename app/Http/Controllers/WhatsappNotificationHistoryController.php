<?php

namespace App\Http\Controllers;

use App\Models\WhatsappNotificationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappNotificationHistoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logsQuery = WhatsappNotificationLog::query()
            ->whereHas('connection', fn ($query) => $query->where('user_id', $request->user()->id));

        $statusCounts = (clone $logsQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $logs = $logsQuery
            ->with('task.list.board')
            ->latest()
            ->paginate(15);

        return view('profile.whatsapp-history', compact('logs', 'statusCounts'));
    }
}
