<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupermarketResource\Pages;
use App\Filament\Resources\SupermarketResource\RelationManagers;
use App\Models\Supermarket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section as ComponentsSection;
use Illuminate\Support\Facades\Auth;

class SupermarketResource extends Resource
{
    protected static ?string $model = Supermarket::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'المتجر';
    protected static ?string $navigationGroup = 'إدارة المتجر';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        ComponentsSection::make('بيانات المتجر')->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('اسم المتجر')
                                ->required()
                                ->rules([
                                    'required',
                                    'string',
                                    'max:50',
                                    'regex:/^[\p{L}\s]+$/u',
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if (preg_match('/<[^>]+>/', $value)) {
                                                $fail('اسم المتجر لا يمكن أن يحتوي على سكربتات أو علامات HTML.');
                                            }
                                        };
                                    },
                                ])
                                ->validationMessages([
                                    'required' => 'اسم المتجر مطلوب.',
                                    'string' => 'يجب أن يكون اسم المتجر نصًا.',
                                    'max' => 'يجب ألا يتجاوز اسم المتجر 50 حرفًا.',
                                    'regex' => 'اسم المتجر يجب أن يحتوي فقط على أحرف عربية، مسافات، أو شرطات.',
                                ])
                                ->suffixIcon('heroicon-m-building-storefront')
                                ->suffixIconColor('primary')
                                ->maxLength(50),

                            Forms\Components\TextInput::make('position')
                                ->label('الموقع')
                                ->prefix('حماة - حي ')
                                ->required()
                                ->default('حماة - حي ')
                                ->dehydrateStateUsing(fn ($state) => str_starts_with($state, 'حماة - ') ? $state : 'حماة - ' . $state)
                                ->placeholder('حماة - حي البرازية')
                                ->rules([
                                    'required',
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            $userInput = str_replace('حماة - حي ', '', $value);
                                            if (empty(trim($userInput))) {
                                                $fail('يرجى إدخال اسم الحي بعد "حماة - حي ".');
                                            }
                                            if (stripos($userInput, 'حماه') !== false || stripos($userInput, 'حماة') !== false) {
                                                $fail('لا يمكن أن يحتوي الموقع على كلمة "حماه" أو "حماة" في الجزء المدخل.');
                                            }
                                        };
                                    },
                                    'regex:/^حماة - حي [\p{Arabic}\s\-]+$/u',
                                ])
                                ->validationMessages([
                                    'required' => 'الموقع مطلوب.',
                                    'regex' => 'الموقع يجب أن يحتوي فقط على حروف عربية، مسافات، أو شرطات بعد "حماة - حي ".',
                                ])
                                ->maxLength(100),

                            Forms\Components\TextInput::make('email')
                                ->label('البريد الإلكتروني')
                                ->email()
                                ->prefix('com.')
                                //
                                ->suffixIcon('heroicon-m-globe-alt')
                                ->suffixIconColor('success')

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

                            Forms\Components\TextInput::make('phone_number')
                                ->label('رقم الهاتف')
                                ->required()
                                ->rules([
                                    'required',
                                    'regex:/^09[0-9]{8}$/',
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            if (preg_match('/<[^>]+>/', $value)) {
                                                $fail('رقم الهاتف لا يمكن أن يحتوي على سكربتات أو علامات HTML.');
                                            }
                                        };
                                    },
                                ])
                                ->validationMessages([
                                    'required' => 'رقم الهاتف مطلوب.',
                                    'regex' => 'رقم الهاتف يجب أن يبدأ بـ 09 ويتكون من 10 أرقام.',
                                ])
                                ->suffixIcon('heroicon-m-phone')
                                ->suffixIconColor('success')
                                ->maxLength(10),

                            Forms\Components\Hidden::make('user_id')
                                ->default(Auth::id()),
                        ])->columns(2),

                        Forms\Components\Group::make()
                            ->schema([
                                ComponentsSection::make('صورة المتجر')->schema([
                                    Forms\Components\FileUpload::make('image')
                                        ->label('صورة المتجر')
                                        ->disk('public')
                                        ->directory('supermarkets')
                                        ->image()
                                        ->nullable()
                                        ->rules(['nullable', 'image', 'max:2048'])
                                        ->validationMessages([
                                            'image' => 'يجب أن يكون الملف صورة.',
                                            'max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت.',
                                        ]),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                /** @var \Illuminate\Support\Facades\Auth $auth */
                $auth = Auth::getFacadeRoot();
                /** @var \App\Models\User $user */
                $user = $auth->user();
                $query = Supermarket::with('user');
                if ($auth->check() && $user->hasRole('vendor')) {
                    $query->where('user_id', $user->id);
                }
                return $query;
            })
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('صورة المتجر')
                    ->circular()
                    ->size(50)
                    ->disk('public'),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المتجر')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('الموقع'),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('رقم الهاتف'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->label('الموقع')
                    ->options([
                        'حماة - حي البرازية' => 'حماة - حي البرازية',
                        'حماة - حي الغدير' => 'حماة - حي الغدير',
                        'حماة - حي العليلي' => 'حماة - حي العليلي',
                    ]),

                Tables\Filters\Filter::make('has_products')
                    ->label('لديه منتجات')
                    ->query(fn (Builder $query) => $query->whereHas('products')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('عرض متجر'),
                Tables\Actions\EditAction::make()->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    return Auth::check() && $user->hasRole(['admin', 'vendor']);
                }),
                Tables\Actions\DeleteAction::make()->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    return Auth::check() && $user->hasRole('admin');
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return Auth::check() && $user->hasRole('admin');
                    }),
                ]),
            ])
            ->emptyStateHeading('لا توجد متاجر بعد')
            ->emptyStateDescription('اضغط على "إنشاء" لإضافة متجر جديد.');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupermarkets::route('/'),
            'create' => Pages\CreateSupermarket::route('/create'),
            'edit' => Pages\EditSupermarket::route('/{record}/edit'),
            'view' => Pages\ViewSupermarket::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // return Auth::check() && $user->hasRole('admin');
        // return Auth::check() && ($user->hasRole('admin') || ($user->hasRole('vendor') && !$user->supermarket));
        return Auth::check() && $user->hasRole(['admin', 'vendor']);



    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return Auth::check() && ($user->hasRole('admin') || ($user->hasRole('vendor') && !$user->supermarket));
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return Auth::check() && $user->hasRole(['admin', 'vendor']);
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return Auth::check() && $user->hasRole('admin');
    }

    public static function canView($record): bool
    {
        if (!Auth::check()) {
            return false;
        }
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->hasRole('vendor') && $user->id === $record->user_id;
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return Auth::check() && $user->hasRole(['admin', 'vendor']);
    }

    public static function getLabel(): string
    {
        return __('متجر');
    }

    public static function getPluralLabel(): string
    {
        return '🏪المتاجر';
    }
}
