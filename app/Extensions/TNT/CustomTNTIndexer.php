<?php

namespace App\Extensions\TNT;

use PDO;
use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Extensions\TNT\CustomTNTSearch;
use TeamTNT\TNTSearch\Support\Tokenizer;
use TeamTNT\TNTSearch\Indexer\TNTIndexer;
use TeamTNT\TNTSearch\Support\Collection;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\Stemmer\CroatianStemmer;
use TeamTNT\TNTSearch\Connectors\MySqlConnector;
use TeamTNT\TNTSearch\Connectors\SQLiteConnector;
use TeamTNT\TNTSearch\FileReaders\TextFileReader;
use TeamTNT\TNTSearch\Support\TokenizerInterface;
use TeamTNT\TNTSearch\Connectors\PostgresConnector;
use TeamTNT\TNTSearch\Connectors\SqlServerConnector;
use TeamTNT\TNTSearch\Connectors\FileSystemConnector;

class CustomTNTIndexer extends TNTIndexer
{
    public $tokenizer = null;
    public $stemmer = null;
    public $filereader = null;

    public function __construct()
    {
        $this->stemmer = new PorterStemmer;
        $this->tokenizer = new CustomTNTTokenizer;
        $this->filereader = new TextFileReader;
    }

    public function breakIntoTokens($text)
    {
        return $this->tokenizer->tokenize($text);
    }

    /**
     * @param string $indexName
     *
     * @return TNTIndexer
     */
    public function createIndex($indexName)
    {
        $this->indexName = $indexName;

        $this->index = app('db')->connection('mysqltnt')->getPdo();

        $this->index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->index->exec("CREATE TABLE IF NOT EXISTS ". CustomTNTSearch::getTNTName($indexName, 'wordlist') ." (
            id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
            term TEXT COLLATE utf8mb4_unicode_ci,
            num_hits INTEGER,
            num_docs INTEGER
        )");

        // $result = $this->index->query("SHOW INDEX FROM ". CustomTNTSearch::getTNTName($indexName, 'wordlist') ."
        //     where Key_name = '". CustomTNTSearch::getTNTName($indexName, 'main_index') ."'
        // ");

        // if (empty($result->fetchAll())) {
        //     $this->index->exec("ALTER TABLE ". CustomTNTSearch::getTNTName($indexName, 'wordlist') ."
        //         ADD UNIQUE INDEX ". CustomTNTSearch::getTNTName($indexName, 'main_index') ." (term)
        //     ");
        // }

        $this->index->exec("CREATE TABLE IF NOT EXISTS ". CustomTNTSearch::getTNTName($indexName, 'doclist') ." (
            term_id INTEGER COLLATE utf8_bin,
            doc_id INTEGER,
            hit_count INTEGER
        )");

        $this->index->exec("CREATE TABLE IF NOT EXISTS ". CustomTNTSearch::getTNTName($indexName, 'fields') ." (
            id INTEGER PRIMARY KEY,
            name TEXT
        )");

        $this->index->exec("CREATE TABLE IF NOT EXISTS ". CustomTNTSearch::getTNTName($indexName, 'hitlist') ." (
            term_id INTEGER,
            doc_id INTEGER,
            field_id INTEGER,
            position INTEGER,
            hit_count INTEGER
        )");

        $this->index->exec("CREATE TABLE IF NOT EXISTS ". CustomTNTSearch::getTNTName($indexName, 'info') ." (
            index_key TEXT,
            value VARCHAR(255)
        )");

        $this->index->exec("INSERT INTO ". CustomTNTSearch::getTNTName($indexName, 'info') ." (index_key, value)
            values ('total_documents', 0)
        ");

        $result = $this->index->query("SHOW INDEX FROM ". CustomTNTSearch::getTNTName($indexName, 'doclist') ."
            where Key_name = '". CustomTNTSearch::getTNTName($indexName, 'main_term_id_index') ."'
        ");

        if (empty($result->fetchAll())) {
            $this->index->exec("ALTER TABLE ". CustomTNTSearch::getTNTName($indexName, 'doclist') ."
                ADD INDEX ". CustomTNTSearch::getTNTName($indexName, 'main_term_id_index') ." (term_id);
            ");
        }

        if (!$this->dbh) {
            $connector = $this->createConnector($this->config);
            $this->dbh = $connector->connect($this->config);
        }

        return $this;
    }

    public function setStemmer($stemmer, $indexName = null)
    {
        $this->stemmer = $stemmer;
        $class = get_class($stemmer);

        $this->index->exec("INSERT INTO ". CustomTNTSearch::getTNTName($indexName, 'info') ." (index_key, value)
            values ('stemmer', '". str_replace('\\', '\\\\\\', $class) ."')
        ");
    }

    public function prepareStatementsForIndex()
    {
        if (!$this->statementsPrepared) {
            $this->insertWordlistStmt = $this->index->prepare("
                INSERT INTO ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ." (term, num_hits, num_docs)
                VALUES (:keyword, :hits, :docs)
            ");

            $this->selectWordlistStmt = $this->index->prepare("
                SELECT * FROM ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ."
                WHERE term like :keyword LIMIT 1
            ");

            $this->updateWordlistStmt = $this->index->prepare("
                UPDATE ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ."
                SET num_docs = num_docs + :docs, num_hits = num_hits + :hits
                WHERE term = :keyword
            ");

            $this->statementsPrepared = true;
        }
    }

    public function update($id, $document, $indexName = null)
    {
        $this->indexName = $indexName;
        $this->delete($id);
        $this->insert($document);
    }

    public function delete($documentId, $indexName = null)
    {
        if (empty($this->indexName)) {
            $this->indexName = $indexName;
        }

        $rows = $this->prepareAndExecuteStatement("SELECT * FROM ". CustomTNTSearch::getTNTName($this->indexName, 'doclist') ."
            WHERE doc_id = :documentId;", [
            ['key' => ':documentId', 'value' => $documentId]
        ])->fetchAll(PDO::FETCH_ASSOC);

        $updateStmt = $this->index->prepare("UPDATE ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ."
            SET num_docs = num_docs - 1, num_hits = num_hits - :hits
            WHERE id = :term_id
        ");

        foreach ($rows as $document) {
            $updateStmt->bindParam(":hits", $document['hit_count']);
            $updateStmt->bindParam(":term_id", $document['term_id']);
            $updateStmt->execute();
        }

        $this->prepareAndExecuteStatement("DELETE FROM ". CustomTNTSearch::getTNTName($this->indexName, 'doclist') ."
            WHERE doc_id = :documentId;", [
            ['key' => ':documentId', 'value' => $documentId]
        ]);

        $res = $this->prepareAndExecuteStatement("DELETE FROM ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ."
            WHERE num_hits = 0
        ");

        $affected = $res->rowCount();

        if ($affected) {
            $total = $this->totalDocumentsInCollection() - 1;
            $this->updateInfoTable('total_documents', $total);
        }
    }

    public function updateInfoTable($key, $value)
    {
        $this->index->exec("UPDATE ". CustomTNTSearch::getTNTName($this->indexName, 'info') ." SET value = $value
            WHERE index_key = '$key'
        ");
    }

    public function saveDoclist($terms, $docId)
    {
        $insert = "INSERT INTO ". CustomTNTSearch::getTNTName($this->indexName, 'doclist') ." (term_id, doc_id, hit_count)
            VALUES (:id, :doc, :hits)";
        $stmt = $this->index->prepare($insert);

        foreach ($terms as $key => $term) {
            $stmt->bindValue(':id', $term['id']);
            $stmt->bindValue(':doc', $docId);
            $stmt->bindValue(':hits', $term['hits']);

            try {
                $stmt->execute();
            } catch (\Exception $e) {
                //we have a duplicate
                echo $e->getMessage();
            }
        }
    }

    public function saveHitList($stems, $docId, $termsList)
    {
        $fieldCounter = 0;
        $fields       = [];

        $insert = "INSERT INTO ". CustomTNTSearch::getTNTName($this->indexName, 'hitlist') ."
            (term_id, doc_id, field_id, position, hit_count)
            VALUES (:term_id, :doc_id, :field_id, :position, :hit_count)";
        $stmt = $this->index->prepare($insert);

        foreach ($stems as $field => $terms) {
            $fields[$fieldCounter] = $field;
            $positionCounter = 0;
            $termCounts = array_count_values($terms);
            foreach ($terms as $term) {
                if (isset($termsList[$term])) {
                    $stmt->bindValue(':term_id', $termsList[$term]['id']);
                    $stmt->bindValue(':doc_id', $docId);
                    $stmt->bindValue(':field_id', $fieldCounter);
                    $stmt->bindValue(':position', $positionCounter);
                    $stmt->bindValue(':hit_count', $termCounts[$term]);
                    $stmt->execute();
                }
                $positionCounter++;
            }
            $fieldCounter++;
        }
    }

    public function getWordFromWordList($word)
    {
        $selectStmt = $this->index->prepare("SELECT * FROM ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ."
            WHERE term like :keyword LIMIT 1
        ");
        $selectStmt->bindValue(':keyword', $word);
        $selectStmt->execute();
        return $selectStmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buildDictionary($filename, $count = -1, $hits = true, $docs = false)
    {
        $selectStmt = $this->index->prepare("SELECT * FROM ". CustomTNTSearch::getTNTName($this->indexName, 'wordlist') ."
            ORDER BY num_hits DESC;
        ");
        $selectStmt->execute();

        $dictionary = "";
        $counter = 0;

        while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
            $dictionary .= $row['term'];
            if ($hits) {
                $dictionary .= "\t".$row['num_hits'];
            }

            if ($docs) {
                $dictionary .= "\t".$row['num_docs'];
            }

            $counter++;

            if ($counter >= $count && $count > 0) {
                break;
            }

            $dictionary .= "\n";
        }

        file_put_contents($filename, $dictionary, LOCK_EX);
    }

    /**
     * @return int
     */
    public function totalDocumentsInCollection()
    {
        $query = "SELECT * FROM ". CustomTNTSearch::getTNTName($this->indexName, 'info') ." WHERE index_key = 'total_documents'";
        $docs  = $this->index->query($query);

        return $docs->fetch(PDO::FETCH_ASSOC)['value'];
    }
}
