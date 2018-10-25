<?php

namespace App\Extensions\TNT;

use PDO;
use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\Support\Tokenizer;
use TeamTNT\TNTSearch\Indexer\TNTIndexer;
use TeamTNT\TNTSearch\Support\Collection;
use TeamTNT\TNTSearch\Support\Expression;
use TeamTNT\TNTSearch\Support\Highlighter;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\Support\TokenizerInterface;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;

class CustomTNTSearch extends TNTSearch
{
    public $indexName = null;

    /**
     * @param string $indexName
     *
     * @throws IndexNotFoundException
     */
    public function selectIndex($indexName)
    {
        $this->indexName = $indexName;
        $this->index = app('db')->connection('mysqltnt')->getPDO();
        $this->index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setStemmer();
    }

    /**
     * @param string $indexName
     * @param boolean $disableOutput
     *
     * @return CustomTNTIndexer
     */
    public function createIndex($indexName, $disableOutput = false)
    {
        $indexer = new CustomTNTIndexer;
        $indexer->loadConfig($this->config);
        $indexer->disableOutput = $disableOutput;

        if ($this->dbh) {
            $indexer->setDatabaseHandle($this->dbh);
        }

        return $indexer->createIndex($indexName);
    }

    /**
     * @return CustomTNTIndexer
     */
    public function getIndex()
    {
        $indexer = new CustomTNTIndexer;
        $indexer->inMemory = false;
        $indexer->setIndex($this->index);
        $indexer->setStemmer($this->stemmer, $this->indexName);

        return $indexer;
    }

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
            $df = $this->totalMatchingDocuments($term, $isLastKeyword);
            $idf = log($count / max(1, $df));

            foreach ($this->getAllDocumentsForKeyword($term, false, $isLastKeyword) as $document) {
                $docID = $document['doc_id'];
                $tf = $document['hit_count'];
                $num = ($tfWeight + 1) * $tf;
                $denom = $tfWeight * ((1 - $dlWeight) + $dlWeight) + $tf;
                $score = $idf * ($num / $denom);
                $docScores[$docID] = isset($docScores[$docID]) ?
                $docScores[$docID] + $score : $score;
            }
        }

        arsort($docScores);

        $docs = new Collection($docScores);

        $totalHits = $docs->count();
        $docs = $docs->map(function ($doc, $key) {
            return $key;
        })->take($numOfResults);

        $stopTimer = microtime(true);

        if ($this->isFileSystemIndex()) {
            return $this->filesystemMapIdsToPaths($docs)->toArray();
        }

        return [
            'ids'            => array_keys($docs->toArray()),
            'hits'           => $totalHits,
            'execution_time' => round($stopTimer - $startTimer, 7) * 1000 .' ms'
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

        $searchWordlist = "SELECT * FROM ". $this->getTNTName($this->indexName, 'wordlist') ." WHERE term like :keyword";
        $stmtWord       = $this->index->prepare($searchWordlist);

        if ($this->asYouType && $isLastWord) {
            $searchWordlist = "SELECT * FROM ". $this->getTNTName($this->indexName, 'wordlist') ." WHERE term
                like :keyword ORDER BY length(term) ASC, num_hits DESC LIMIT 1";
            $stmtWord = $this->index->prepare($searchWordlist);
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

        $query = "SELECT * FROM ". $this->getTNTName($this->indexName, 'doclist') ."
            WHERE term_id IN ($criteria) ORDER BY hit_count DESC LIMIT {$this->maxDocs}";

        if ($noLimit) {
            $query = "SELECT * FROM ". $this->getTNTName($this->indexName, 'doclist') ."
                WHERE term_id IN ($criteria) ORDER BY hit_count DESC";
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
        $query = "SELECT * FROM ". $this->getTNTName($this->indexName, 'doclist') ."
            WHERE term_id in ($binding_params) ORDER BY CASE term_id";
        $order_counter = 1;

        foreach ($words as $word) {
            $query .= " WHEN " . $word['id'] . " THEN " . $order_counter++;
        }

        $query .= " END";

        if (!$noLimit) {
            $query .= " LIMIT {$this->maxDocs}";
        }

        $stmtDoc = $this->index->prepare($query);

        $ids = null;

        foreach ($words as $word) {
            $ids[] = $word['id'];
        }

        $stmtDoc->execute($ids);

        return new Collection($stmtDoc->fetchAll(PDO::FETCH_ASSOC));
    }

    public function setStemmer()
    {
        $stemmer = $this->getValueFromInfoTable('stemmer');

        if ($stemmer) {
            $this->stemmer = new $stemmer;
        } else {
            $this->stemmer = isset($this->config['stemmer']) ? new $this->config['stemmer'] : new PorterStemmer;
        }
    }

    public function getValueFromInfoTable($value)
    {
        $query = "SELECT * FROM ". $this->getTNTName($this->indexName, 'info') ." WHERE index_key = '$value'";
        $docs = $this->index->query($query);

        return $docs->fetch(PDO::FETCH_ASSOC)['value'];
    }

    public static function getTNTName($name, $id = null)
    {
        $result = $name;

        if (isset($id)) {
            $result .= '_'. $id;
        }

        return $result;
    }
}
