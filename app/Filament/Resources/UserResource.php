<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\Supermarket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Spatie\Permission\Models\Role;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'المستخدمين';

    protected static ?string $navigationGroup = 'إدارة عامة';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255)
                    ->rules([
                        'required',
                        'string',
                        'max:50',
                        'regex:/^[\p{Arabic}\s\-]+$/u',
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (preg_match('/<[^>]+>/', $value)) {
                                    $fail('اسم  لا يمكن أن يحتوي على سكربتات أو علامات .');
                                }
                            };
                        },
                    ])
                        ->validationMessages([
                        'required' => 'الاسم مطلوب.',
                        'string' => 'يجب أن يكون الاسم نصًا.',
                        'min' => 'يجب أن يحتوي الاسم على 3 أحرف على الأقل.',
                        'max' => 'يجب ألا يتجاوز الاسم 255 حرفًا.',
                        'regex' => 'الاسم يجب أن يحتوي فقط على حروف ومسافات (الأرقام والشرطات غير مسموح بها).',

                ])
                ->validationMessages([
                    'required' => 'اسم  مطلوب.',
                    'string' => 'يجب أن يكون اسم  نصًا.',
                    'max' => 'يجب ألا يتجاوز اسم  50 حرفًا.',
                    'regex' => 'اسم  يجب أن يحتوي فقط على أحرف عربية، مسافات، أو شرطات.',
                ]),

                Forms\Components\TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->required()
                ->rules([
                    'required',
                    'email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                    function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            if (preg_match('/<[^>]+>/', $value)) {
                                $fail('البريد الإلكتروني لا يمكن أن يحتوي على سكربتات أو علامات HTML.');
                            }
                            if (preg_match('/[\'";|\\\\\/]/', $value)) {
                                $fail('البريد الإلكتروني لا يمكن أن يحتوي على رموز مثل \' " ; | \ /.');
                            }
                        };
                    },
                ])
                ->validationMessages([
                    'required' => 'البريد الإلكتروني مطلوب.',
                    'email' => 'يجب أن يكون البريد الإلكتروني صالحًا.',
                    'regex' => 'صيغة البريد الإلكتروني غير صحيحة.',
                ])
                ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateUser || $livewire instanceof Pages\EditUser)
                    ->rules(['min:8'])
                    ->validationMessages([
                        'required' => 'كلمة المرور مطلوبة.',
                        'min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.',
                    ]),

                Forms\Components\Select::make('roles')
                    ->label('الدور')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->options(fn () => Role::whereIn('name', ['admin', 'vendor'])->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $hasVendorRole = in_array(Role::where('name', 'vendor')->first()?->id, $state ?? []);
                        $set('show_supermarket_field', $hasVendorRole);
                    })
                    ->rules(['array', 'min:1'])
                    ->validationMessages([
                        'required' => 'يجب اختيار دور واحد على الأقل.',
                        'array' => 'يجب أن تكون القيمة مصفوفة.',
                        'min' => 'يجب اختيار دور واحد على الأقل.',
                    ]),

                Forms\Components\Select::make('supermarket')
                    ->label('المتجر')
                    ->options(fn () => Supermarket::whereNull('user_id')->orWhere('user_id', auth()->id())->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get) => $get('show_supermarket_field') === true)
                    ->required(fn (callable $get) => $get('show_supermarket_field') === true)
                    ->afterStateHydrated(function ($component, $record) {
                        if ($record && $record->supermarket) {
                            $component->state($record->supermarket->id);
                        }
                    })
                    ->rules(['nullable', 'exists:supermarkets,id'])
                    ->validationMessages([
                        'required' => 'يجب اختيار متجر عند اختيار دور التاجر.',
                        'exists' => 'المتجر المختار غير موجود.',
                    ])
                    ->saveRelationshipsUsing(function ($record, $state) {
                        // إلغاء ربط المتاجر القديمة بالمستخدم
                        Supermarket::where('user_id', $record->id)->update(['user_id' => null]);

                        // ربط المتجر الجديد
                        if ($state) {
                            Supermarket::where('id', $state)->update(['user_id' => $record->id]);
                        }
                    }),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with(['supermarket', 'roles']))
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('الدور')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->roles->pluck('name')->implode(',')),

                TextColumn::make('supermarket.name')
                    ->label('المتجر')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                    Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                ->label('الدور')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('لا يوجد مستخدمين بعد')
            ->emptyStateDescription('اضغط على "إنشاء" لإضافة مستخدم جديد.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function getLabel(): ?string
    {
        return 'مستخدم';
    }

    public static function getPluralLabel(): ?string
    {
        return ' 💻 المستخدمين';
    }
}
