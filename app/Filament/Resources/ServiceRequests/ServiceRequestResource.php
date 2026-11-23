<?php

namespace App\Filament\Resources\ServiceRequests;

use App\Enums\ServiceRequestStatus;
use App\Filament\Resources\ServiceRequests\Pages\EditServiceRequest;
use App\Filament\Resources\ServiceRequests\Pages\ListServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\ViewServiceRequest;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestWorkflow;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $navigationLabel = 'طلبات الخدمات';

    protected static ?string $modelLabel = 'طلب خدمة';

    protected static ?string $pluralModelLabel = 'طلبات الخدمات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الخدمات';

    protected static ?int $navigationSort = 3;

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return collect(ServiceRequestStatus::cases())
            ->mapWithKeys(fn (ServiceRequestStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الطلب')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference')->label('رقم الطلب')->disabled(),
                        Select::make('user_id')
                            ->label('العميل')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->disabled(),
                        Select::make('service_category_id')
                            ->label('نوع الخدمة')
                            ->relationship('category', 'name_ar')
                            ->disabled(),
                        Select::make('service_provider_id')
                            ->label('مقدم الخدمة')
                            ->relationship('provider', 'display_name')
                            ->searchable()
                            ->disabled(),
                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name_ar')
                            ->disabled(),
                        Select::make('city_id')
                            ->label('المدينة')
                            ->relationship('city', 'name_ar')
                            ->disabled(),
                        TextInput::make('title')->label('عنوان الطلب')->disabled()->columnSpanFull(),
                        Textarea::make('description')->label('الوصف')->rows(4)->disabled()->columnSpanFull(),
                        TextInput::make('address')->label('العنوان التفصيلي')->disabled()->columnSpanFull(),
                        TextInput::make('contact_phone')->label('رقم التواصل')->disabled(),
                        DatePicker::make('preferred_date')->label('الموعد المفضل')->disabled(),
                        TimePicker::make('preferred_time')->label('الوقت المفضل')->disabled(),
                    ]),

                Section::make('متابعة الإدارة')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('الحالة الحالية')
                            ->options(self::statusOptions())
                            ->disabled(),
                        TextInput::make('quoted_price')
                            ->label('السعر المتفق عليه')
                            ->numeric()
                            ->prefix('ج.م '),
                        Textarea::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الطلب')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reference')->label('رقم الطلب'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (ServiceRequestStatus $state): string => $state->label())
                            ->color(fn (ServiceRequestStatus $state): string => $state->color()),
                        TextEntry::make('customer.name')->label('العميل')->placeholder('-'),
                        TextEntry::make('customer.phone')->label('هاتف العميل')->placeholder('-'),
                        TextEntry::make('category.name_ar')->label('نوع الخدمة')->placeholder('-'),
                        TextEntry::make('provider.display_name')->label('مقدم الخدمة')->placeholder('لم يُسند'),
                        TextEntry::make('governorate.name_ar')->label('المحافظة')->placeholder('-'),
                        TextEntry::make('city.name_ar')->label('المدينة')->placeholder('-'),
                        TextEntry::make('title')->label('العنوان')->columnSpanFull(),
                        TextEntry::make('description')->label('الوصف')->columnSpanFull(),
                        TextEntry::make('address')->label('العنوان التفصيلي')->columnSpanFull(),
                        TextEntry::make('contact_phone')->label('رقم التواصل'),
                        TextEntry::make('preferred_date')->label('الموعد المفضل')->date()->placeholder('-'),
                        TextEntry::make('preferred_time')->label('الوقت المفضل')->placeholder('-'),
                        TextEntry::make('quoted_price')->label('السعر المتفق عليه')->money('EGP')->placeholder('-'),
                        TextEntry::make('images_count')
                            ->label('عدد الصور')
                            ->getStateUsing(fn (ServiceRequest $record): int => $record->images()->count()),
                    ]),

                Section::make('المراحل الزمنية')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
                        TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime()->placeholder('-'),
                        TextEntry::make('accepted_at')->label('تاريخ القبول')->dateTime()->placeholder('-'),
                        TextEntry::make('started_at')->label('تاريخ البدء')->dateTime()->placeholder('-'),
                        TextEntry::make('completed_at')->label('تاريخ الاكتمال')->dateTime()->placeholder('-'),
                        TextEntry::make('cancelled_at')->label('تاريخ الإلغاء')->dateTime()->placeholder('-'),
                        TextEntry::make('admin_notes')->label('ملاحظات الإدارة')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('cancellation_reason')->label('سبب الإلغاء')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('category.name_ar')
                    ->label('نوع الخدمة')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('provider.display_name')
                    ->label('مقدم الخدمة')
                    ->placeholder('لم يُسند'),

                TextColumn::make('governorate.name_ar')
                    ->label('المحافظة')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (ServiceRequestStatus $state): string => $state->label())
                    ->color(fn (ServiceRequestStatus $state): string => $state->color())
                    ->sortable(),

                TextColumn::make('preferred_date')
                    ->label('الموعد')
                    ->date()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(self::statusOptions()),

                SelectFilter::make('service_category_id')
                    ->label('نوع الخدمة')
                    ->relationship('category', 'name_ar')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('governorate_id')
                    ->label('المحافظة')
                    ->relationship('governorate', 'name_ar')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('service_provider_id')
                    ->label('مقدم الخدمة')
                    ->relationship('provider', 'display_name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('assign')
                    ->label('إسناد')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (ServiceRequest $record): bool => $record->status->isOpen())
                    ->schema(fn (ServiceRequest $record): array => [
                        Select::make('provider_id')
                            ->label('مقدم الخدمة')
                            ->options(
                                ServiceProvider::query()
                                    ->available()
                                    ->forCategory((int) $record->service_category_id)
                                    ->orderBy('display_name')
                                    ->pluck('display_name', 'id')
                                    ->all()
                            )
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (ServiceRequest $record, array $data): void {
                        self::run(function () use ($record, $data): void {
                            $provider = ServiceProvider::query()->findOrFail($data['provider_id']);

                            app(ServiceRequestWorkflow::class)->assign($record, $provider, auth()->user());
                        }, 'تم إسناد الطلب إلى مقدم الخدمة.');
                    }),

                Action::make('accept')
                    ->label('قبول')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceRequest $record): bool => $record->status === ServiceRequestStatus::Assigned)
                    ->action(function (ServiceRequest $record): void {
                        self::run(fn () => app(ServiceRequestWorkflow::class)->accept($record, auth()->user()), 'تم قبول الطلب.');
                    }),

                Action::make('start')
                    ->label('بدء التنفيذ')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceRequest $record): bool => $record->status === ServiceRequestStatus::Accepted)
                    ->action(function (ServiceRequest $record): void {
                        self::run(fn () => app(ServiceRequestWorkflow::class)->start($record, auth()->user()), 'تم بدء تنفيذ الطلب.');
                    }),

                Action::make('complete')
                    ->label('إكمال')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceRequest $record): bool => $record->status === ServiceRequestStatus::InProgress)
                    ->action(function (ServiceRequest $record): void {
                        self::run(fn () => app(ServiceRequestWorkflow::class)->complete($record, auth()->user()), 'تم إكمال الطلب.');
                    }),

                Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ServiceRequest $record): bool => $record->status->isOpen())
                    ->schema([
                        Textarea::make('reason')->label('سبب الإلغاء')->rows(2),
                    ])
                    ->action(function (ServiceRequest $record, array $data): void {
                        self::run(
                            fn () => app(ServiceRequestWorkflow::class)->cancel($record, auth()->user(), $data['reason'] ?? null),
                            'تم إلغاء الطلب.'
                        );
                    }),
            ])
            ->toolbarActions([]);
    }

    private static function run(callable $callback, string $successMessage): void
    {
        try {
            $callback();

            Notification::make()->title($successMessage)->success()->send();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title($exception->validator->errors()->first())
                ->danger()
                ->send();
        } catch (AuthorizationException) {
            Notification::make()->title('غير مصرح بهذا الإجراء.')->danger()->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceRequests::route('/'),
            'view' => ViewServiceRequest::route('/{record}'),
            'edit' => EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
