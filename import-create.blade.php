<!-- resources/views/imports/create.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Career History | Bulldog Statbook</title>
    <style>
        :root {
            --bg: #f3f5f4;
            --surface: #ffffff;
            --surface-alt: #f7f8f8;
            --text: #0d1b26;
            --text-muted: #5f6d78;
            --border: #d7dede;
            --border-strong: #b9c4c4;
            --navy: #081722;
            --navy-2: #102533;
            --green: #007a43;
            --green-hover: #00663a;
            --green-soft: #e4f3eb;
            --blue-accent: #0f6f97;
            --blue-soft: #e7f2f7;
            --warning-soft: #fff2d9;
            --warning-text: #9a6700;
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 20px;
            --shadow-sm: 0 1px 2px rgba(8, 23, 34, 0.06);
            --shadow-md: 0 8px 24px rgba(8, 23, 34, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            padding: 2rem;
            line-height: 1.5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }

        /* Progress Steps Wizard Row */
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--navy);
            color: white;
            padding: 1.25rem 2rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.5;
        }

        .step.active {
            opacity: 1;
            color: var(--green-soft);
        }

        .step-num {
            background-color: rgba(255, 255, 255, 0.15);
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.75rem;
        }

        .step.active .step-num {
            background-color: var(--green);
            color: white;
        }

        /* Interactive form card layout */
        .card {
            background: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background-color: var(--surface-alt);
            color: var(--text);
            transition: all 0.2s ease;
        }

        input[type="text"]:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--border-strong);
            background-color: var(--surface);
        }

        /* Styled drag and drop file field wrapper */
        .file-upload-wrapper {
            border: 2px dashed var(--border-strong);
            padding: 3rem 2rem;
            border-radius: var(--radius-md);
            text-align: center;
            background-color: var(--surface-alt);
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
        }

        .file-upload-wrapper:hover {
            border-color: var(--green);
            background-color: var(--green-soft);
        }

        .upload-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .upload-text-main {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0.25rem;
        }

        .upload-text-sub {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Action buttons footer layout */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 1.75rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--green);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--green-hover);
        }

        .btn-secondary {
            background-color: var(--surface-alt);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--border);
            color: var(--text);
        }

        /* Fidelity info banner */
        .fidelity-info-box {
            background-color: var(--blue-soft);
            border: 1px solid rgba(15, 111, 151, 0.15);
            border-left: 4px solid var(--blue-accent);
            padding: 1rem;
            border-radius: var(--radius-sm);
            color: var(--blue-accent);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
    </style>
</head>
<body>

<div class="container">
    
    <h1 class="header-title">Historical Import Wizard</h1>
    <p class="subtitle">Upload historical scorecards, GameChanger CSV files, or manual spreadsheets to persist career records [114].</p>

    <!-- Multi-stage navigation indicators -->
    <div class="wizard-steps">
        <div class="step active">
            <span class="step-num">1</span>
            <span>Upload CSV</span>
        </div>
        <div class="step">
            <span class="step-num">2</span>
            <span>Map Columns</span>
        </div>
        <div class="step">
            <span class="step-num">3</span>
            <span>Confirm & Sync</span>
        </div>
    </div>

    <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card">
            
            <!-- Fidelity details disclaimer -->
            <div class="fidelity-info-box">
                <span style="font-size: 1.25rem; font-weight: bold; line-height: 1;">ℹ️</span>
                <div>
                    <strong>Provenance Settings:</strong> Imported files are registered as <em>unverified</em> but persist cleanly inside player histories. Stating the correct source label ensures clear audit records [75, 83].
                </div>
            </div>

            <!-- Target Context Selector [183] -->
            <div class="form-group">
                <label for="context_id">Target Athlete Identity Profile</label>
                <select name="context_id" id="context_id" required>
                    <option value="" disabled selected>-- Select Claimed Athlete --</option>
                    <option value="90ff5d09-199c-447c-8a4c-f79f5db7d8ed">Jonny Damon (PLY-X29TQ8)</option>
                </select>
                <input type="hidden" name="context_type" value="player">
            </div>

            <div class="grid-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Source Label [142, 148] -->
                <div class="form-group">
                    <label for="source_label">Source Provenance Label</label>
                    <input type="text" name="source_label" id="source_label" placeholder="e.g., Summer 2025 GC Export" required>
                </div>

                <!-- Fidelity Selector [142, 150] -->
                <div class="form-group">
                    <label for="fidelity_level">Fidelity Level</label>
                    <select name="fidelity_level" id="fidelity_level" required>
                        <option value="season_totals" selected>Season Totals (Aggregated totals per year)</option>
                        <option value="box_score">Box Score (Game-by-game aggregate totals)</option>
                        <option value="game_log">Game Log (Pitch-by-pitch event chains)</option>
                    </select>
                </div>
            </div>

            <!-- File Upload Dropzone -->
            <div class="form-group">
                <label>Upload Spreadsheet File (.csv)</label>
                <div class="file-upload-wrapper" onclick="document.getElementById('csv_file').click()">
                    <span class="upload-icon">📁</span>
                    <span class="upload-text-main">Choose or Drag CSV File here</span>
                    <span class="upload-text-sub">Supports standardized spreadsheet columns (Max size: 5MB)</span>
                    <input type="file" name="csv_file" id="csv_file" style="display: none;" accept=".csv" required onchange="updateFileName(this)">
                </div>
                <div id="file-name-preview" style="font-size: 0.85rem; font-weight: 700; color: var(--green); text-align: center; display: none;"></div>
            </div>

            <input type="hidden" name="import_type" value="season_stats">

            <!-- Notes Field -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="notes">Uploader Administrative Notes</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Optional notes regarding the authenticity, league details, or scoring discrepancies of this dataset..."></textarea>
            </div>

            <!-- Actions Footer -->
            <div class="actions-bar">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Proceed to Column Mapping →</button>
            </div>

        </div>
    </form>

</div>

<script>
    function updateFileName(input) {
        const preview = document.getElementById('file-name-preview');
        if (input.files && input.files[0]) {
            preview.innerText = `Selected File: ${input.files[0].name}`;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }
</script>

</body>
</html>
