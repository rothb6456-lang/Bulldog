namespace App\Filament\Resources;

use App\Filament\Resources\ExerciseResource\Pages;
use App\Models\Exercise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExerciseResource extends Resource
{
    protected static ?string $model = Exercise::class;

    protected static ?string $navigationGroup = 'Momentum Training';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Exercise Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('canonical_name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('muscle_group')
                            ->options([
                                'Arms_Biceps' => 'Arms (Biceps)',
                                'Arms_Triceps' => 'Arms (Triceps)',
                                'Back' => 'Back / Pull',
                                'Chest' => 'Chest / Push',
                                'Shoulders' => 'Shoulders',
                                'Legs' => 'Legs / Lower Body',
                                'Core' => 'Core / Abs',
                                'Carry' => 'Carry / Grip',
                                'Cardio' => 'Cardio / Aerobic',
                                'Warmup' => 'Warmup / Mobility',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('movement_pattern')
                            ->placeholder('e.g. Curl, Extension, Horizontal Press, Pull, Squat/Lunge')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('category')
                            ->placeholder('e.g. Compound, Isolation, Carry, Core, Mobility')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('equipment_type')
                            ->placeholder('e.g. Dumbbells, Cable, Machine, Bodyweight')
                            ->maxLength(100),
                        Forms\Components\Toggle::make('is_unilateral')
                            ->label('Unilateral Movement?'),
                        Forms\Components\Toggle::make('is_timed')
                            ->label('Time-based / Duration Exercise?'),
                    ])->columns(2),

                Forms\Components\Section::make('Clinical & Safety Cues')
                    ->schema([
                        Forms\Components\Textarea::make('safety_notes')
                            ->label('Shoulder / Joint Safety Notes')
                            ->placeholder('e.g. Neutral grip well tolerated. Slight lean-back on pulldowns eliminates anterior capsule tightness.')
                            ->rows(3),
                        Forms\Components\Textarea::make('notes')
                            ->label('General Exercise Cues & Equipment Notes')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Name Aliases (Name Map)')
                    ->schema([
                        Forms\Components\Repeater::make('nameMaps')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('original_name')
                                    ->required()
                                    ->placeholder('e.g. DB Romanian Deadlift (RDL)'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Name Alias'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('canonical_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('muscle_group')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('movement_pattern')
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipment_type'),
                Tables\Columns\IconColumn::make('is_unilateral')
                    ->boolean()
                    ->label('Unilateral'),
                Tables\Columns\IconColumn::make('is_timed')
                    ->boolean()
                    ->label('Timed'),
                Tables\Columns\TextColumn::make('safety_notes')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->safety_notes),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('muscle_group')
                    ->options([
                        'Arms_Biceps' => 'Arms_Biceps',
                        'Arms_Triceps' => 'Arms_Triceps',
                        'Back' => 'Back',
                        'Chest' => 'Chest',
                        'Legs' => 'Legs',
                        'Carry' => 'Carry',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExercises::route('/'),
            'create' => Pages\CreateExercise::route('/create'),
            'edit' => Pages\EditExercise::route('/{record}/edit'),
        ];
    }
}