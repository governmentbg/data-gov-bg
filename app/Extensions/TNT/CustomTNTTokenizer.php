<?php

namespace App\Extensions\TNT;

use TeamTNT\TNTSearch\Support\TokenizerInterface;

class CustomTNTTokenizer implements TokenizerInterface
{
    public function tokenize($text, $stopwords = [])
    {
        $text = mb_strtolower($text);
        $splitBySpace = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_diff($splitBySpace, $stopwords);
    }
}
