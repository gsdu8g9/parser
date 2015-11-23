<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 11:30
 */

namespace Parser;

/**
 * Result wrapper for comparasion
 * Class Result
 * @package Parser
 */

class Result {
    private $__db = NULL;

    /**
     * Create tmp storage
     * @throws \Exception
     */
    public function __construct() {
        session_start();
        // Tmp storage of requests
        if (!$this->__db = new \SQLite3("result_".session_id().".db")) {
            throw new \Exception('Could not create db!');
        }
    }

    /**
     * Create storage tables
     */
    public function init() {
        $this->__db->exec('PRAGMA synchronous=OFF');
        $this->__db->exec('DROP TABLE IF EXISTS links');
        $this->__db->exec('CREATE TABLE links (id int unsigned primary_key not null, url text, skip int default 0, scheme text default NULL, host text default NULL, path text default NULL, query text default NULL, fragment text default NULL)');
        $this->__db->exec('DROP TABLE IF EXISTS new');
        $this->__db->exec('DROP TABLE IF EXISTS  old');
        $this->__db->exec('DROP TABLE IF EXISTS  result');
    }

    /**
     * Add link to storage
     * @param $id
     * @param $url
     */
    public function addLink($id, $url) {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            $ar_link = parse_url($url);
            $this->__db->exec("INSERT INTO links (id, url, skip, scheme, host, path, query, fragment) VALUES ('"
                .$this->__db->escapeString($id)."', '"
                .$this->__db->escapeString($url)."', '"
                .$this->__db->escapeString(0)."', '"
                .$this->__db->escapeString(isset($ar_link['scheme']) && !empty($ar_link['scheme']) ? $ar_link['scheme'] : "")."', '"
                .$this->__db->escapeString(isset($ar_link['host']) && !empty($ar_link['host']) ? $ar_link['host'] : "")."', '"
                .$this->__db->escapeString(isset($ar_link['path']) && !empty($ar_link['path']) ? $ar_link['path'] : "")."', '"
                .$this->__db->escapeString(isset($ar_link['query']) && !empty($ar_link['query']) ? $ar_link['query'] : "")."', '"
                .$this->__db->escapeString(isset($ar_link['fragment']) && !empty($ar_link['fragment']) ? $ar_link['fragment'] : "")."'"
                .")");
        } else {
            // Skip url
            $this->__db->exec("INSERT INTO links (id, url, skip) VALUES ('"
                .$this->__db->escapeString($id)."', '"
                .$this->__db->escapeString($url)."', '"
                .$this->__db->escapeString(1)."'"
                .")");
        }

    }

    /**
     * Get links to check
     * @param int $num
     * @param $new_domain
     * @return array|null
     */
    public function getLinks($num = 10, $new_domain) {
        $data = array();
        $results = $this->__db->query('SELECT * FROM links ORDER BY id ASC limit 0,'.intval($num));
        while ($row = $results->fetchArray()) {
            $row['url'] = array (
                'old' => $row['url']
            );
            $new_url = $row['scheme'].'://'.$new_domain.$row['path'];
            if (!empty($row['query'])) {
                $new_url .= '?'.$row['query'];
            }
            if (!empty($row['fragment'])) {
                $new_url .= '#'.$row['fragment'];
            }
            $row['url']['new'] = $new_url;
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Delete checked links
     * @param $num
     */
    public function delLinks($num) {
        $this->__db->query('DELETE FROM links WHERE id IN (SELECT id FROM links ORDER BY id ASC limit 0,'.intval($num).')');
    }
    public function getResult($num) {
        $data = array();
        $results = $this->__db->query('SELECT * FROM result ORDER BY id DESC limit 0,'.intval($num));
        while ($row = $results->fetchArray()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Insert data to storage
     * @param $line
     * @param $url
     * @param $data
     * @param $site
     * @throws \Exception
     */
    public function addData($line, $url, $data, $site) {
        if (in_array($site, array('old', 'new'))) {
            $this->storeData($line, $url, $data, $site);
        } else {
            throw new \Exception ('Wrong storage table');
        }
    }

    /**
     * Store data from site
     * @param $line
     * @param $url
     * @param $data
     * @param $site
     */
    private function storeData ($line, $url, $data, $site) {
        $query = $this->__db->prepare("INSERT INTO '{$site}' VALUES (?, ?, ?)");
        $query->bindValue(1, $line, SQLITE3_INTEGER);
        $query->bindValue(2, $url, SQLITE3_TEXT);
        $query->bindValue(3, $data, SQLITE3_BLOB);
        $query->execute();
    }


    /**
     * Create tables for storing results
     * @param array $filters
     */
    public function createResultStorage(array $filters) {
        $filters_string = '';
        foreach ($filters as $name => $filter) {
            $filters_string .= ", ".$name." text";
        }
        $this->__db->exec('CREATE TABLE if NOT EXISTS result (id int unsigned primary_key not null, old_url text, new_url text,  skip integer default 0, Robots text'.$filters_string.')');
        $this->__db->exec('CREATE TABLE if NOT EXISTS new (id int unsigned primary_key not null, url text, data blob)');
        $this->__db->exec('CREATE TABLE if NOT EXISTS old (id int unsigned primary_key not null, url text, data blob)');
    }

    /**
     * Remove parsed data from local storage
     */
    public function removeResultStorage(){
        $this->__db->exec('DROP TABLE IF EXISTS new');
        $this->__db->exec('DROP TABLE IF EXISTS old');
        $this->__db->exec('DROP TABLE IF EXISTS result');
    }

    /**
     * Add result line
     * @param $line
     * @param $old_url
     * @param $new_url
     */
    public function addLine($line, $old_url, $new_url, $skip = 0) {
        $this->__db->exec("INSERT INTO result (id, old_url, new_url, skip) VALUES ('"
            .$this->__db->escapeString($line)."', '"
            .$this->__db->escapeString($old_url)."', '"
            .$this->__db->escapeString($new_url)."', '"
            .$this->__db->escapeString($skip)."'"
            .")");
        if (intval($skip) == 1) {
            $this->__db->exec('UPDATE links SET skip=1 where id='.intval($line));
        }
    }

    /**
     * Save compare result
     * @param $line
     * @param $result
     * @param $perc
     */
    public function addResult($line, $result, $perc) {
        $query = $this->__db->prepare("UPDATE result SET  {$result}=:result WHERE id=:id");
        $query->bindValue(':result', $perc, SQLITE3_TEXT);
        $query->bindValue(':id', $line, SQLITE3_INTEGER);
        $query->execute();
    }

    /**
     * Get request from old site
     * @param $id
     * @return mixed
     */
    public function getOldResponse($id) {
        return $this->__db->querySingle('SELECT data FROM old WHERE id='.intval($id), true)['data'];
    }

    /**
     * Get request from new site
     * @param $id
     * @return mixed
     */
    public function getNewResponse($id) {
        return $this->__db->querySingle('SELECT data FROM new WHERE id='.intval($id), true)['data'];
    }

    /**
     * Array of results table columns names
     * @return array
     */
    public function getResultHeader() {
        $data = array();
        $results = $this->__db->query('pragma table_info(result)');
        while ($row = $results->fetchArray()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Array of result of comparasion
     * @return array
     */
    public function getResultData() {
        $data = array();
        $results = $this->__db->query('select * from result');
        while ($row = $results->fetchArray()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Clear results
     */
    public function clearResultData() {
        $this->__db->exec('DELETE FROM new');
        $this->__db->exec('DELETE FROM old');
        $this->__db->exec('DELETE FROM result');
        $this->__db->exec('VACUUM');
    }
}