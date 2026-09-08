<?php

namespace App\Actions\Imports;

use App\Models\HistoricalImport;
use App\Models\ImportedStatLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParseCsvImportAction
{
    /**
     * Parse the CSV file stream and store unconfirmed imported rows transaction-safely [114].
     */
    public function execute(HistoricalImport $import, string $csvFilePath, array $mappings): int
    {
        return DB::transaction(function () use ($import, $csvFilePath, $mappings) {
            $handle = fopen($csvFilePath, 'r');
            if (!$handle) {
                throw new \InvalidArgumentException("Unable to open CSV file.");
            }

            // 1. Fetch CSV headers to match mappings
            $headers = fgetcsv($handle, 1000, ",");
            $headers = array_map('trim', $headers);

            $rowCount = 0;

            // 2. Map CSV column indexes
            $playerIdentityColIndex = $this->resolveMappedIndex($headers, $mappings['player_identity_id'] ?? null);
            $seasonColIndex = $this->resolveMappedIndex($headers, $mappings['season_key'] ?? null);
            $gameDateColIndex = $this->resolveMappedIndex($headers, $mappings['game_date'] ?? null);

            // Establish mapping indices for common performance metrics
            $statMappingIndexes = [];
            foreach ($mappings['stats'] ?? [] as $statKey => $csvHeaderName) {
                $idx = $this->resolveMappedIndex($headers, $csvHeaderName);
                if ($idx !== null) {
                    $statMappingIndexes[$statKey] = $idx;
                }
            }

            // 3. Process each CSV row
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $rowCount++;

                // Resolve linked PlayerIdentity ID (usually selected in preview/confirm screen or resolved by code/name)
                $resolvedPlayerId = $playerIdentityColIndex !== null ? trim($row[$playerIdentityColIndex]) : null;
                if (!$resolvedPlayerId) {
                    continue; // Roster lines must map cleanly to a PlayerIdentity [136]
                }

                $seasonKey = $seasonColIndex !== null ? trim($row[$seasonColIndex]) : 'Historical Unassigned';
                $gameDate = ($gameDateColIndex !== null && !empty($row[$gameDateColIndex])) ? date('Y-m-d', strtotime(trim($row[$gameDateColIndex]))) : null;

                // Build structured metrics JSON blob
                $statsBlob = [];
                foreach ($statMappingIndexes as $statKey => $colIndex) {
                    $statsBlob[$statKey] = (float) trim($row[$colIndex]);
                }

                // Append unverified line item
                ImportedStatLine::create([
                    'id' => Str::uuid()->toString(),
                    'historical_import_id' => $import->id,
                    'subject_type' => 'player',
                    'subject_id' => $resolvedPlayerId,
                    'season_key' => $seasonKey,
                    'game_date' => $gameDate,
                    'stat_blob_json' => $statsBlob,
                    'source_row_identifier' => "CSV Row #{$rowCount}",
                ]);
            }

            fclose($handle);

            // Audit the parse completion
            $import->auditLogs()->create([
                'user_id' => $import->uploaded_by_user_id,
                'action' => 'previewed',
                'metadata_json' => [
                    'records_parsed' => $rowCount,
                    'mappings_used' => $mappings,
                ]
            ]);

            return $rowCount;
        });
    }

    protected function resolveMappedIndex(array $headers, ?string $headerName): ?int
    {
        if (!$headerName) {
            return null;
        }
        $index = array_search($headerName, $headers);
        return $index !== false ? (int)$index : null;
    }
}