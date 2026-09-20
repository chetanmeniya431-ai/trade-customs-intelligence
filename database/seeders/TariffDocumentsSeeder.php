<?php

namespace Database\Seeders;

use App\Models\TariffDocument;
use App\Models\User;
use App\Services\TariffIngestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds two synthetic India Customs Tariff Schedule excerpts (Textiles
 * chapters 50-63, Electronics chapters 84-85) as real PDFs, then runs them
 * through the same TariffIngestService pipeline the manual upload screen
 * uses — parse, chunk, embed via nomic-embed-text. This is demo content
 * only; no real tariff schedule data.
 */
class TariffDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@tradecustoms.local')->first();
        $ingest = app(TariffIngestService::class);

        $this->seedDocument(
            name: 'India Customs Tariff Schedule — Chapters 50–63 (Textiles)',
            countryCode: 'IN',
            html: $this->textilesHtml(),
            uploadedBy: $admin?->id,
            ingest: $ingest,
        );

        $this->seedDocument(
            name: 'India Customs Tariff Schedule — Chapters 84–85 (Electronics)',
            countryCode: 'IN',
            html: $this->electronicsHtml(),
            uploadedBy: $admin?->id,
            ingest: $ingest,
        );
    }

    protected function seedDocument(string $name, string $countryCode, string $html, ?int $uploadedBy, TariffIngestService $ingest): void
    {
        if (TariffDocument::where('name', $name)->exists()) {
            return;
        }

        $pdfBinary = Pdf::loadHTML($html)->setPaper('a4')->output();
        $filename = 'tariffs/'.Str::uuid().'.pdf';
        Storage::disk('public')->put($filename, $pdfBinary);

        $document = TariffDocument::create([
            'name' => $name,
            'country_code' => $countryCode,
            'document_type' => 'tariff_schedule',
            'file_path' => $filename,
            'uploaded_by' => $uploadedBy,
        ]);

        $ingest->ingest($document);
    }

    protected function textilesHtml(): string
    {
        $body = <<<'TEXT'
INDIA CUSTOMS TARIFF SCHEDULE — SECTION XI: TEXTILES AND TEXTILE ARTICLES (Chapters 50–63)
Synthetic demo excerpt for training and testing purposes only. Not an official tariff schedule.

CHAPTER 50 — SILK
Heading 5007: Woven fabrics of silk or of silk waste.
5007.10 — Fabrics of noil silk. 5007.20 — Other fabrics, containing 85% or more by weight of silk.
Classification note: fabrics must be predominantly silk by weight to fall under this heading.

CHAPTER 52 — COTTON
Heading 5208: Woven fabrics of cotton, containing 85% or more by weight of cotton, weighing not
more than 200 g/m2.
5208.11 — Unbleached, plain weave, weighing not more than 100 g/m2.
5208.12 — Unbleached, plain weave, weighing more than 100 g/m2.
5208.21 — Bleached, plain weave, weighing not more than 100 g/m2.
5208.31 — Dyed, plain weave, weighing not more than 100 g/m2.
5208.52 — Printed, plain weave, weighing more than 100 g/m2.
Classification note: "cotton fabric, woven" with no synthetic fibre content and weighing under
200 g/m2 is classified under 5208, not under Chapter 54 (man-made filaments). A common
misclassification is filing woven cotton fabric under 5407 (synthetic filament fabrics) because
both headings cover "woven fabrics" — always confirm fibre composition (cotton vs. synthetic
filament) before assigning the heading.
Heading 5209: Woven fabrics of cotton, containing 85% or more by weight of cotton, weighing
more than 200 g/m2. Sub-structure mirrors 5208 by weave/finish (unbleached, bleached, dyed,
printed).

CHAPTER 54 — MAN-MADE FILAMENTS
Heading 5407: Woven fabrics of synthetic filament yarn, including woven fabrics obtained from
materials of heading 5404.
5407.10 — Woven fabrics obtained from high tenacity yarn of nylon, polyester or other polyamides.
5407.42 — Dyed, containing 85% or more by weight of textured polyester filaments.
5407.61 — Containing 85% or more by weight of non-textured polyester filaments.
Classification note: heading 5407 is for fabrics woven predominantly from SYNTHETIC filament
yarn (polyester, nylon, polypropylene filament, etc.), not natural fibres such as cotton. A
commercial invoice describing "cotton fabric woven" should map to 5208/5209, and a declaration
under 5407 for a cotton product is a classification mismatch that should be flagged for review,
with 5208 offered as the likely correct code.

CHAPTER 61 — ARTICLES OF APPAREL, KNITTED OR CROCHETED
Heading 6109: T-shirts, singlets and other vests, knitted or crocheted.
6109.10 — Of cotton. 6109.90 — Of other textile materials.

CHAPTER 63 — OTHER MADE-UP TEXTILE ARTICLES; SETS; WORN CLOTHING
Heading 6305: Sacks and bags, of a kind used for the packing of goods.
6305.32 — Flexible intermediate bulk containers.
6305.33 — Other, of polyethylene or polypropylene strip or the like.
6305.90 — Sacks and bags of other textile materials.
Classification note: woven polypropylene (PP) sacks used for bulk packing of goods such as
grain, cement, fertiliser, or animal feed — including multi-ply woven PP sacks rated for 25kg to
50kg loads — fall under 6305.33 (sacks and bags of polyethylene or polypropylene strip). This
applies regardless of ply count; a "6-ply polypropylene woven sack, 50kg capacity, plain weave"
should be classified under 6305.33.10 in the Indian tariff schedule, not under Chapter 39
(plastics) since the sack is woven textile strip, not a moulded plastic article.
Heading 6307: Other made up articles, including dress patterns.
6307.90 — Other, including surgical masks, cleaning cloths, and similar made-up items.
TEXT;

        return $this->wrapHtml($body);
    }

    protected function electronicsHtml(): string
    {
        $body = <<<'TEXT'
INDIA CUSTOMS TARIFF SCHEDULE — SECTION XVI: MACHINERY AND ELECTRICAL EQUIPMENT (Chapters 84–85)
Synthetic demo excerpt for training and testing purposes only. Not an official tariff schedule.

CHAPTER 84 — NUCLEAR REACTORS, BOILERS, MACHINERY AND MECHANICAL APPLIANCES
Heading 8479: Machines and mechanical appliances having individual functions, not specified or
included elsewhere in this Chapter.
8479.89 — Other machines and mechanical appliances.
Classification note: this is a residual heading; use only when no more specific heading applies.

CHAPTER 85 — ELECTRICAL MACHINERY AND EQUIPMENT
Heading 8501: Electric motors and generators (excluding generating sets).
8501.10 — Motors of an output not exceeding 37.5 W.
8501.31 — Other DC motors, DC generators, of an output not exceeding 750 W.
Classification note: brushless DC (BLDC) motors used in drones fall under 8501.10 or 8501.31
depending on output rating.

Heading 8503: Parts suitable for use solely or principally with the machines of heading 8501 or
8502.
8503.00 — Parts of electric motors, generators and generating sets, including rotors, stators,
brush holders, and dedicated motor-control assemblies sold as a spare part for a specific motor.

Heading 8536: Electrical apparatus for switching or protecting electrical circuits, for a voltage
not exceeding 1,000 V (switches, relays, fuses, plugs, sockets, junction boxes).
8536.50 — Other switches.
8536.69 — Other plugs and sockets.

Heading 8543: Electrical machines and apparatus, having individual functions, not specified or
included elsewhere in this Chapter.
8543.70 — Other machines and apparatus, including electronic speed controllers (ESCs) for
brushless motors, signal generators, and other standalone electronic control modules that are
not simple "parts" of a single specified machine.
Classification note: a standalone electronic speed controller (ESC) for brushless drone motors —
rated by current such as 30A, sold without a battery — is classified as an electrical apparatus
with an individual function (regulating motor speed via pulse-width modulation) under 8543.70,
rather than as a "part" under 8503, because the ESC is a discrete electronic control device usable
with more than one specific motor model. A common misclassification is filing the ESC under
8501 (motors) since it is used with motors — the ESC itself contains no motor windings and must
be classified separately under 8543.70.

Heading 8517: Telephone sets, including telephones for cellular networks; other apparatus for
transmission or reception of voice, images or data.
8517.62 — Machines for the reception, conversion and transmission or regeneration of voice,
images or other data, including switches and routers.

Heading 8544: Insulated wire, cable and other insulated electric conductors.
8544.42 — Other electric conductors, for a voltage not exceeding 1,000 V, fitted with connectors.
TEXT;

        return $this->wrapHtml($body);
    }

    protected function wrapHtml(string $text): string
    {
        $escaped = e($text);

        return '<html><body style="font-family: DejaVu Sans, sans-serif; font-size: 11px; white-space: pre-wrap;">'.$escaped.'</body></html>';
    }
}
