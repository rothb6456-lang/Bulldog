namespace App\Filament\Resources;

use App\Filament\Resources\PlayerTrainingAssumptionResource\Pages;
use App\Models\PlayerTrainingAssumption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlayerTrainingAssumptionResource extends Resource
{
    protected static ?string $model = PlayerTrainingAssumption::class;

    protected static ?string $navigationGroup = 'Momentum Training';
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('player_identity_id')
                    ->relationship('playerIdentity', 'display_name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('category')
                    ->options([
                        'Shoulder' => 'Shoulder / Joint',
                        'Grip' => 'Grip / Forearm',
                        'Movement Pattern' => 'Movement Pattern',
                        'Programming' => 'Programming / Tempo',
                        'Recovery' => 'Recovery / Nutrition',
                        'Equipment' => 'Equipment Difference',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('confidence')
                    ->options([
                        'High' => 'High',
                        'Medium' => 'Medium',
                        'Low' => 'Low',
                    ])
                    ->default('High'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active Guardrail',
                        'resolved' => 'Resolved / Archived',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Textarea::make('supporting_evidence')
                    ->rows(2),
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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('confidence')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'gray' => 'resolved',
                    ]),
                Tables\Columns\TextColumn::make('last_validated_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active Guardrails',
                        'resolved' => 'Resolved',
                    ]),
                Tables\Filters\SelectFilter::make('category'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayerTrainingAssumptions::route('/'),
        ];
    }
}