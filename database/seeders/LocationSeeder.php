<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->governorates() as $governorateSlug => $governorateData) {
            $governorate = Governorate::updateOrCreate(
                ['slug' => $governorateSlug],
                [
                    'name_ar' => $governorateData['name_ar'],
                    'name_en' => $governorateData['name_en'],
                    'is_active' => true,
                ],
            );

            foreach ($governorateData['cities'] as $citySlug => $cityData) {
                $city = City::updateOrCreate(
                    ['governorate_id' => $governorate->id, 'slug' => $citySlug],
                    [
                        'name_ar' => $cityData['name_ar'],
                        'name_en' => $cityData['name_en'],
                        'is_active' => true,
                    ],
                );

                foreach ($cityData['districts'] ?? [] as $districtSlug => $districtName) {
                    District::updateOrCreate(
                        ['city_id' => $city->id, 'slug' => $districtSlug],
                        [
                            'name_ar' => $districtName,
                            'name_en' => null,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }

    /**
     * @return array<string, array{name_ar: string, name_en: string, cities: array<string, array{name_ar: string, name_en: string, districts?: array<string, string>}>}>
     */
    private function governorates(): array
    {
        return [
            'cairo' => [
                'name_ar' => 'القاهرة', 'name_en' => 'Cairo',
                'cities' => [
                    'cairo' => [
                        'name_ar' => 'القاهرة', 'name_en' => 'Cairo',
                        'districts' => [
                            'nasr-city' => 'مدينة نصر',
                            'maadi' => 'المعادي',
                            'heliopolis' => 'مصر الجديدة',
                            'zamalek' => 'الزمالك',
                            'fifth-settlement' => 'التجمع الخامس',
                            'new-cairo' => 'القاهرة الجديدة',
                            'mokattam' => 'المقطم',
                            'helwan' => 'حلوان',
                            'downtown' => 'وسط البلد',
                        ],
                    ],
                ],
            ],
            'giza' => [
                'name_ar' => 'الجيزة', 'name_en' => 'Giza',
                'cities' => [
                    'giza' => [
                        'name_ar' => 'الجيزة', 'name_en' => 'Giza',
                        'districts' => [
                            'dokki' => 'الدقي',
                            'mohandessin' => 'المهندسين',
                            'haram' => 'الهرم',
                            'faisal' => 'فيصل',
                            'agouza' => 'العجوزة',
                            'imbaba' => 'إمبابة',
                        ],
                    ],
                    '6th-of-october' => [
                        'name_ar' => '6 أكتوبر', 'name_en' => '6th of October',
                        'districts' => [
                            'el-hosary' => 'الحصري',
                            'october-gardens' => 'حدائق أكتوبر',
                            'industrial-zone' => 'المنطقة الصناعية',
                        ],
                    ],
                    'sheikh-zayed' => [
                        'name_ar' => 'الشيخ زايد', 'name_en' => 'Sheikh Zayed',
                        'districts' => [
                            'sheikh-zayed-city' => 'الشيخ زايد',
                            'beit-el-watan' => 'بيت الوطن',
                        ],
                    ],
                ],
            ],
            'alexandria' => [
                'name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria',
                'cities' => [
                    'alexandria' => [
                        'name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria',
                        'districts' => [
                            'smouha' => 'سموحة',
                            'sidi-gaber' => 'سيدي جابر',
                            'agami' => 'العجمي',
                            'montazah' => 'المنتزه',
                            'moharam-bek' => 'محرم بك',
                            'cleopatra' => 'كليوباترا',
                            'loran' => 'لوران',
                        ],
                    ],
                ],
            ],
            'dakahlia' => [
                'name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia',
                'cities' => [
                    'mansoura' => ['name_ar' => 'المنصورة', 'name_en' => 'Mansoura'],
                    'mit-ghamr' => ['name_ar' => 'ميت غمر', 'name_en' => 'Mit Ghamr'],
                ],
            ],
            'red-sea' => [
                'name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea',
                'cities' => [
                    'hurghada' => ['name_ar' => 'الغردقة', 'name_en' => 'Hurghada'],
                    'safaga' => ['name_ar' => 'سفاجا', 'name_en' => 'Safaga'],
                    'marsa-alam' => ['name_ar' => 'مرسى علم', 'name_en' => 'Marsa Alam'],
                ],
            ],
            'beheira' => [
                'name_ar' => 'البحيرة', 'name_en' => 'Beheira',
                'cities' => [
                    'damanhour' => ['name_ar' => 'دمنهور', 'name_en' => 'Damanhour'],
                    'kafr-el-dawwar' => ['name_ar' => 'كفر الدوار', 'name_en' => 'Kafr El Dawwar'],
                ],
            ],
            'fayoum' => [
                'name_ar' => 'الفيوم', 'name_en' => 'Fayoum',
                'cities' => [
                    'fayoum' => ['name_ar' => 'الفيوم', 'name_en' => 'Fayoum'],
                ],
            ],
            'gharbia' => [
                'name_ar' => 'الغربية', 'name_en' => 'Gharbia',
                'cities' => [
                    'tanta' => ['name_ar' => 'طنطا', 'name_en' => 'Tanta'],
                    'mahalla' => ['name_ar' => 'المحلة الكبرى', 'name_en' => 'El Mahalla El Kubra'],
                ],
            ],
            'ismailia' => [
                'name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia',
                'cities' => [
                    'ismailia' => ['name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia'],
                ],
            ],
            'monufia' => [
                'name_ar' => 'المنوفية', 'name_en' => 'Monufia',
                'cities' => [
                    'shebin-el-kom' => ['name_ar' => 'شبين الكوم', 'name_en' => 'Shebin El Kom'],
                    'menouf' => ['name_ar' => 'منوف', 'name_en' => 'Menouf'],
                ],
            ],
            'minya' => [
                'name_ar' => 'المنيا', 'name_en' => 'Minya',
                'cities' => [
                    'minya' => ['name_ar' => 'المنيا', 'name_en' => 'Minya'],
                    'mallawi' => ['name_ar' => 'ملوي', 'name_en' => 'Mallawi'],
                ],
            ],
            'qalyubia' => [
                'name_ar' => 'القليوبية', 'name_en' => 'Qalyubia',
                'cities' => [
                    'banha' => ['name_ar' => 'بنها', 'name_en' => 'Banha'],
                    'shubra-el-kheima' => ['name_ar' => 'شبرا الخيمة', 'name_en' => 'Shubra El Kheima'],
                ],
            ],
            'new-valley' => [
                'name_ar' => 'الوادي الجديد', 'name_en' => 'New Valley',
                'cities' => [
                    'kharga' => ['name_ar' => 'الخارجة', 'name_en' => 'Kharga'],
                ],
            ],
            'suez' => [
                'name_ar' => 'السويس', 'name_en' => 'Suez',
                'cities' => [
                    'suez' => ['name_ar' => 'السويس', 'name_en' => 'Suez'],
                ],
            ],
            'aswan' => [
                'name_ar' => 'أسوان', 'name_en' => 'Aswan',
                'cities' => [
                    'aswan' => ['name_ar' => 'أسوان', 'name_en' => 'Aswan'],
                    'kom-ombo' => ['name_ar' => 'كوم أمبو', 'name_en' => 'Kom Ombo'],
                ],
            ],
            'assiut' => [
                'name_ar' => 'أسيوط', 'name_en' => 'Assiut',
                'cities' => [
                    'assiut' => ['name_ar' => 'أسيوط', 'name_en' => 'Assiut'],
                ],
            ],
            'beni-suef' => [
                'name_ar' => 'بني سويف', 'name_en' => 'Beni Suef',
                'cities' => [
                    'beni-suef' => ['name_ar' => 'بني سويف', 'name_en' => 'Beni Suef'],
                ],
            ],
            'port-said' => [
                'name_ar' => 'بورسعيد', 'name_en' => 'Port Said',
                'cities' => [
                    'port-said' => ['name_ar' => 'بورسعيد', 'name_en' => 'Port Said'],
                ],
            ],
            'damietta' => [
                'name_ar' => 'دمياط', 'name_en' => 'Damietta',
                'cities' => [
                    'damietta' => ['name_ar' => 'دمياط', 'name_en' => 'Damietta'],
                    'ras-el-bar' => ['name_ar' => 'رأس البر', 'name_en' => 'Ras El Bar'],
                ],
            ],
            'sharqia' => [
                'name_ar' => 'الشرقية', 'name_en' => 'Sharqia',
                'cities' => [
                    'zagazig' => ['name_ar' => 'الزقازيق', 'name_en' => 'Zagazig'],
                    '10th-of-ramadan' => ['name_ar' => 'العاشر من رمضان', 'name_en' => '10th of Ramadan'],
                ],
            ],
            'south-sinai' => [
                'name_ar' => 'جنوب سيناء', 'name_en' => 'South Sinai',
                'cities' => [
                    'sharm-el-sheikh' => ['name_ar' => 'شرم الشيخ', 'name_en' => 'Sharm El Sheikh'],
                    'dahab' => ['name_ar' => 'دهب', 'name_en' => 'Dahab'],
                ],
            ],
            'kafr-el-sheikh' => [
                'name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh',
                'cities' => [
                    'kafr-el-sheikh' => ['name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh'],
                ],
            ],
            'matrouh' => [
                'name_ar' => 'مطروح', 'name_en' => 'Matrouh',
                'cities' => [
                    'marsa-matrouh' => ['name_ar' => 'مرسى مطروح', 'name_en' => 'Marsa Matrouh'],
                    'el-alamein' => ['name_ar' => 'العلمين', 'name_en' => 'El Alamein'],
                ],
            ],
            'luxor' => [
                'name_ar' => 'الأقصر', 'name_en' => 'Luxor',
                'cities' => [
                    'luxor' => ['name_ar' => 'الأقصر', 'name_en' => 'Luxor'],
                ],
            ],
            'qena' => [
                'name_ar' => 'قنا', 'name_en' => 'Qena',
                'cities' => [
                    'qena' => ['name_ar' => 'قنا', 'name_en' => 'Qena'],
                ],
            ],
            'north-sinai' => [
                'name_ar' => 'شمال سيناء', 'name_en' => 'North Sinai',
                'cities' => [
                    'arish' => ['name_ar' => 'العريش', 'name_en' => 'Arish'],
                ],
            ],
            'sohag' => [
                'name_ar' => 'سوهاج', 'name_en' => 'Sohag',
                'cities' => [
                    'sohag' => ['name_ar' => 'سوهاج', 'name_en' => 'Sohag'],
                ],
            ],
        ];
    }
}
