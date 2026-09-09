namespace App\Filament\Resources;

use App\Filament\Resources\TrainingSessionResource\Pages;
use App\Models\TrainingSession;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainingSessionResource extends Resource
{
    protected static ?string $model = TrainingSession::class;

    protected static ?string $navigationGroup = 'Momentum Training';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_date')
                    ->date()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('playerIdentity.display_name')
                    ->label('Athlete')
                    ->searchable(),
                Tables\Columns\TextColumn::make('workout_name')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('gym_location')
                    ->badge(),
                Tables\Columns\TextColumn::make('sets_count')
                    ->counts('sets')
                    ->label('Total Sets'),
                Tables\Columns\TextColumn::make('general_notes')
                    ->limit(40),
            ])
            ->defaultSort('session_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('gym_location'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingSessions::route('/'),
            'view' => Pages\ViewTrainingSession::route('/{record}'),
        ];
    }
}