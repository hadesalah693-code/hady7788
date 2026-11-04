<?php

namespace Database\Seeders;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Models\City;
use App\Models\District;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class DemoPropertySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->properties() as $data) {
            $governorate = Governorate::where('slug', $data['governorate'])->firstOrFail();
            $city = City::where('governorate_id', $governorate->id)->where('slug', $data['city'])->first();
            $district = $city && isset($data['district'])
                ? District::where('city_id', $city->id)->where('slug', $data['district'])->first()
                : null;
            $type = PropertyType::where('slug', $data['type'])->firstOrFail();

            $property = Property::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => null,
                    'governorate_id' => $governorate->id,
                    'city_id' => $city?->id,
                    'district_id' => $district?->id,
                    'property_type_id' => $type->id,
                    'purpose' => $data['purpose'],
                    'status' => PropertyStatus::Published,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'area' => $data['area'],
                    'bedrooms' => $data['bedrooms'] ?? null,
                    'bathrooms' => $data['bathrooms'] ?? null,
                    'floor' => $data['floor'] ?? null,
                    'address' => $data['address'] ?? null,
                    'is_featured' => $data['featured'] ?? false,
                    'published_at' => now()->subDays($data['days_ago'] ?? 0),
                    'views_count' => $data['views'] ?? 0,
                ],
            );

            PropertyImage::where('property_id', $property->id)->delete();

            foreach ($data['images'] as $index => $image) {
                PropertyImage::create([
                    'property_id' => $property->id,
                    'path' => 'images/demo/'.$image,
                    'alt' => $data['title'].' - صورة '.($index + 1),
                    'sort_order' => $index,
                    'is_cover' => $index === 0,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function properties(): array
    {
        return [
            [
                'slug' => 'luxury-apartment-nasr-city',
                'title' => 'شقة فاخرة بثلاث غرف في مدينة نصر',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'nasr-city',
                'price' => 3_200_000, 'area' => 145, 'bedrooms' => 3, 'bathrooms' => 2, 'floor' => 4,
                'address' => 'شارع عباس العقاد، مدينة نصر',
                'description' => 'شقة واسعة بتشطيب حديث في قلب مدينة نصر، قريبة من محطة المترو والخدمات. تتكون من ثلاث غرف نوم وصالة كبيرة ومطبخ مجهز وحمامين، مع تهوية طبيعية ممتازة وإطلالة مفتوحة.',
                'featured' => true, 'days_ago' => 2, 'views' => 820,
                'images' => ['apartment-1.svg', 'apartment-2.svg', 'apartment-3.svg'],
            ],
            [
                'slug' => 'modern-apartment-maadi-rent',
                'title' => 'شقة حديثة للإيجار في المعادي',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Rent,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'maadi',
                'price' => 18_000, 'area' => 120, 'bedrooms' => 2, 'bathrooms' => 2, 'floor' => 2,
                'address' => 'شارع 9، المعادي',
                'description' => 'شقة مفروشة بالكامل للإيجار في المعادي، هادئة وقريبة من المدارس والمراكز التجارية. تشمل غرفتين نوم وصالة ومطبخ وحمامين، وجاهزة للسكن الفوري.',
                'featured' => true, 'days_ago' => 4, 'views' => 610,
                'images' => ['apartment-2.svg', 'apartment-1.svg', 'apartment-3.svg'],
            ],
            [
                'slug' => 'standalone-villa-sheikh-zayed',
                'title' => 'فيلا مستقلة بحديقة في الشيخ زايد',
                'type' => 'villa', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'giza', 'city' => 'sheikh-zayed', 'district' => 'sheikh-zayed-city',
                'price' => 9_500_000, 'area' => 320, 'bedrooms' => 5, 'bathrooms' => 4, 'floor' => 0,
                'address' => 'الحي الأول، الشيخ زايد',
                'description' => 'فيلا مستقلة بتصميم عصري وحديقة خاصة، خمس غرف نوم ماستر وصالة استقبال واسعة وغرفة معيشة ومطبخ أمريكي. موقع مميز بالقرب من الخدمات والطرق الرئيسية.',
                'featured' => true, 'days_ago' => 1, 'views' => 1240,
                'images' => ['villa-1.svg', 'villa-2.svg', 'apartment-3.svg'],
            ],
            [
                'slug' => 'townhouse-6th-october',
                'title' => 'تاون هاوس بتشطيب كامل في 6 أكتوبر',
                'type' => 'townhouse', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'giza', 'city' => '6th-of-october', 'district' => 'el-hosary',
                'price' => 5_800_000, 'area' => 220, 'bedrooms' => 4, 'bathrooms' => 3, 'floor' => 0,
                'address' => 'كمبوند الحصري، 6 أكتوبر',
                'description' => 'تاون هاوس داخل كمبوند متكامل الخدمات، بتشطيب كامل وحديقة خلفية صغيرة. أربع غرف نوم وصالة واسعة، مع أمن وحراسة على مدار اليوم.',
                'featured' => true, 'days_ago' => 6, 'views' => 530,
                'images' => ['townhouse-1.svg', 'villa-1.svg', 'villa-2.svg'],
            ],
            [
                'slug' => 'nile-view-apartment-zamalek',
                'title' => 'شقة بإطلالة على النيل في الزمالك',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Rent,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'zamalek',
                'price' => 35_000, 'area' => 180, 'bedrooms' => 3, 'bathrooms' => 2, 'floor' => 8,
                'address' => 'شارع الجيزة، الزمالك',
                'description' => 'شقة راقية بإطلالة مباشرة على النيل، ثلاث غرف نوم وصالة كبيرة ومطبخ مجهز. المبنى به أسانسير وأمن، والموقع قريب من المطاعم والسفارات.',
                'featured' => true, 'days_ago' => 3, 'views' => 980,
                'images' => ['apartment-3.svg', 'apartment-1.svg', 'apartment-2.svg'],
            ],
            [
                'slug' => 'commercial-shop-heliopolis',
                'title' => 'محل تجاري في مصر الجديدة',
                'type' => 'shop', 'purpose' => PropertyPurpose::Rent,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'heliopolis',
                'price' => 25_000, 'area' => 60, 'bedrooms' => null, 'bathrooms' => 1, 'floor' => 0,
                'address' => 'شارع الحجاز، مصر الجديدة',
                'description' => 'محل تجاري بموقع حيوي على شارع رئيسي، واجهة زجاجية واسعة ومناسبة للكافيهات أو المتاجر. يضم حمامًا وغرفة خلفية صغيرة.',
                'featured' => false, 'days_ago' => 9, 'views' => 310,
                'images' => ['shop-1.svg', 'office-1.svg', 'apartment-2.svg'],
            ],
            [
                'slug' => 'office-fifth-settlement',
                'title' => 'مكتب إداري في التجمع الخامس',
                'type' => 'office', 'purpose' => PropertyPurpose::Rent,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'fifth-settlement',
                'price' => 40_000, 'area' => 150, 'bedrooms' => null, 'bathrooms' => 2, 'floor' => 3,
                'address' => 'التجمع الخامس، القاهرة الجديدة',
                'description' => 'مكتب إداري مجهز في برج إداري حديث، مساحة مفتوحة وقاعات اجتماعات، مع تكييف مركزي وأسانسيرات وأمن. مناسب للشركات والوكالات.',
                'featured' => true, 'days_ago' => 5, 'views' => 470,
                'images' => ['office-1.svg', 'shop-1.svg', 'apartment-1.svg'],
            ],
            [
                'slug' => 'residential-land-october-gardens',
                'title' => 'أرض سكنية في حدائق أكتوبر',
                'type' => 'land', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'giza', 'city' => '6th-of-october', 'district' => 'october-gardens',
                'price' => 3_000_000, 'area' => 400, 'bedrooms' => null, 'bathrooms' => null,
                'address' => 'حدائق أكتوبر، 6 أكتوبر',
                'description' => 'قطعة أرض سكنية بمساحة 400 متر في منطقة حدائق أكتوبر، بواجهة 20 مترًا وموقع قريب من المحاور الرئيسية. جاهزة للبناء والتراخيص.',
                'featured' => false, 'days_ago' => 12, 'views' => 220,
                'images' => ['land-1.svg', 'villa-2.svg', 'townhouse-1.svg'],
            ],
            [
                'slug' => 'apartment-smouha-alexandria',
                'title' => 'شقة في سموحة بالإسكندرية',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'alexandria', 'city' => 'alexandria', 'district' => 'smouha',
                'price' => 2_700_000, 'area' => 130, 'bedrooms' => 3, 'bathrooms' => 2, 'floor' => 5,
                'address' => 'شارع فوزي معاذ، سموحة',
                'description' => 'شقة مشمسة في سموحة بثلاث غرف نوم وصالة واسعة، قريبة من مول سموحة والجامعة. المبنى نظيف وبه أسانسير.',
                'featured' => false, 'days_ago' => 8, 'views' => 390,
                'images' => ['apartment-2.svg', 'apartment-3.svg', 'apartment-1.svg'],
            ],
            [
                'slug' => 'chalet-hurghada',
                'title' => 'شاليه في الغردقة قريب من البحر',
                'type' => 'chalet', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'red-sea', 'city' => 'hurghada',
                'price' => 2_200_000, 'area' => 85, 'bedrooms' => 2, 'bathrooms' => 1, 'floor' => 0,
                'address' => 'منطقة الممشى السياحي، الغردقة',
                'description' => 'شاليه داخل منتجع سياحي بالغردقة، غرفتان نوم وصالة ومطبخ، مع مسبح مشترك وحديقة. فرصة ممتازة للاستثمار أو الإجازة.',
                'featured' => true, 'days_ago' => 7, 'views' => 760,
                'images' => ['chalet-1.svg', 'villa-1.svg', 'apartment-3.svg'],
            ],
            [
                'slug' => 'villa-marsa-matrouh',
                'title' => 'فيلا في مرسى مطروح',
                'type' => 'villa', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'matrouh', 'city' => 'marsa-matrouh',
                'price' => 7_500_000, 'area' => 280, 'bedrooms' => 4, 'bathrooms' => 3, 'floor' => 0,
                'address' => 'طريق كورنيش مطروح',
                'description' => 'فيلا بحرية بتصميم متوسطي وحديقة واسعة، أربع غرف نوم وإطلالة قريبة من البحر. مناسبة للسكن الصيفي أو الاستثمار الفندقي.',
                'featured' => false, 'days_ago' => 15, 'views' => 340,
                'images' => ['villa-2.svg', 'villa-1.svg', 'chalet-1.svg'],
            ],
            [
                'slug' => 'apartment-mansoura-rent',
                'title' => 'شقة للإيجار في المنصورة',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Rent,
                'governorate' => 'dakahlia', 'city' => 'mansoura',
                'price' => 8_000, 'area' => 110, 'bedrooms' => 2, 'bathrooms' => 1, 'floor' => 3,
                'address' => 'شارع الجيش، المنصورة',
                'description' => 'شقة هادئة للإيجار في المنصورة، غرفتان نوم وصالة ومطبخ، قريبة من الجامعة والمواصلات. مناسبة للأسر الصغيرة.',
                'featured' => false, 'days_ago' => 11, 'views' => 180,
                'images' => ['apartment-1.svg', 'apartment-2.svg', 'apartment-3.svg'],
            ],
            [
                'slug' => 'investment-building-tanta',
                'title' => 'عمارة استثمارية في طنطا',
                'type' => 'building', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'gharbia', 'city' => 'tanta',
                'price' => 12_000_000, 'area' => 600, 'bedrooms' => null, 'bathrooms' => null,
                'address' => 'شارع البحر، طنطا',
                'description' => 'عمارة استثمارية مكونة من ستة طوابق و12 وحدة سكنية، بموقع تجاري حيوي في طنطا. دخل إيجاري ثابت وإمكانية إعادة التطوير.',
                'featured' => false, 'days_ago' => 20, 'views' => 290,
                'images' => ['building-1.svg', 'office-1.svg', 'shop-1.svg'],
            ],
            [
                'slug' => 'apartment-sheikh-zayed-sale',
                'title' => 'شقة عصرية في الشيخ زايد',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'giza', 'city' => 'sheikh-zayed', 'district' => 'sheikh-zayed-city',
                'price' => 4_100_000, 'area' => 160, 'bedrooms' => 3, 'bathrooms' => 2, 'floor' => 2,
                'address' => 'الحي الثاني، الشيخ زايد',
                'description' => 'شقة بتشطيب سوبر لوكس في كمبوند بالشيخ زايد، ثلاث غرف نوم وصالة واسعة ومطبخ مجهز. تشمل جراجًا وحديقة مشتركة.',
                'featured' => true, 'days_ago' => 1, 'views' => 690,
                'images' => ['apartment-3.svg', 'apartment-2.svg', 'apartment-1.svg'],
            ],
            [
                'slug' => 'office-downtown-cairo',
                'title' => 'مكتب في وسط البلد',
                'type' => 'office', 'purpose' => PropertyPurpose::Rent,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'downtown',
                'price' => 15_000, 'area' => 90, 'bedrooms' => null, 'bathrooms' => 1, 'floor' => 6,
                'address' => 'شارع طلعت حرب، وسط البلد',
                'description' => 'مكتب بمساحة 90 مترًا في مبنى إداري بوسط البلد، مناسب للشركات الناشئة والمكاتب القانونية. قريب من مترو محمد نجيب.',
                'featured' => false, 'days_ago' => 14, 'views' => 160,
                'images' => ['office-1.svg', 'apartment-2.svg', 'shop-1.svg'],
            ],
            [
                'slug' => 'apartment-mokattam',
                'title' => 'شقة في المقطم',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'cairo', 'city' => 'cairo', 'district' => 'mokattam',
                'price' => 1_800_000, 'area' => 100, 'bedrooms' => 2, 'bathrooms' => 1, 'floor' => 1,
                'address' => 'الهضبة الوسطى، المقطم',
                'description' => 'شقة اقتصادية في المقطم، غرفتان نوم وصالة ومطبخ، مناسبة للبداية العائلية. قريبة من المدارس والمواصلات.',
                'featured' => false, 'days_ago' => 18, 'views' => 240,
                'images' => ['apartment-1.svg', 'apartment-3.svg', 'apartment-2.svg'],
            ],
            [
                'slug' => 'villa-ismailia',
                'title' => 'فيلا في الإسماعيلية',
                'type' => 'villa', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'ismailia', 'city' => 'ismailia',
                'price' => 6_200_000, 'area' => 300, 'bedrooms' => 4, 'bathrooms' => 3, 'floor' => 0,
                'address' => 'حي الشيخ زايد، الإسماعيلية',
                'description' => 'فيلا مستقلة بحديقة في حي هادئ بالإسماعيلية، أربع غرف نوم وصالة كبيرة ومطبخ. مناسبة للسكن العائلي الدائم.',
                'featured' => false, 'days_ago' => 22, 'views' => 190,
                'images' => ['villa-1.svg', 'villa-2.svg', 'townhouse-1.svg'],
            ],
            [
                'slug' => 'apartment-luxor',
                'title' => 'شقة في الأقصر',
                'type' => 'apartment', 'purpose' => PropertyPurpose::Sale,
                'governorate' => 'luxor', 'city' => 'luxor',
                'price' => 1_500_000, 'area' => 120, 'bedrooms' => 3, 'bathrooms' => 2, 'floor' => 2,
                'address' => 'شارع الكرنك، الأقصر',
                'description' => 'شقة واسعة في موقع مميز بالأقصر، ثلاث غرف نوم وصالة ومطبخ، قريبة من المعابد والخدمات. فرصة جيدة للسكن أو الاستثمار السياحي.',
                'featured' => false, 'days_ago' => 25, 'views' => 210,
                'images' => ['apartment-2.svg', 'apartment-1.svg', 'apartment-3.svg'],
            ],
        ];
    }
}
