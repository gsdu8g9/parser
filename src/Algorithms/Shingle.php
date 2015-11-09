<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 11:24
 */

/**
 * Shingle algo wrapper
 */
namespace Parser\Algorithms;


class Shingle extends \Parser\Algorithm {

    private $__shingle = 1; // Shingle size
    /**
     * Payload.
     * Compare text and return percents
     * @param $first
     * @param $second
     * @return float|int
     */
    public function check_it($first, $second) {
        // Debug
        //var_dump($first);
        //var_dump($second);
        //exit;
        if (!$first || !$second) {
            return -1;
        }

        $first_shingles = array_unique($this->get_shingle($first, $this->__shingle));
        $second_shingles = array_unique($this->get_shingle($second, $this->__shingle));


        if(count($first_shingles) < $this->__shingle-1 || count($second_shingles) < $this->__shingle-1) {
            return -2;
        }
        $intersect = array_intersect($first_shingles,$second_shingles);
        $merge = array_unique(array_merge($first_shingles,$second_shingles));
        $diff = (count($intersect)/count($merge))/0.01;
        return round($diff, 2);
    }

    /**
     * Makes shingle array from text
     * @param $text
     * @param int $n
     * @return array
     */
    private function get_shingle($text,$n = 1) {
        $shingles = array();
        $text = $this->clean_text($text);
        $elements = explode(" ",$text);
        for ($i=0;$i<(count($elements)-$n+1);$i++) {
            $shingle = '';
            for ($j=0;$j<$n;$j++){
                $shingle .= mb_strtolower(trim($elements[$i+$j]), 'UTF-8')." ";
            }
            if(strlen(trim($shingle)))
                $shingles[$i] = trim($shingle, ' -');
        }
        return $shingles;
    }

    /**
     * Simple prepare text for shingle
     * @param $text
     * @return mixed|string
     */
    private function clean_text($text) {
        $new_text = preg_replace("/[\,|\.|\'|\"|\\|\/]/i","",$text);
        $new_text = preg_replace("/[\n|\t]/i"," ",$new_text);
        $new_text = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $new_text);
        $new_text = preg_replace('/(\s\s+)/', ' ', trim($new_text));
        return $new_text;
    }
}