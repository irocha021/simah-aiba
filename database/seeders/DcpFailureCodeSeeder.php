<?php
// database/seeders/DcpFailureCodeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DcpFailureCode;

class DcpFailureCodeSeeder extends Seeder
{
    public function run(): void
    {
        $failureCodes = [
            ['code' => 'G', 'description' => 'Good DCP Message'],
            ['code' => '?', 'description' => 'DCP Message with Parity Error'],
            ['code' => 'A', 'description' => 'DCP message contained a correctable address error'],
            ['code' => 'B', 'description' => 'DCP message contained a bad (unknown) address'],
            ['code' => 'D', 'description' => 'DCP message was duplicated (i.e. received on multiple channels)'],
            ['code' => 'I', 'description' => 'DCP message had an invalid address'],
            ['code' => 'M', 'description' => 'The DCP message for the referenced platform was missing (not received in its proper time slice)'],
            ['code' => 'N', 'description' => 'The referenced platform has a non-complete entry in the DAPS Platform Description Table (PDT)'],
            ['code' => 'Q', 'description' => 'DCP message had bad quality measurements'],
            ['code' => 'T', 'description' => 'DCP message was received outside its proper time slice (early/late)'],
            ['code' => 'U', 'description' => 'DCP message was unexpected'],
            ['code' => 'W', 'description' => 'DCP message was received on the wrong channel'],
            ['code' => 'C', 'description' => 'Excessive carrier before start of message'],
            ['code' => 'S', 'description' => 'Low signal strength'],
            ['code' => 'F', 'description' => 'Excessive frequency offset'],
            ['code' => 'X', 'description' => 'Bad modulation index'],
            ['code' => 'V', 'description' => 'Low battery voltage'],
        ];

        foreach ($failureCodes as $code) {
            DcpFailureCode::updateOrCreate(
                ['code' => $code['code']],
                ['description' => $code['description']]
            );
        }
    }
}