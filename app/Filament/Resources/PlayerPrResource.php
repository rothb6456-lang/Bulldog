namespace App\Filament\Resources;

use App\Filament\Resources\PlayerPrResource\Pages;
use App\Models\PlayerPr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlayerPrResource extends Resource
{
    protected static ?string $model = PlayerPr::class;

    protected static ?string $navigationGroup = 'Momentum Training';
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('player_identity_id')
                    ->relationship('playerIdentity', 'display_name')
                    ->required(),
                Forms\Components\Select::make('exercise_id')
                    ->relationship('exercise', 'canonical_name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('pr_type')
                    ->options([
                        'Heaviest Weight' => 'Heaviest Weight',
                        'Longest Duration' => 'Longest Duration',
                        'Max Reps' => 'Max Reps',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('pr_value')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('pr_unit')
                    ->default('lbs')
                    ->required(),
                Forms\Components\DatePicker::make('pr_date')
                    ->required(),
                Forms\Components\TextInput::make('set_details')
                    ->placeholder('e.g. 8 reps @ 170 lbs'),
                Forms\Components\Textarea::make('notes')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('playerIdentity.display_name')
                    ->label('Athlete')
                    ->sortable(),
                Tables\Columns\TextColumn::make('exercise.canonical_name')
                    ->label('Exercise')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('pr_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('pr_value')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => "{$record->pr_value} {$record->pr_unit}"),
                Tables\Columns\TextColumn::make('pr_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('set_details'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(30),
            ])
            ->defaultSort('pr_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('pr_type'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayerPrs::route('/'),
        ];
    }
}