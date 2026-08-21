<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictCitySeeder extends Seeder
{
    /**
     * Seeded from the hardcoded districtCities list previously duplicated in
     * customers/create.blade.php and customers/show.blade.php.
     */
    public function run(): void
    {
        $districtCities = [
            'Ampara'        => ['Ampara','Akkaraipattu','Kalmunai','Mahaoya','Nintavur','Pottuvil','Sammanthurai'],
            'Anuradhapura'  => ['Anuradhapura','Eppawala','Galnewa','Kekirawa','Medawachchiya','Mihintale','Nochchiyagama','Tambuttegama'],
            'Badulla'       => ['Badulla','Bandarawela','Diyatalawa','Ella','Haputale','Mahiyanganaya','Passara','Welimada'],
            'Batticaloa'    => ['Batticaloa','Chenkaladi','Eravur','Kattankudy','Valaichenai'],
            'Colombo'       => ['Colombo','Battaramulla','Boralesgamuwa','Dehiwala','Homagama','Kaduwela','Kesbewa','Kolonnawa','Kotte','Maharagama','Moratuwa','Nugegoda','Rajagiriya','Ratmalana','Sri Jayawardenepura Kotte','Wellampitiya'],
            'Galle'         => ['Galle','Ambalangoda','Balapitiya','Baddegama','Bentota','Elpitiya','Hikkaduwa','Karapitiya'],
            'Gampaha'       => ['Gampaha','Divulapitiya','Ekala','Ganemulla','Ja-Ela','Kadawatha','Kandana','Katunayake','Kiribathgoda','Mirigama','Minuwangoda','Negombo','Nittambuwa','Ragama','Veyangoda','Wattala'],
            'Hambantota'    => ['Hambantota','Ambalantota','Beliatta','Suriyawewa','Tangalle','Tissamaharama','Weeraketiya'],
            'Jaffna'        => ['Jaffna','Chavakachcheri','Chavakacheri','Kopay','Manipay','Nallur','Point Pedro','Uduvil'],
            'Kalutara'      => ['Kalutara','Agalawatta','Aluthgama','Bandaragama','Beruwala','Horana','Ingiriya','Matugama','Panadura','Wadduwa'],
            'Kandy'         => ['Kandy','Akurana','Digana','Gampola','Kadugannawa','Katugastota','Kundasale','Nawalapitiya','Peradeniya','Teldeniya','Wattegama'],
            'Kegalle'       => ['Kegalle','Aranayake','Galigamuwa','Mawanella','Rambukkana','Warakapola'],
            'Kilinochchi'   => ['Kilinochchi','Pallai','Paranthan'],
            'Kurunegala'    => ['Kurunegala','Alawwa','Bingiriya','Giriulla','Kuliyapitiya','Mawathagama','Narammala','Nikaweratiya','Pannala','Wariyapola'],
            'Mannar'        => ['Mannar','Musali','Nanattan'],
            'Matale'        => ['Matale','Dambulla','Galewela','Pallepola','Rattota','Sigiriya','Ukuwela'],
            'Matara'        => ['Matara','Akuressa','Devinuwara','Dikwella','Hakmana','Kamburupitiya','Weligama'],
            'Monaragala'    => ['Monaragala','Bibile','Buttala','Siyambalanduwa','Wellawaya'],
            'Mullaitivu'    => ['Mullaitivu','Oddusuddan','Puthukkudiyiruppu'],
            'Nuwara Eliya'  => ['Nuwara Eliya','Ginigathena','Hatton','Kotagala','Maskeliya','Ragala','Talawakele'],
            'Polonnaruwa'   => ['Polonnaruwa','Dimbulagala','Hingurakgoda','Kaduruwela','Medirigiriya','Welikanda'],
            'Puttalam'      => ['Puttalam','Anamaduwa','Chilaw','Marawila','Mundal','Nattandiya','Wennappuwa'],
            'Ratnapura'     => ['Ratnapura','Balangoda','Eheliyagoda','Embilipitiya','Kalawana','Kuruwita','Pelmadulla'],
            'Trincomalee'   => ['Trincomalee','Kantale','Kinniya','Mutur','Thampalakamam'],
            'Vavuniya'      => ['Vavuniya','Cheddikulam','Nedunkerni'],
        ];

        foreach ($districtCities as $districtName => $cities) {
            $district = District::firstOrCreate(['name' => $districtName], ['is_active' => true]);
            foreach ($cities as $cityName) {
                City::firstOrCreate(
                    ['district_id' => $district->id, 'name' => $cityName],
                    ['is_active' => true]
                );
            }
        }
    }
}
