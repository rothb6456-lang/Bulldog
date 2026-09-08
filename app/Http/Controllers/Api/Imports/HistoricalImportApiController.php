<?php

namespace App\Http\Controllers\Api\Imports;

use App\Actions\History\RecompilePlayerHistoryAction;
use App\Actions\Imports\ParseCsvImportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportRequest;
use App\Models\HistoricalImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HistoricalImportApiController extends Controller
{
    public function __construct(
        protected ParseCsvImportAction $parser,
        protected RecompilePlayerHistoryAction $recompiler,
    ) {}

    public function store(StoreImportRequest $request): JsonResponse
    {
        $file = $request->file('csv_file');
        $tempPath = $file->storeAs('temp-imports', Str::uuid()->toString() . '.csv');

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

        return response()->json([
            'message' => 'Import record created.',
            'import' => $import,
            'file_path' => $tempPath,
        ], 201);
    }

    public function confirm(Request $request, HistoricalImport $import): JsonResponse
    {
        $validated = $request->validate([
            'player_identity_id' => ['required', 'string'],
            'season_key' => ['required', 'string'],
            'stats' => ['required', 'array'],
        ]);

        $csvPath = storage_path('app/' . $request->input('file_path', ''));
        if (! is_file($csvPath)) {
            return response()->json(['message' => 'CSV not found.'], 404);
        }

        $rowCount = $this->parser->execute($import, $csvPath, $validated);

        $import->update(['verification_status' => 'approved']);

        $import->auditLogs()->create([
            'user_id' => $request->user()->id,
            'action' => 'confirmed',
            'metadata_json' => ['rows_imported' => $rowCount],
        ]);

        $playerIdentityId = $import->context_id;
        $this->recompiler->execute($playerIdentityId);

        return response()->json([
            'message' => 'Import confirmed and history rebuilt.',
            'rows_imported' => $rowCount,
            'player_identity_id' => $playerIdentityId,
        ]);
    }

    public function show(HistoricalImport $import): JsonResponse
    {
        return response()->json([
            'import' => $import->load(['uploader', 'statLines', 'auditLogs']),
        ]);
    }
}
