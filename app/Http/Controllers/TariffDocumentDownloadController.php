<?php

namespace App\Http\Controllers;

use App\Models\TariffDocument;
use Illuminate\Support\Facades\Storage;

class TariffDocumentDownloadController
{
    public function __invoke(TariffDocument $tariffDocument)
    {
        if (! Storage::disk('public')->exists($tariffDocument->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($tariffDocument->file_path, $tariffDocument->name);
    }
}
