<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WaterSupplyImport;

class WaterSupplyDataImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:watersupply';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import of Water Supply data from excel/csv file to database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start_time=time();
        $storage = Storage::disk('importwatersupply')->path('/');
        $location = preg_replace('/\\\\/', '', $storage);
        
        $file_selection = Storage::disk('importwatersupply')->listContents();
        $filename = $file_selection[0]['basename'];
        
        \DB::statement('TRUNCATE TABLE watersupply_info.watersupply_payments RESTART IDENTITY');
        \DB::statement('ALTER SEQUENCE IF exists watersupply_info.watersupply_payments_id_seq RESTART WITH 1');
        Excel::import(new WaterSupplyImport, $location.$filename);
        

        $end_time=time();
        
        //check larave.log file to confirm all the queries has run successfully
        \Log::info("Tax data imported from excel/csv file to database successfully on ". date("F j, Y, g:i a"));
        Log::info('Total execution time : '. ($end_time-$start_time).'seconds');

        $this->info('Tax data imported successfully in '. ($end_time-$start_time).'seconds');
        return Command::SUCCESS;
    }
}
