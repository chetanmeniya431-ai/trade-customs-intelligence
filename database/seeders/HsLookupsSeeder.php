<?php

namespace Database\Seeders;

use App\Models\HsLookup;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Pre-populates the HS Code Finder's lookup history so the demo shows the
 * AI assistant working from first login, before anyone runs a live query.
 */
class HsLookupsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@tradecustoms.local')->first();

        $lookups = [
            [
                'query_text' => '6-ply polypropylene woven sacks, 50kg capacity, plain weave',
                'suggested_codes' => [
                    ['code' => '6305.33', 'heading' => 'Sacks and bags of polyethylene or polypropylene strip', 'confidence' => 'high'],
                ],
                'confidence' => 'high',
                'source_section' => 'Chapter 63 — Other made-up textile articles',
                'result_summary' => 'Woven PP sacks used for bulk packing of goods are classified under 6305.33 regardless of ply count.',
            ],
            [
                'query_text' => 'Electronic speed controller (ESC) for brushless drones, 30A, no battery',
                'suggested_codes' => [
                    ['code' => '8543.70', 'heading' => 'Electrical machines and apparatus, having individual functions, n.e.s.', 'confidence' => 'medium'],
                ],
                'confidence' => 'medium',
                'source_section' => 'Chapter 85 — Electrical machinery and equipment',
                'result_summary' => 'A standalone ESC is a discrete electronic control device, not a motor part, so it falls under 8543.70 rather than 8503.',
            ],
            [
                'query_text' => 'Cotton fabric, woven, 100% cotton, plain weave, unbleached, 150 g/m2',
                'suggested_codes' => [
                    ['code' => '5208.12', 'heading' => 'Woven fabrics of cotton, unbleached, plain weave, >100 g/m2', 'confidence' => 'high'],
                ],
                'confidence' => 'high',
                'source_section' => 'Chapter 52 — Cotton',
                'result_summary' => 'Predominantly cotton woven fabric under 200 g/m2 falls under heading 5208, not 5407 (synthetic filament fabrics).',
            ],
            [
                'query_text' => 'Brushless DC motor, 24V, 750W, for industrial fans',
                'suggested_codes' => [
                    ['code' => '8501.31', 'heading' => 'DC motors, DC generators, output not exceeding 750 W', 'confidence' => 'high'],
                ],
                'confidence' => 'high',
                'source_section' => 'Chapter 85 — Electrical machinery and equipment',
                'result_summary' => 'Output rating places this motor at the top of the 8501.31 band.',
            ],
            [
                'query_text' => 'Polyester filament woven lining fabric, dyed, 85% polyester',
                'suggested_codes' => [
                    ['code' => '5407.42', 'heading' => 'Dyed, containing 85% or more by weight of textured polyester filaments', 'confidence' => 'medium'],
                ],
                'confidence' => 'medium',
                'source_section' => 'Chapter 54 — Man-made filaments',
                'result_summary' => 'Synthetic filament composition confirms Chapter 54 rather than Chapter 52.',
            ],
        ];

        foreach ($lookups as $lookup) {
            HsLookup::firstOrCreate(
                ['query_text' => $lookup['query_text']],
                [
                    'suggested_codes' => $lookup['suggested_codes'],
                    'confidence' => $lookup['confidence'],
                    'source_section' => $lookup['source_section'],
                    'result_summary' => $lookup['result_summary'],
                    'created_by' => $admin?->id,
                    'created_at' => now()->subDays(rand(1, 20)),
                ]
            );
        }
    }
}
