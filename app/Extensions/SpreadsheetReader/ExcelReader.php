<?php

namespace App\Extensions\ExcelReader\;

include_once(base_path() . '/vendor/nuovo/spreadsheet-reader/php-excel-reader/excel_reader2.php');

class Custom_Spreadsheet_Excel_Reader extends Spreadsheet_Excel_Reader
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $this->_ole = new OLERead();
        $this->setUTFEncoder('iconv');

        if ($outputEncoding != '') {
            $this->setOutputEncoding($outputEncoding);
        }

        for ($i = 1; $i < 245; $i++) {
            $name = strtolower(((($i - 1) / 26 >= 1) ? chr(($i - 1) / 26 + 64) : '') . chr(($i - 1) % 26 + 65));
            
            $this->colnames[$name] = $i;
            $this->colindexes[$i] = $name;
        }

        $this->store_extended_info = $store_extended_info;

        if ($file != "") {
            $this->read($file);
        }
    }
}
