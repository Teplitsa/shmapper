<?php

require_once(__DIR__ . "/vendor/autoload.php");
use League\Csv\Reader;

function get_sheet($id)
{
    $sheet_url = "https://docs.google.com/spreadsheets/u/0/d/{$id}/export?format=csv&id={$id}";

    $data = file_get_contents($sheet_url);
    $csv = Reader::createFromString($data);

    return iterator_to_array($csv->getRecords());

    $data = explode("\n", $data);
    $result = [];
    foreach ($data as $row)
    {
        $result[] = str_getcsv($row);
    }
    return $result;
}

//$id = "1dQupQpiGjPqIbVHCTRvpybr-cmk5zs8U";
//print_r(get_sheet($id));