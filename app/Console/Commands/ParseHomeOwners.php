<?php

namespace App\Console\Commands;

use App\Services\HomeOwnerParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ParseHomeOwners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-home-owners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse homeowners csv';

    /**
     * Execute the console command.
     */
    public function handle(HomeOwnerParser $parser): int
    {
        $file = 'examples.csv';

        if (!Storage::disk('local')->exists($file)) {
            $this->error('CSV file not found');
            return Command::FAILURE;
        }

        $stream = Storage::disk('local')->readStream($file);

        while (($row = fgetcsv($stream)) !== false) {

            // Skip header
            if ($row[0] === 'homeowner')
                continue;

            $parser->parse($row[0]);
        }

        fclose($stream);

        $homeOwners = $parser->getParsedHomeOwners();
        $this->line(json_encode($homeOwners, JSON_PRETTY_PRINT));

        return Command::SUCCESS;

    }
}
