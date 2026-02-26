<?php

namespace App\Console\Commands;

use App\Models\Sponsor;
use Illuminate\Console\Command;

class ImportSponsorsFromCsv extends Command
{
    protected $signature = 'sponsors:import-csv {file : Pad naar het CSV-bestand}';

    protected $description = 'Importeer sponsors uit een vrienden-CSV export';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!file_exists($path)) {
            $this->error("Bestand niet gevonden: {$path}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->error('Kon bestand niet openen.');
            return self::FAILURE;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->error('Lege of ongeldige CSV.');
            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $data = array_combine($header, $row);

            // Sla lege rijen over
            if (empty(trim($data['email'] ?? ''))) {
                $skipped++;
                continue;
            }

            $betalingId = trim($data['betaling_id'] ?? '') ?: null;
            $payload = [
                    'bedrijfsnaam' => $data['bedrijfsnaam'] ?? null,
                'voornaam' => $data['voornaam'] ?? '',
                'achternaam' => $data['achternaam'] ?? '',
                'postcode' => $data['postcode'] ?? null,
                'huisnummer' => $data['huisnummer'] ?? null,
                'telefoonnummer' => $data['telefoonnummer'] ?? null,
                'email' => $data['email'] ?? '',
                'bedrag' => (float) str_replace(',', '.', $data['bedrag'] ?? 0),
                'betaalstatus' => $data['betaalstatus'] ?? 'open',
                'invoice_id' => $data['invoice_id'] ?? null,
                'betaling_id' => $betalingId,
            ];

            if ($betalingId) {
                Sponsor::updateOrCreate(['betaling_id' => $betalingId], $payload);
            } else {
                Sponsor::create($payload);
            }
            $imported++;
        }

        fclose($handle);

        $this->info("Geïmporteerd: {$imported} sponsors" . ($skipped > 0 ? ", overgeslagen: {$skipped}" : '') . '.');

        return self::SUCCESS;
    }
}
