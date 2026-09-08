<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportRequest;
use App\Models\HistoricalImport;
use App\Actions\Imports\ParseCsvImportAction;
use App\Actions\History\RecompilePlayerHistoryAction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportWizardController extends Controller
{
    protected ParseCsvImportAction $parser;
    protected RecompilePlayerHistoryAction $recompiler;

    public function __construct(ParseCsvImportAction $parser, RecompilePlayerHistoryAction $recompiler)
    {
        $this->parser = $parser;
        $this->recompiler = $recompiler;
    }

    public function create()
    {
        return view('imports.create');
    }

    public function store(StoreImportRequest $request)
    {
        $file = $request->file('csv_file');

        // Save uploaded CSV temporarily
        $tempPath = $file->storeAs('temp-imports', Str::uuid()->toString() . '.csv');

        // Create the shell record
        $import = HistoricalImport::create([
            'id' => Str::uuid()->toString(),
            'import_type' => $request->import_type,
            'context_type' => $request->context_type,
            'context_id' => $request->context_id,
            'source_label' => $request->source_label,
            'fidelity_level' => $request->fidelity_level,
            'verification_status' => 'pending',
            'uploaded_by_user_id' => $request->user()->id,
            'notes' => $request->notes,
        ]);

        $import->auditLogs()->create([
            'user_id' => $request->user()->id,
            'action' => 'uploaded',
            'metadata_json' => ['file_path' => $tempPath],
        ]);

        // Put filepath into session for mapping preview
        session(['import_csv_path' => $tempPath]);

        return redirect()->route('imports.mapping', $import);
    }

    public function mapping(HistoricalImport $import)
    {
        $csvPath = session('import_csv_path');
        if (!$csvPath || !file_exists(storage_path('app/' . $csvPath))) {
            return redirect()->route('imports.create')->withErrors(['csv_file' => 'Upload session expired.']);
        }

        // Read the CSV headers to let the coach perform visual column mapping
        $handle = fopen(storage_path('app/' . $csvPath), 'r');
        $headers = fgetcsv($handle, 1000, ",");
        fclose($handle);

        return view('imports.mapping', compact('import', 'headers'));
    }

    public function confirm(Request $request, HistoricalImport $import)
    {
        $csvPath = session('import_csv_path');

        $mappings = $request->validate([
            'player_identity_id' => ['required', 'string'],
            'season_key' => ['required', 'string'],
            'stats' => ['required', 'array'],
        ]);

        // Parse and write imported lines
        $rowCount = $this->parser->execute($import, storage_path('app/' . $csvPath), $mappings);

        // Transactionally approve and sync to aggregations [83]
        $import->update(['verification_status' => 'approved']);

        $import->auditLogs()->create([
            'user_id' => $request->user()->id,
            'action' => 'confirmed',
            'metadata_json' => ['rows_imported' => $rowCount],
        ]);

        // Trigger dynamic compilation for this athlete
        $playerIdentityId = $import->context_id; // In player-context import
        $this->recompiler->execute($playerIdentityId);

        // Clear session file path
        session()->forget('import_csv_path');

        return redirect()->route('players.career', $playerIdentityId)->with('success', "Import completed! {$rowCount} lines processed and merged into career history.");
    }
}