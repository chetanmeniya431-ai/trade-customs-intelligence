<?php

namespace App\Livewire\Shipments;

use App\Imports\ShipmentsImport as ShipmentsImportClass;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Import extends Component
{
    use WithFileUploads;

    public $file;

    public ?int $createdCount = null;

    public array $rowErrors = [];

    public function import(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $importer = new ShipmentsImportClass(Auth::id());
        Excel::import($importer, $this->file);

        $this->createdCount = $importer->created;
        $this->rowErrors = $importer->errors;
        $this->reset('file');
    }

    public function render()
    {
        return view('livewire.shipments.import')->title('Bulk import shipments');
    }
}
