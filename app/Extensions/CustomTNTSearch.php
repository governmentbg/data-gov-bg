<?php

namespace App\Extensions;

use PDO;
use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;
use TeamTNT\TNTSearch\Indexer\TNTIndexer;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\Support\Collection;
use TeamTNT\TNTSearch\Support\Expression;
use TeamTNT\TNTSearch\Support\Highlighter;
use TeamTNT\TNTSearch\Support\Tokenizer;
use TeamTNT\TNTSearch\Support\TokenizerInterface;

class CustomTNTSearch extends TNTSearch
{
    /**
     * @param      $keyword
     * @param bool $noLimit
     * @param bool $isLastKeyword
     *
     * @return Collection
     */
    public function getAllDocumentsForKeyword($keyword, $noLimit = false, $isLastKeyword = false)
    {
        $word = $this->getWordlistByKeyword($keyword, $isLastKeyword);

        if (!isset($word[0])) {
            return new Collection([]);
        }

        if ($this->fuzziness) {
            return $this->getAllDocumentsForFuzzyKeyword($word, $noLimit);
        }

        return $this->getAllDocumentsForStrictKeyword($word, $noLimit);
    }

    /**
     * @param      $keyword
     * @param bool $isLastWord
     *
     * @return array
     */
    public function getWordlistByKeyword($keyword, $isLastWord = false)
    {
        if ($this->fuzziness) {
            return $this->fuzzySearch($keyword);
        }

        $searchWordlist = "SELECT * FROM wordlist WHERE term like :keyword";
        $stmtWord       = $this->index->prepare($searchWordlist);

        if ($this->asYouType && $isLastWord) {
            $searchWordlist = "SELECT * FROM wordlist WHERE term like :keyword ORDER BY length(term) ASC, num_hits DESC LIMIT 1";
            $stmtWord       = $this->index->prepare($searchWordlist);
            $stmtWord->bindValue(':keyword', mb_strtolower($keyword) ."%");
        } else {
            $stmtWord->bindValue(':keyword', "%". mb_strtolower($keyword) ."%");
        }

        $stmtWord->execute();
        $res = $stmtWord->fetchAll(PDO::FETCH_ASSOC);

        return $res;
    }

    /**
     * @param $word
     * @param $noLimit
     *
     * @return Collection
     */
    private function getAllDocumentsForStrictKeyword($word, $noLimit)
    {
        $criteria = implode(', ', array_column($word, 'id'));

        $query = "SELECT * FROM doclist WHERE term_id IN ($criteria) ORDER BY hit_count DESC LIMIT {$this->maxDocs}";

        if ($noLimit) {
            $query = "SELECT * FROM doclist WHERE term_id IN ($criteria) ORDER BY hit_count DESC";
        }

        $stmtDoc = $this->index->prepare($query);
        $stmtDoc->execute();

        return new Collection($stmtDoc->fetchAll(PDO::FETCH_ASSOC));
    }
}
