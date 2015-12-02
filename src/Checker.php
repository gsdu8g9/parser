<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 11:07
 */
/**
 * Base class for parse data from old and new site
 * and compare it
 */
namespace Parser;

class Checker {
    private $__old_domain = array();
    private $__new_domain = "";
    private $__counter = 0;
    private $__threads = 10;
    private $__filters = array();
    private $__used_filters = array();
    private $__algorythms = array();
    private $__result = NULL;
    private $__old_encoding = NULL;
    private $__new_encoding = NULL;
    // Add extensions to skip here
    private $__skip_files = array (
        "jpg",
        "jpeg",
        "png",
        "gif",
        "pdf",
        "css",
        "js",
        "ico"
    );


    public function __construct() {
        $this->__setFilters();
        $this->__result = new \Parser\Result();
        $this->__rc = new \RollingCurl\RollingCurl();
        $this->__rc->setCallback(array($this, "save"));
        $this->__rc->window_size = $this->__threads;
        $this->__rc->setOptions(
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_FOLLOWLOCATION => true,
                // accept all certs
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            )
        );
    }

    /**
     * Find out a
     */
    private function __setFilters() {
        foreach (array_diff(scandir(dirname(__FILE__).DIRECTORY_SEPARATOR.'Filters'), array('..', '.')) as $filter) {
            $a = call_user_func(array('\Parser\Filters\\'.pathinfo($filter, PATHINFO_FILENAME), 'getInstance'));
            $this->__filters[$a->getName()] = $a;
        }
    }

    /**
     * Get list of available filters
     * @return array
     */
    public function getFilters() {
        return $this->__filters;
    }
    /**
     * Get line number from request
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    private function getLine(\RollingCurl\Request $request) {
        if (preg_match("/Line: ([\d]+)/i", $request->getHeaders()[0], $out)) {
            return $out[1];
        }
        throw new \Exception('Could not get line from response!');
    }

    /**
     * Get site (old or new) from request
     * @param \RollingCurl\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function getSite(\RollingCurl\Request $request) {
        if (preg_match("/Site: ([\w]+)/i", $request->getHeaders()[1], $out)) {
            return $out[1];
        }
        throw new \Exception('Could not get site from response!');
    }

    /**
     * Validate domain
     * @param $domain
     * @return bool
     */
    private function isValidateDomain($domain) {
        return (bool)preg_match("/^([a-z0-9][a-z0-9\-\.]{1,63})\.[a-z]{2,6}$/i", $this->__clearDomain($domain));
    }

    /**
     * Array of available old domains
     * @param strin $domain
     * @return array
     */
    public function setOldDomain($domain) {
        if ($this->isValidateDomain($domain)) {
            $domain = $this->__clearDomain($domain);
            $this->__old_domain = array (
                'www.'.$this->__clearDomain($domain),
                $this->__clearDomain($domain)
            );
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Store new domain
     * @param $domain
     * @return bool
     */
    public function setNewDomain($domain) {
        if ($this->isValidateDomain($domain)) {
            $this->__new_domain = str_replace(array('http://', 'https://', '//'), '', trim($domain, "/"));
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Strip out any http or www
     * @param $domain
     * @return mixed
     */
    private function __clearDomain($domain) {
        return str_replace(array('http://', 'www.'), '', trim($domain, "/"));
    }

    /**
     * Attach filters
     * @param array $filters
     */
    private function setFilters(array $filters = array()) {
        foreach ($filters as $filter) {
            if (array_key_exists($filter, $this->__filters)) {
                $this->__used_filters[$this->__filters[$filter]->getName()] =& $this->__filters[$filter];
            }
        }
    }
    /**
     * Set window size
     * @param $threads
     */
    private function setThreads($threads) {
        $this->__rc->window_size = $this->__threads = intval($threads);
    }

    /**
     * Proceed all comparasion
     * Payload
     * @param array $filters
     * @param $threads
     * @return int
     * @throws \Exception
     */
    public function run(array $filters, $threads, $old_encoding = "UTF-8", $new_encoding = "UTF-8") {
        $this->setFilters($filters);
        $this->setThreads($threads);
        $this->__old_encoding = $old_encoding;
        $this->__new_encoding = $new_encoding;

        $this->__result->createResultStorage($this->__used_filters);
        // Get data from storage
        foreach ($this->__result->getLinks($this->__threads, $this->__new_domain) as $link) {
            // Skip not local urls
            if (!in_array($link['host'], $this->__old_domain)) {
                $this->__result->addLine($link['id'], $link["url"]["old"], $link["url"]["new"], 1);
                continue;
            }
            // Skip extension
            if (in_array((new \SplFileInfo(basename($link["url"]["old"])))->getExtension(), $this->__skip_files)) {
                $this->__result->addLine($link['id'], $link["url"]["old"], $link["url"]["new"], 1);
                continue;
            }
            $this->__result->addLine($link['id'], $link["url"]["old"], $link["url"]["new"]);
            $this->__rc->add((new RequestOld ($link["url"]["old"], "GET"))->setHeaders(array("Line: ".$link['id'], 'Site: old')));
            $this->__rc->add((new RequestNew ($link["url"]["new"], "GET"))->setHeaders(array("Line: ".$link['id'], 'Site: new')));
            $this->__counter++;
        }

        // Proceed requests
        $this->__rc->execute();
        // prepare result
        $links_exists = false;
        foreach ($this->__result->getLinks($this->__threads, $this->__new_domain) as $link) {
            $links_exists = true;
            if ($link['skip'] == 1) {
                continue;
            }
            $old_request = unserialize(base64_decode($this->__result->getOldResponse($link['id'])));
            $new_request = unserialize(base64_decode($this->__result->getNewResponse($link['id'])));

            foreach ($this->__used_filters as $filter) {
                if ($link['skip'] == 1) {
                    continue;
                }
                foreach ($filter->getMethod()->execute ($old_request, $new_request) as $col => $data) {
                    $this->__result->addResult(
                        $link['id'],
                        $col,
                        $data
                    );
                }
            }
        }
        // Удаляем ссылки
        $this->__result->delLinks($this->__threads);
        return ($links_exists) ? 1 : 0;
    }

    /**
     * Save data for comparasion
     **/
    public function save(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
        $line = $this->getLine($request);
        $site = $this->getSite($request);
        /* Преобразует в UTF-8 */
        if ($request instanceof \Parser\RequestOld) {
            if ($this->__old_encoding !== "UTF-8") {
                $request->setResponseText(mb_convert_encoding($request->getResponseText(), "UTF-8", $this->__old_encoding));
            }
        }
        if ($request instanceof \Parser\RequestNew) {
            if ($this->__new_encoding !== "UTF-8") {
                $request->setResponseText(mb_convert_encoding($request->getResponseText(), "UTF-8", $this->__new_encoding));
            }
        }

        $this->__result->addData($line, $request->getUrl(), base64_encode(serialize($request)), $site);
    }
}
