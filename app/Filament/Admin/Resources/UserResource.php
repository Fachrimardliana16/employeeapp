<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function form(Form $form): Form
    {
        $hasUsername = self::hasUsersColumn('username');
        $hasRoleTables = self::hasRoleTables();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->description('Lengkapi data profil pengguna di sini.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('username')
                                    ->label('Username')
                                    ->required($hasUsername)
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->visible($hasUsername),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->maxLength(255),
                                Forms\Components\Select::make('role')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->label('Role')
                                    ->multiple()
                                    ->visible($hasRoleTables),
                            ]),
                    ]),
                Forms\Components\Section::make('Verifikasi & Status')
                    ->description('Kelola status verifikasi akun pengguna.')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Terverifikasi')
                            ->helperText('Hanya pengguna terverifikasi yang dapat login ke aplikasi.')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Hanya pengguna aktif yang dapat login.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $hasUsername = self::hasUsersColumn('username');
        $hasRoleTables = self::hasRoleTables();

        $baseColumns = [
            'id',
            'name',
            'email',
            'is_verified',
            'is_active',
            'email_verified_at',
            'created_at',
        ];

        if ($hasUsername) {
            $baseColumns[] = 'username';
        }

        $tableColumns = [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->sortable(),
        ];

        if ($hasUsername) {
            $tableColumns[] = Tables\Columns\TextColumn::make('username')
                ->label('Username')
                ->searchable()
                ->sortable();
        }

        if ($hasRoleTables) {
            $tableColumns[] = Tables\Columns\TextColumn::make('roles.name')
                ->label('Peran')
                ->badge()
                ->searchable(false);
        }

        $tableColumns[] = Tables\Columns\IconColumn::make('is_verified')
            ->label('Terverifikasi')
            ->boolean()
            ->sortable();

        $tableColumns[] = Tables\Columns\IconColumn::make('is_active')
            ->label('Aktif')
            ->boolean()
            ->sortable();

        $tableColumns[] = Tables\Columns\TextColumn::make('email_verified_at')
            ->label('Diverifikasi Pada')
            ->dateTime('d/m/Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $tableColumns[] = Tables\Columns\TextColumn::make('created_at')
            ->label('Dibuat Pada')
            ->dateTime('d/m/Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->deferLoading()
            ->modifyQueryUsing(fn (Builder $query) => $query->select($baseColumns))
            ->columns($tableColumns)
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Status Verifikasi'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\Action::make('verify')
                        ->label('Verifikasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (User $record) => $record->update(['is_verified' => true]))
                        ->visible(fn (User $record) => !$record->is_verified),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_selected')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_verified' => true])),
                ])->label('Hapus yang Dipilih'),
            ]);
    }

    public static function getRelations(): array
    {
        if (!self::safeHasTable('activity_log')) {
            return [];
        }

        return [ActivitylogRelationManager::class];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if (self::hasRoleTables()) {
            $query->with('roles:id,name');
        }

        return $query;
    }

    protected static function hasUsersColumn(string $column): bool
    {
        return Cache::remember("users_has_column_{$column}", now()->addMinutes(30), function () use ($column): bool {
            return self::safeHasColumn('users', $column);
        });
    }

    protected static function hasRoleTables(): bool
    {
        return Cache::remember('users_has_role_tables', now()->addMinutes(30), function (): bool {
            return self::safeHasTable('roles') && self::safeHasTable('model_has_roles');
        });
    }

    protected static function safeHasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable $e) {
            return false;
        }
    }

    protected static function safeHasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
