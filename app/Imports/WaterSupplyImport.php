<?php

namespace App\Imports;

Use App\Models\WaterSupplyInfo\WaterSupply;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\Queue\ShouldQueue;

class WaterSupplyImport implements ToModel, WithHeadingRow, WithChunkReading,WithValidation, SkipsOnError, WithColumnFormatting, WithMapping, WithBatchInserts, ShouldQueue
{
    use Importable;
    
    public function model(array $row)
    {
        return new WaterSupply([
            "water_customer_id" => $row['water_customer_id'],
            "customer_name" => $row['customer_name']?$row['customer_name']:null,
            "customer_contact" => $row['customer_contact']?$row['customer_contact']:null,
            #"last_payment_date" => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['last_payment_date']),
            "last_payment_date" => ($row['last_payment_date']),
        ]);
    }
    
    public function chunkSize(): int
    {
        return 1000;
    }
    public function batchSize(): int
    {
        return 1000;
    }
    /**
    * @return array
    */
    public function rules(): array {
         
         return [
            'water_customer_id' => [
                'required',
                'string',
                //'unique:pgsql.watersupply_info.watersupply_payments,water_customer_id',
            ],
            'customer_name' => [
                'nullable',
                'string',
            ],
            'customer_contact' => [
                'nullable',
                'integer',
            ],
            'last_payment_date' => [
                'required',
                'date_format:Y-m-d', 
               
                ],
            
        ];
    }
    
    public function map($row): array
    {
        return [
            "water_customer_id" => $row['water_customer_id'],
            "customer_name" => $row['customer_name'],
            "customer_contact" => $row['customer_contact'],
            "last_payment_date" => date('Y-m-d', strtotime($row['last_payment_date'])),
        ];
    }
     /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'last_payment_date' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function onError(\Throwable $e) {
        
    }
    
    

}