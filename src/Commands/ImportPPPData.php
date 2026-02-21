<?php

namespace MuhammadQuran\PPPStripe\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPPPData extends Command
{
    // The name and signature of the console command.
    protected $signature = 'import:ppp';

    // The console command description.
    protected $description = 'Import latest PPP values from CSV into the database';

    public function handle()
    {
        // 1. Path to your CSV file
        // Ensure the file is in storage/app or specify the full path
        $csvFilePath = storage_path('app/private/ppp_world.csv'); 

        if (!file_exists($csvFilePath)) {
            $this->error("File not found at: $csvFilePath");
            return 1;
        }

        $this->info("Starting Import...");

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $rowNumber = 0;
            $recordsProcessed = 0;

            // Begin a transaction for data integrity
            DB::beginTransaction();

            try {
                while (($data = fgetcsv($handle, null, ",", '"', '\\')) !== FALSE) {
                    $rowNumber++;

                    // 1. Skip Header Rows (first 5 rows)
                    if ($rowNumber < 6) {
                        continue;
                    }

                    // 2. Extract Basic Info
                    // Assuming Column 0 is Name, Column 1 is Code based on your description
                    $countryName = isset($data[0]) ? trim($data[0]) : null;
                    $countryCode = isset($data[1]) ? trim($data[1]) : null;

                    // Skip invalid rows where code is missing
                    if (empty($countryCode)) {
                        continue;
                    }

                    // 3. Find Latest PPP Value (Iterate backwards)
                    $latestValue = null;
                    $columnCount = count($data);

                    for ($i = $columnCount - 1; $i > 1; $i--) {
                        $val = trim($data[$i]);
                        if ($val) {
                            $latestValue = (float) $val;
                            break; // Stop at the first non-empty value from the right
                        }
                    }
                    
                    if ($latestValue === null) {
                        continue;
                    }

                    // 4. Update or Insert into Database
                    // We match by 'country_code' to avoid duplicates.
                    DB::table('ppp_data')->updateOrInsert(
                        ['country_code' => $countryCode], // Search criteria
                        [
                            'country_name' => $countryName,
                            'latest_ppp_value' => $latestValue,
                            'created_at' => now(),
                        ]
                    );

                    $recordsProcessed++;
                }

                // Commit the transaction
                DB::commit();
                fclose($handle);
                
                $this->info("Success! Processed and saved $recordsProcessed records.");

            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                $this->error("Error occurred: " . $e->getMessage());
                return 1;
            }
        }
        
        return 0;
    }
}