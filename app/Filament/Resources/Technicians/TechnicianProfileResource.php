<?php

namespace App\Filament\Resources\Technicians;

use App\Filament\Resources\Technicians\Pages\CreateTechnicianProfile;
use App\Filament\Resources\Technicians\Pages\EditTechnicianProfile;
use App\Filament\Resources\Technicians\Pages\ListTechnicianProfiles;
use App\Models\TechnicianProfile;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TechnicianProfileResource extends Resource
{
    protected static ?string $model = TechnicianProfile::class;
    protected static ?string $recordTitleAttribute = 'display_name';
    protected static ?string $navigationLabel = 'فنيو الصيانة';
    protected static ?string $modelLabel = 'فني';
    protected static ?string $pluralModelLabel = 'فنيو الصيانة';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الفني والاعتماد')->columns(2)->schema([
                Select::make('user_id')->label('حساب المستخدم')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))->searchable()->required()->unique(ignoreRecord: true),
                TextInput::make('display_name')->label('الاسم الظاهر')->required()->maxLength(150),
                TextInput::make('professional_title')->label('المسمى المهني')->maxLength(150),
                TextInput::make('years_experience')->label('سنوات الخبرة')->numeric()->minValue(0)->maxValue(60)->required(),
                Select::make('verification_status')->label('حالة الاعتماد')->options(['pending'=>'قيد المراجعة','approved'=>'معتمد','rejected'=>'مرفوض'])->required(),
                Select::make('availability_status')->label('التوفر')->options(['available'=>'متاح','busy'=>'مشغول','suspended'=>'موقوف'])->required(),
                Textarea::make('bio')->label('نبذة')->rows(4)->columnSpanFull(),
                Textarea::make('approval_notes')->label('ملاحظات الإدارة')->rows(3)->columnSpanFull(),
                Repeater::make('specializations')->label('تخصصات الفني')->relationship()->schema([
                    Select::make('service_category_id')->label('التخصص')->options(fn () => \\App\\Models\\ServiceCategory::active()->orderBy('name_ar')->pluck('name_ar', 'id'))->searchable()->required(),
                    TextInput::make('starting_price')->label('سعر يبدأ من')->numeric()->minValue(0),
                    Select::make('accepts_quotes')->label('استقبال عروض الأسعار')->options([1 => 'نعم', 0 => 'لا'])->default(1)->required(),
                ])->columns(2)->defaultItems(0)->columnSpanFull(),
                Repeater::make('serviceAreas')->label('مناطق عمل الفني')->relationship()->schema([
                    Select::make('governorate_id')->label('المحافظة')->options(fn () => \\App\\Models\\Governorate::where('is_active', true)->orderBy('name_ar')->pluck('name_ar', 'id'))->searchable()->required(),
                    Select::make('city_id')->label('المدينة (اختياري)')->options(fn () => \\App\\Models\\City::where('is_active', true)->orderBy('name_ar')->pluck('name_ar', 'id'))->searchable(),
                ])->columns(2)->defaultItems(0)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('display_name')->label('الفني')->searchable()->sortable(),
            TextColumn::make('user.phone')->label('الهاتف'),
            TextColumn::make('professional_title')->label('التخصص المهني')->searchable(),
            TextColumn::make('verification_status')->label('الاعتماد')->badge()->formatStateUsing(fn ($state) => ['pending'=>'قيد المراجعة','approved'=>'معتمد','rejected'=>'مرفوض'][$state] ?? $state),
            TextColumn::make('availability_status')->label('التوفر')->badge(),
            TextColumn::make('created_at')->label('تاريخ التسجيل')->dateTime('Y-m-d')->sortable(),
        ])->filters([
            SelectFilter::make('verification_status')->label('حالة الاعتماد')->options(['pending'=>'قيد المراجعة','approved'=>'معتمد','rejected'=>'مرفوض']),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])->defaultSort('created_at','desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicianProfiles::route('/'),
            'create' => CreateTechnicianProfile::route('/create'),
            'edit' => EditTechnicianProfile::route('/{record}/edit'),
        ];
    }
}
