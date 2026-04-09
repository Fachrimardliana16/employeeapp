<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeCareerMovementResource\Pages;
use App\Models\EmployeeCareerMovement;
use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterSubDepartment;
use App\Models\MasterEmployeePosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeCareerMovementResource extends Resource
{
    protected static ?string $model = EmployeeCareerMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Promosi & Demosi';

    protected static ?string $modelLabel = 'Promosi & Demosi';

    protected static ?string $pluralModelLabel = 'Promosi & Demosi';

    protected static ?int $navigationSort = 304;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keputusan')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('decision_letter_number')
                            ->label('Nomor SK')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SK/HRD/PRO/001/2026'),

                        Forms\Components\DatePicker::make('movement_date')
                            ->label('Tanggal Efektif')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Pergerakan')
                            ->options([
                                'promotion' => 'Promosi',
                                'demotion' => 'Demosi',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(3),

                Forms\Components\Section::make('Data Pegawai')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = Employee::find($state);
                                    if ($employee) {
                                        $set('old_department_id', $employee->departments_id);
                                        $set('old_sub_department_id', $employee->sub_department_id);
                                        $set('old_position_id', $employee->employee_position_id);
                                    }
                                }
                            })
                            ->helperText('Pilih pegawai. Data lamanya akan terisi otomatis.'),

                        Forms\Components\Placeholder::make('current_info')
                            ->label('Informasi Saat Ini')
                            ->content(function (Forms\Get $get) {
                                $id = $get('employee_id');
                                if (!$id) return 'Pilih pegawai terlebih dahulu.';
                                $employee = Employee::with(['department', 'subDepartment', 'position'])->find($id);
                                if (!$employee) return 'Data tidak ditemukan.';

                                return new \Illuminate\Support\HtmlString('
                                    <div class="text-sm space-y-1">
                                        <div><strong>Bagian:</strong> ' . ($employee->department?->name ?? '-') . '</div>
                                        <div><strong>Sub Bagian:</strong> ' . ($employee->subDepartment?->name ?? '-') . '</div>
                                        <div><strong>Jabatan:</strong> ' . ($employee->position?->name ?? '-') . '</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ])->columns(2),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Posisi Lama (Sistem)')
                            ->description('Akan terisi otomatis berdasarkan data pegawai terpilih.')
                            ->schema([
                                Forms\Components\Select::make('old_department_id')
                                    ->label('Bagian Lama')
                                    ->relationship('oldDepartment', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('old_sub_department_id')
                                    ->label('Sub Bagian Lama')
                                    ->relationship('oldSubDepartment', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('old_position_id')
                                    ->label('Jabatan Lama')
                                    ->relationship('oldPosition', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                            ])->columnSpan(1),

                        Forms\Components\Section::make('Posisi Baru (Target)')
                            ->description('Tentukan jabatan dan bagian baru hasil promosi/demosi.')
                            ->schema([
                                Forms\Components\Select::make('new_department_id')
                                    ->label('Bagian Baru')
                                    ->relationship('newDepartment', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('new_sub_department_id', null)),
                                
                                Forms\Components\Select::make('new_sub_department_id')
                                    ->label('Sub Bagian Baru')
                                    ->relationship('newSubDepartment', 'name', fn (Builder $query, Forms\Get $get) => 
                                        $query->where('departments_id', $get('new_department_id'))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (Forms\Get $get) => !$get('new_department_id')),
                                
                                Forms\Components\Select::make('new_position_id')
                                    ->label('Jabatan Baru')
                                    ->relationship('newPosition', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ])->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Tambahan')
                    ->schema([
                        Forms\Components\FileUpload::make('doc_path')
                            ->label('Dokumen SK (PDF)')
                            ->disk('public')
                            ->directory('career-movements')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('users_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('decision_letter_number')
                    ->label('No. SK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promotion' => 'success',
                        'demotion' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'promotion' => 'PROMOSI',
                        'demotion' => 'DEMOSI',
                    }),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('movement_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('newPosition.name')
                    ->label('Jabatan Baru')
                    ->description(fn ($record) => $record->newDepartment?->name)
                    ->searchable(),

                Tables\Columns\IconColumn::make('doc_path')
                    ->label('SK')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->getStateUsing(fn ($record) => (bool) $record->doc_path),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'promotion' => 'Promosi',
                        'demotion' => 'Demosi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeCareerMovements::route('/'),
            'create' => Pages\CreateEmployeeCareerMovement::route('/create'),
            'view' => Pages\ViewEmployeeCareerMovement::route('/{record}'),
            'edit' => Pages\EditEmployeeCareerMovement::route('/{record}/edit'),
        ];
    }
}
