<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            // Local State Banks
            ['name' => 'Bank of Ceylon',                      'short_name' => 'BOC',    'swift_code' => 'BCEYLKLX'],
            ['name' => "People's Bank",                       'short_name' => 'PB',     'swift_code' => 'PEOELKLX'],
            ['name' => 'National Savings Bank',               'short_name' => 'NSB',    'swift_code' => 'NSBKLKLX'],
            ['name' => 'Regional Development Bank',           'short_name' => 'RDB',    'swift_code' => null],
            ['name' => 'Housing Development Finance Corporation', 'short_name' => 'HDFC', 'swift_code' => null],

            // Local Licensed Commercial Banks
            ['name' => 'Commercial Bank of Ceylon',           'short_name' => 'CBC',    'swift_code' => 'CCEYLKLX'],
            ['name' => 'Hatton National Bank',                'short_name' => 'HNB',    'swift_code' => 'HBLILKLX'],
            ['name' => 'Sampath Bank',                        'short_name' => 'Sampath','swift_code' => 'BSAMLKLX'],
            ['name' => 'Seylan Bank',                         'short_name' => 'Seylan', 'swift_code' => 'SEYLLKLX'],
            ['name' => 'Nations Trust Bank',                  'short_name' => 'NTB',    'swift_code' => 'NTBKLKLX'],
            ['name' => 'National Development Bank',           'short_name' => 'NDB',    'swift_code' => 'NDBSLKLX'],
            ['name' => 'Pan Asia Banking Corporation',        'short_name' => 'PABC',   'swift_code' => 'PABCLKLX'],
            ['name' => 'DFCC Bank',                           'short_name' => 'DFCC',   'swift_code' => 'DFCCLKLX'],
            ['name' => 'Union Bank of Colombo',               'short_name' => 'UBC',    'swift_code' => 'UNIONLKLX'],
            ['name' => 'Amana Bank',                          'short_name' => 'Amana',  'swift_code' => 'AMANLKLX'],
            ['name' => 'LB Finance',                          'short_name' => 'LBF',    'swift_code' => null],
            ['name' => 'Citizens Development Business Finance','short_name' => 'CDB',   'swift_code' => null],

            // Foreign Banks
            ['name' => 'Standard Chartered Bank',             'short_name' => 'SCB',    'swift_code' => 'SCBLLKLX'],
            ['name' => 'HSBC Sri Lanka',                      'short_name' => 'HSBC',   'swift_code' => 'HSBCLKLX'],
            ['name' => 'Citibank Sri Lanka',                  'short_name' => 'Citi',   'swift_code' => 'CITILKLX'],
            ['name' => 'Deutsche Bank Sri Lanka',             'short_name' => 'DB',     'swift_code' => 'DEUTLKLX'],
            ['name' => 'Habib Bank AG Zurich',                'short_name' => 'HBZ',    'swift_code' => 'HBZULKLX'],
            ['name' => 'Indian Bank Sri Lanka',               'short_name' => 'IB',     'swift_code' => 'IDIBINBB'],
            ['name' => 'Indian Overseas Bank Sri Lanka',      'short_name' => 'IOB',    'swift_code' => 'IOBAINBB'],
            ['name' => 'MCB Bank Sri Lanka',                  'short_name' => 'MCB',    'swift_code' => 'MUCBLKLX'],
            ['name' => 'ICICI Bank Sri Lanka',                'short_name' => 'ICICI',  'swift_code' => 'ICICILKL'],
            ['name' => 'Axis Bank Sri Lanka',                 'short_name' => 'Axis',   'swift_code' => 'AXISINBB'],
            ['name' => 'State Bank of India Sri Lanka',       'short_name' => 'SBI',    'swift_code' => 'SBININBB'],
        ];

        foreach ($banks as $bank) {
            \App\Models\Bank::firstOrCreate(
                ['name' => $bank['name']],
                array_merge($bank, ['is_active' => true])
            );
        }
    }
}
