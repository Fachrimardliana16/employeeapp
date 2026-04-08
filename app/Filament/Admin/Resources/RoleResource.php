<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Role')
                    ->description('Tentukan nama role yang akan digunakan.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Nama Role'),
                    ]),
                
                Forms\Components\Tabs::make('Permissions')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Akses Panel')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Forms\Components\CheckboxList::make('panel_permissions')
                                    ->options(\Spatie\Permission\Models\Permission::where('name', 'like', 'access_%')->pluck('name', 'name'))
                                    ->columns(3)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->label('Centang panel mana saja yang dapat diakses oleh role ini.')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->permissions->where('name', 'like', 'access_%')->pluck('name')->toArray() ?? []))
                                    ->saveRelationshipsUsing(function ($record, $state, $form) {
                                        // Collect all permissions from virtual fields in the form
                                        $formState = $form->getRawState();
                                        $allPermissions = $state ?? [];
                                        
                                        foreach ($formState as $key => $value) {
                                            if (str_starts_with($key, 'resource_perms_') && is_array($value)) {
                                                $allPermissions = array_merge($allPermissions, $value);
                                            }
                                        }
                                        
                                        $record->syncPermissions(array_filter($allPermissions));
                                    }),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Akses Fitur & Menu')
                            ->icon('heroicon-o-lock-closed')
                            ->schema(static::getResourcePermissionSchema()),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    /**
     * Helper to generate dynamic grouped permissions schema
     */
    protected static function getResourcePermissionSchema(): array
    {
        // Get all permissions that are NOT panel access
        $permissions = \Spatie\Permission\Models\Permission::where('name', 'not like', 'access_%')->get();
        
        // Map resources to panels by scanning filesystem
        $resourceToPanel = [];
        $panels = ['Admin', 'Employee', 'User'];
        foreach ($panels as $panel) {
            $path = app_path('Filament/' . $panel . '/Resources');
            if (file_exists($path)) {
                $files = glob($path . '/*.php');
                foreach ($files as $file) {
                    $className = basename($file, '.php');
                    if (str_ends_with($className, 'Resource')) {
                        $slug = str_contains($className, 'Activity') ? 'activity_log' : strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', str_replace('Resource', '', $className)));
                        $resourceToPanel[$slug] = $panel;
                    }
                }
            }
        }

        $groupedByPanel = [];
        foreach ($permissions as $permission) {
            $resource = '';
            if (str_starts_with($permission->name, 'view_any_')) {
                $resource = str_replace('view_any_', '', $permission->name);
            } else {
                foreach (['view_', 'create_', 'update_', 'delete_', 'restore_', 'force_delete_'] as $prefix) {
                    if (str_starts_with($permission->name, $prefix)) {
                        $resource = str_replace($prefix, '', $permission->name);
                        break;
                    }
                }
            }
            
            if (!$resource) continue;

            $panel = $resourceToPanel[$resource] ?? 'Lainnya';
            $groupedByPanel[$panel][$resource][] = $permission->name;
        }

        $panelSections = [];
        
        // Sort panels: Admin, Employee, User, then Others
        $sortOrder = ['Admin' => 1, 'Employee' => 2, 'User' => 3, 'Lainnya' => 4];
        uksort($groupedByPanel, fn($a, $b) => ($sortOrder[$a] ?? 99) <=> ($sortOrder[$b] ?? 99));

        foreach ($groupedByPanel as $panelName => $resources) {
            $resourceSections = [];
            foreach ($resources as $resource => $perms) {
                $label = ucwords(str_replace('_', ' ', $resource));
                $fieldName = "resource_perms_{$resource}";
                
                $resourceSections[] = Forms\Components\Section::make($label)
                    ->schema([
                        Forms\Components\CheckboxList::make($fieldName)
                            ->options(array_combine($perms, $perms))
                            ->columns(2)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->label('')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->permissions->whereIn('name', $perms)->pluck('name')->toArray() ?? [])),
                    ])
                    ->collapsible()
                    ->compact()
                    ->columnSpan(1);
            }

            $panelLabel = match($panelName) {
                'Admin' => 'Panel Admin',
                'Employee' => 'Panel Kepegawaian',
                'User' => 'Portal Pegawai',
                default => 'Fitur Lainnya',
            };

            $panelSections[] = Forms\Components\Section::make($panelLabel)
                ->description("Hak akses untuk menu di {$panelLabel}.")
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema($resourceSections)
                ])
                ->collapsible()
                ->collapsed() // Keep it clean by default
                ->columnSpanFull();
        }

        return [
            Forms\Components\Placeholder::make('Resource Permissions Info')
                ->content('Pilih panel di bawah ini untuk mengatur hak akses menu masing-masing.')
                ->columnSpanFull(),
            ...$panelSections
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Role Name'),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users Count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
