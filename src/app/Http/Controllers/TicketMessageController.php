<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttachmentResource;
use App\Http\Resources\TicketMessageResource;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\StoreTicketMessageRequest;
use App\Services\Tickets\TicketMessageService;
use Illuminate\Support\Facades\Storage;

class TicketMessageController extends Controller
{
    public function __construct(
        private readonly TicketMessageService $service
    ) {}

    public function store(StoreTicketMessageRequest $request, Ticket $ticket)
    {
        $this->authorize('create', [TicketMessage::class, $ticket]);

        $data = $request->validated();

        if (!$request->user()->isAdmin() && !$request->user()->isAgent()) {
            $data['is_internal'] = false;
        }

        $this->service->addMessage($ticket, $data, $request->user()->id);

        return redirect()->route('tickets.show', $ticket->id);
    }

    public function destroy(Ticket $ticket, TicketMessage $message)
    {
        $this->authorize('delete', $message);

        $this->service->deleteMessage($message);

        return response()->json(['message' => 'Mensaje eliminado correctamente']);
    }

    public function storeAttachment(StoreAttachmentRequest $request, Ticket $ticket)
    {
        $this->authorize('create', [Attachment::class, $ticket]);

        $this->service->addAttachmentToTicket($ticket, $request->file('file'));

        return redirect()->route('tickets.show', $ticket->id);
    }

    public function destroyAttachment(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);

        $this->service->deleteAttachment($attachment);

        return response()->json(['message' => 'Adjunto eliminado correctamente']);
    }

    public function downloadAttachment(Attachment $attachment)
    {
        $this->authorize('download', $attachment);

        return Storage::download($attachment->file_path, $attachment->file_name);
    }
}
