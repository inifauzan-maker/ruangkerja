<?php

namespace App\Http\Controllers;

use App\Actions\DispatchWhatsappNotifications;
use App\Actions\StoreAttachments;
use App\Http\Requests\StoreBoardMessageRequest;
use App\Models\Board;
use App\Models\BoardMessage;
use App\Models\WhatsappConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BoardMessageController extends Controller
{
    public function store(
        StoreBoardMessageRequest $request,
        Board $board,
        StoreAttachments $storeAttachments,
        DispatchWhatsappNotifications $dispatchWhatsappNotifications,
    ): RedirectResponse {
        abort_unless($board->belongsToUser($request->user()), 404);

        $message = DB::transaction(function () use ($board, $request, $storeAttachments): BoardMessage {
            $message = $board->messages()->create([
                'user_id' => $request->user()->id,
                'body' => $request->string('body')->trim()->toString(),
            ]);

            $storeAttachments->execute($message, $request->user(), $request->file('attachments', []));

            return $message;
        });

        $board->loadMissing('team');
        $summary = $message->body !== '' ? Str::limit($message->body, 100) : 'Mengirim lampiran baru.';
        $dispatchWhatsappNotifications->execute(
            $board->team,
            $request->user(),
            WhatsappConnection::EventChatMessage,
            'Pesan grup baru',
            $request->user()->name.': '.$summary,
            $board->name,
            route('boards.chat', $board),
        );

        return redirect()->route('boards.chat', $board)->with('status', 'Pesan terkirim.');
    }
}
