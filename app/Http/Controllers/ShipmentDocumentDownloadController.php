<?php

namespace App\Http\Controllers;

use App\Models\ShipmentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShipmentDocumentDownloadController
{
    public function __invoke(Request $request, ShipmentDocument $document)
    {
        $user = $request->user();
        $shipment = $document->shipment;

        if ($user->isClientUser() && $shipment->client_id !== $user->client_id) {
            abort(403);
        }

        if ($user->hasRole('Finance') && ! str_contains(strtolower($document->document_type), 'invoice')
            && ! str_contains(strtolower($document->document_type), 'payment')) {
            abort(403, 'Finance access is limited to value and payment documents.');
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->document_type.'.pdf');
    }
}
