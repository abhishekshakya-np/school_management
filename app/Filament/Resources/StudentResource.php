<?php

namespace App\Filament\Resources;

use App\Events\PromoteStudent;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers\GuardiansRelationManager;
use App\Models\Certificate;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /*
                // this is wizard section from episode 12 so check it again //
                Section::make('personal info')
                 ->description('Add student personal information')
                             ->schema([
                                 Wizard::make([
                                     Wizard\Step::make('Personal information')
                                         ->schema([
                                             TextInput::make('name')->required()->maxLength('255'),
                                             TextInput::make('student_id')->required()->minLength('4'),
                                         ])
                                         ->description('Enter your info')
                                         ->icon('heroicon-o-users'),
                                     Wizard\Step::make('Address')
                                         ->schema([
                                             TextInput::make('address_1'),
                                             TextInput::make('address_2'),
                                         ])
                                         ->description('Add your address')
                                         ->icon('heroicon-o-users'),
                                     Wizard\Step::make('School')
                                         ->schema([
                                             Select::make('standard_id')->required()->relationship('standard', 'name'),
                             ]),
                                     Section::make('Medical info')
                                         ->description('Add Medical information of student')
                                         ->schema([
                                             Repeater::make('vitals')
                                             ->schema([
                                                 Select::make('name')
                                                     ->options(config('sm_config.vitals'))
                                                     ->required(),
                                                 TextInput::make('value')
                                                     ->required()
                                                     ->maxLength(255),
                                             ])

                         ])
                        ->icon('heroicon-o-academic-cap')
                 ])
                    ->skippable(),
             ]);*/

                Section::make('personal info')
                    ->description('Add student personal information')
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')->required()->maxLength('255'),
                        TextInput::make('student_id')->required()->minLength('1'),
                        TextInput::make('address_1'),
                        TextInput::make('address_2'),
                        Select::make('standard_id')->required()->relationship('standard', 'name'),
                    ]),
                Section::make('Certificate')
                    ->description('Add student certificate info')
                    ->collapsible()
                    ->schema([
                        Repeater::make('certificates')
                            ->relationship()
                            ->schema([
                                Select::make('certificate_id')
                                    ->options(Certificate::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('description')
                    ])
                    ->columns(2)
            ]),
                Section::make('Medical info')
                    ->description('Add Medical information of student')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('vitals')
                            ->schema([
                                Select::make('name')
                                    ->options(config('sm_config.vitals'))
                                    ->required(),
                                TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                    ])
            ]);
    }


    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('standard.name')->searchable(),
            ])
            ->filters([
//               Filter::make('start')
//                   ->query(fn(Builder $query): Builder => $query->where('standard_id', 1)),
//                SelectFilter::make('standard_id')
//                ->options([
//                    1 => 'Standard 1',
//                    5 => 'Standard 5',
//                    9 => 'Standard 9',
//                ])->label('select the standard'),
                SelectFilter::make('All standard')->relationship('standard', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Promote')
                        ->action(function (Student $record) {
                            $record->standard_id = $record->standard_id + 1;
                            $record->save();
                        })
                        ->color('success')
                        ->requiresConfirmation(),
                    //    dump($record);
                    Tables\Actions\Action::make('Demote')->action(function (Student $record) {
                        if ($record->standard_id > 1) {
                            $record->standard_id = $record->standard_id + 1;
                            $record->save();
                        }
                    })->color('danger')->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('Promote all')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $records->each(function ($record) {
                            event(new PromoteStudent($record));
                        });
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            GuardiansRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }


//    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
//    {
//        return 'Search result:' . $record->name;
//    }


    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Name' => $record->name,
            'Standard' => $record->standard->name,
        ];
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('Edit')
                ->iconButton()
                ->icon('heroicon-s-pencil')
                ->url(static::getUrl('edit', ['record' => $record])),
            // Action::make('Delete')
            // ->iconButton()
            // ->icon('heroicon-s-eye')
            // ->url(static::getUrl('index'))
        ];
    }

}
