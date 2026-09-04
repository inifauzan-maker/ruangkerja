<?php

namespace App\Http\Controllers;

use App\Actions\DispatchWhatsappNotifications;
use App\Actions\StoreAttachments;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Board;
use App\Models\WhatsappConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function store(
        StoreAnnouncementRequest $request,
        Board $board,
        StoreAttachments $storeAttachments,
        DispatchWhatsappNotifications $dispatchWhatsappNotifications,
    ): RedirectResponse {
        abort_unless($board->belongsToUser($request->user()), 404);

        $announcement = DB::transaction(function () use ($board, $request, $storeAttachments): Announcement {
            $announcement = $board->announcements()->create([
                ...$request->safe()->only(['title', 'body']),
                'author_id' => $request->user()->id,
                'is_pinned' => $request->boolean('is_pinned'),
            ]);

            $storeAttachments->execute($announcement, $request->user(), $request->file('attachments', []));

            return $announcement;
        });

        $board->loadMissing('team');
        $dispatchWhatsappNotifications->execute(
            $board->team,
            $request->user(),
            WhatsappConnection::EventAnnouncement,
            'Pengumuman baru',
            $request->user()->name.' menerbitkan “'.$announcement->title.'”.',
            $board->name,
            route('boards.announcements', $board),
        );

        return redirect()->route('boards.announcements', $board)->with('status', 'Pengumuman diterbitkan.');
    }
}
