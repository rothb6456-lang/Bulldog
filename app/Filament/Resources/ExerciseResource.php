<?php

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

    // Movement pattern and exercise category vocabularies, confirmed against the
    // real 102-exercise library (tbl_ExcerciseLibrary.csv) rather than invented.
    protected static function movementPatternOptions(): array
    {
        $v = ['Cardio', 'Carry', 'Core', 'Hinge', 'Hold', 'Isolation', 'Mobility', 'Pull', 'Push', 'Squat'];
        return array_combine($v, $v);
    }

    protected static function exerciseCategoryOptions(): array
    {
        $v = ['Cardio', 'Carry', 'Compound', 'Core', 'Hold', 'Isolation', 'Rehab', 'Warmup'];
        return array_combine($v, $v);
    }

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
                        Forms\Components\Select::make('movement_pattern')
                            ->options(self::movementPatternOptions())
                            ->searchable(),
                        Forms\Components\Select::make('exercise_category')
                            ->options(self::exerciseCategoryOptions())
                            ->required(),
                        Forms\Components\Select::make('equipment_id')
                            ->relationship('equipment', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('laterality')
                            ->options([
                                'bilateral' => 'Bilateral',
                                'unilateral' => 'Unilateral',
                                'alternating' => 'Alternating',
                            ])
                            ->default('bilateral')
                            ->required(),
                        Forms\Components\Toggle::make('is_time_based')
                            ->label('Time-based / Duration Exercise?'),
                        Forms\Components\Toggle::make('is_distance_based')
                            ->label('Distance-based Exercise?'),
                    ])->columns(2),

                Forms\Components\Section::make('Musculature & Body Structures')
                    ->description('Every muscle, tendon, ligament, or functional structure this exercise involves. The seeded library tags these as primary/secondary automatically — new exercises added here default to primary; adjust secondary emphasis via a follow-up edit if needed.')
                    ->schema([
                        Forms\Components\Select::make('bodyStructures')
                            ->relationship('bodyStructures', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->bodyStructures()->sync(
                                    collect($state)->mapWithKeys(fn ($id) => [$id => ['role' => 'primary']])
                                );
                            }),
                    ]),

                Forms\Components\Section::make('Clinical & Safety Cues')
                    ->schema([
                        Forms\Components\Textarea::make('shoulder_safety_notes')
                            ->label('Shoulder / Joint Safety Notes')
                            ->placeholder('e.g. Neutral grip well tolerated. Slight lean-back on pulldowns eliminates anterior capsule tightness.')
                            ->rows(3),
                        Forms\Components\TextInput::make('preferred_replacements')
                            ->label('Preferred Replacements')
                            ->placeholder('e.g. Dumbbell Romanian Deadlift; Cable Pull-Through')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('exercise_category')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('movement_pattern')
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bodyStructures.name')
                    ->label('Body Structures')
                    ->badge()
                    ->limitList(3),
                Tables\Columns\IconColumn::make('is_time_based')
                    ->boolean()
                    ->label('Timed'),
                Tables\Columns\IconColumn::make('is_distance_based')
                    ->boolean()
                    ->label('Distance'),
                Tables\Columns\TextColumn::make('shoulder_safety_notes')
                    ->label('Safety Notes')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->shoulder_safety_notes),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exercise_category')
                    ->options(self::exerciseCategoryOptions()),
                Tables\Filters\SelectFilter::make('movement_pattern')
                    ->options(self::movementPatternOptions()),
                Tables\Filters\SelectFilter::make('equipment_id')
                    ->relationship('equipment', 'name')
                    ->label('Equipment'),
                Tables\Filters\SelectFilter::make('bodyStructures')
                    ->relationship('bodyStructures', 'name')
                    ->label('Body Structure'),
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
