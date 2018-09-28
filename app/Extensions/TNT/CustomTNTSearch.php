<?php

namespace App\Extensions\TNT;

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
     * @param string $phrase
     * @param int    $numOfResults
     *
     * @return array
     */
    public function search($phrase, $numOfResults = 100, $fuzzy = null)
    {
        $startTimer = microtime(true);
        $keywords = $this->breakIntoTokens($phrase);
        $keywords = new Collection($keywords);

        $keywords = $keywords->map(function ($keyword) {
            return $this->stemmer->stem($keyword);
        });

        $tfWeight = 1;
        $dlWeight = 0.5;
        $docScores = [];
        $count = $this->totalDocumentsInCollection();

        foreach ($keywords as $index => $term) {
            $isLastKeyword = ($keywords->count() - 1) == $index;
            $df = $this->totalMatchingDocuments($term, $isLastKeyword, $fuzzy);

            foreach ($this->getAllDocumentsForKeyword($term, false, $isLastKeyword, $fuzzy) as $document) {
                $docID = $document['doc_id'];
                $tf = $document['hit_count'];
                $idf = log($count / $df);
                $num = ($tfWeight + 1) * $tf;
                $denom = $tfWeight
                    * ((1 - $dlWeight) + $dlWeight)
                    + $tf;
                $score = $idf * ($num / $denom);
                $docScores[$docID] = isset($docScores[$docID]) ?
                $docScores[$docID] + $score : $score;
            }
        }

        arsort($docScores);

        $docs = new Collection($docScores);

        $counter = 0;
        $totalHits = $docs->count();
        $docs = $docs->map(function ($doc, $key) {
            return $key;
        })->filter(function ($item) use (&$counter, $numOfResults) {
            $counter++;

            if ($counter <= $numOfResults) {
                return true;
            }

            return false; // ?
        });

        $stopTimer = microtime(true);

        if ($this->isFileSystemIndex()) {
            return $this->filesystemMapIdsToPaths($docs)->toArray();
        }

        return [
            'ids'            => array_keys($docs->toArray()),
            'hits'           => $totalHits,
            'execution_time' => round($stopTimer - $startTimer, 7) * 1000 ." ms"
        ];
    }

    /**
     * @param      $keyword
     * @param bool $isLastWord
     *
     * @return int
     */
    public function totalMatchingDocuments($keyword, $isLastWord = false, $fuzzy = null)
    {
        $occurance = $this->getWordlistByKeyword($keyword, $isLastWord, $fuzzy);

        if (isset($occurance[0])) {
            return $occurance[0]['num_docs'];
        }

        return 0;
    }

    /**
     * @param      $keyword
     * @param bool $noLimit
     * @param bool $isLastKeyword
     *
     * @return Collection
     */
    public function getAllDocumentsForKeyword($keyword, $noLimit = false, $isLastKeyword = false, $fuzzy = null)
    {
        $word = $this->getWordlistByKeyword($keyword, $isLastKeyword, $fuzzy);

        if (!isset($word[0])) {
            return new Collection([]);
        }

        $fuzzySearchResults = new Collection([]);

        if (is_null($fuzzy) && $this->fuzziness || !empty($fuzzy)) {
            $fuzzySearchResults = $this->getAllDocumentsForFuzzyKeyword($word, $noLimit);
        }

        return collect(array_merge(
            $fuzzySearchResults->toArray(),
            $this->getAllDocumentsForStrictKeyword($word, $noLimit)->toArray()
        ));
    }

    /**
     * @param      $keyword
     * @param bool $isLastWord
     *
     * @return array
     */
    public function getWordlistByKeyword($keyword, $isLastWord = false, $fuzzy = null)
    {
        $fuzzySearchResults = [];

        if (is_null($fuzzy) && $this->fuzziness || !empty($fuzzy)) {
            $fuzzySearchResults = $this->fuzzySearch($keyword);
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

        return array_merge($fuzzySearchResults, $res);
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

    /**
     * @param $words
     * @param $noLimit
     *
     * @return Collection
     */
    private function getAllDocumentsForFuzzyKeyword($words, $noLimit)
    {
        $binding_params = implode(',', array_fill(0, count($words), '?'));
        $query = "SELECT * FROM doclist WHERE term_id in ($binding_params) ORDER BY hit_count DESC LIMIT {$this->maxDocs}";

        if ($noLimit) {
            $query = "SELECT * FROM doclist WHERE term_id in ($binding_params) ORDER BY hit_count DESC";
        }

        $stmtDoc = $this->index->prepare($query);

        $ids = null;

        foreach ($words as $word) {
            $ids[] = $word['id'];
        }

        $stmtDoc->execute($ids);

        return new Collection($stmtDoc->fetchAll(PDO::FETCH_ASSOC));
    }
}
