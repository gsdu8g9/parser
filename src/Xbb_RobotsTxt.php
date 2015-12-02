<?php
/**
 * Xbb/Xbb_RobotsTxt.php
 *
 * Этот скрипт описывает класс для работы с robots.txt
 *
 * @author Dmitriy Skorobogatov <info at xbb dot uz>
 * @editor Alexander Rubtsov <RubtsovAV at gmail dot com>
 * @license public domain
 * @version 0.1.1
 */

/**
 * Класс для парсинга и последующей работы с robots.txt
 */
namespace Parser;
use \Parser\Traits\Singleton;
use \Parser\Traits\Named;

class Xbb_RobotsTxt {
    use Named;
    use Singleton;

    /**
     * Максимальный размер файла robots.txt
     */
    private $_MAXSIZE = 32768;  //32Кб

    /**
     * Сайт, которому принадлежит robots.txt. Например: http://xbb.uz/
     */
    private $_site = '';

    /**
     * Список директив файла robots.txt
     */
    private $_directives = array();

    /**
     * Список директив allow|disallow
     */
    private $_allows = array();


    static public function getInstance($url) {
        if (empty(static::$instance)) {
            static::$instance = new static($url);
        }

        return static::$instance;
    }
    /**
     * Конструктор
     * @param $url
     * @throws \Exception
     */
    private function __construct($url)
    {
        if (false === ($arUrl = @parse_url($url))) {
            throw new \Exception('Невозможно распарсить URL "' . $url . '"');
        }
        if (empty($arUrl['scheme']) || empty($arUrl['host'])) {
            $er = 'Введенный URL "' . $url . '" не содержит схемы и имени хоста';
            throw new \Exception($er);
        }
        $this->_site = $arUrl['scheme'] . '://' . $arUrl['host'] . '/';
        $url = $this->_site . 'robots.txt';
        $ctx = stream_context_create(array('http' => array('timeout' => 10))); // ограничение времени ответа
        if (false === ($directives = @file_get_contents($url, false, $ctx, -1, $this->_MAXSIZE))) {
            $er = 'Файл ' . $url . ' не существует или не может быть загружен.';
            //throw new \Exception($er);
        }
        $directives = explode("\n", $directives);
        $userAgent = '';
        foreach ($directives as $v) {
            if (false !== ($pos = strpos($v, '#'))) {
                $v = substr($v, 0, $pos);
            }
            $v = trim($v);
            if (! $v) {
                continue;
            }
            list($field, $value) = explode(':', $v, 2);
            $field = strtolower(trim($field));
            $value = trim($value);
            if ('user-agent' == $field && $value) {
                $userAgent = strtolower($value);
                $this->_directives[$userAgent] = array();
                continue;
            }
            if (! $userAgent) {
                continue;
            }
            $this->_directives[$userAgent][] = array($field, $value);
            if ($field == 'allow' || $field == 'disallow') {
                $type = $field{0};
                if (empty($value)) {
                    $type = ($type != 'a') ? 'a' : 'd';
                    $value = '/';
                }
                $this->_allows[$userAgent][] = array($type, $value);
            }
        }
        foreach ($this->_allows as $bot => &$directives) {
            usort($directives, array($this, 'sortAllows'));
            foreach ($directives as &$value) {
                $match = $value[1];
                if (strpos($match, '*') !== false || strpos($match, '$') !== false) {
                    $match = strtr(preg_quote($match, '#'), array(
                        '\\*' => '.*',
                        '\\$' => '$',
                    ));
                    $value[1] = "#^$match#U";
                }
            }
        }
    }

    /**
     * Callback сортирует правила allow|disallow в соотвествии со стандартом
     * @param $a
     * @param $b
     * @return int
     */
    private function sortAllows($a,$b){
        $s = strlen($b[1])-strlen($a[1]);
        if ($s==0) {  //если длины равны, то у allow приоритет
            if($a[0]==$b[0]) $s = 0;
            elseif($b[0]=='a') $s = 1;
            elseif($a[0]=='a') $s = -1;
        }
        return $s;
    }

    /**
     * Возвращает адрес сайта, с чьим robots.txt работаем
     *
     * @return string - Адрес сайта
     */
    public function getSite()
    {
        return $this->_site;
    }

    /**
     * Возвращает список директив из robots.txt в структурированном виде
     *
     * @return array - Список директив
     */
    public function getDirectives()
    {
        return $this->_directives;
    }

    /**
     * Проверяет, разрешен ли в robots.txt данный URL для обращения к нему
     * данного бота
     * @param $url
     * @param string $bot
     * @return bool
     * @throws \Exception
     */
    public function allow($url, $bot = '*') {
        if (false === ($arUrl = @parse_url($url))) {
            $er = 'Невозможно распарсить проверяемый URL "' . $url . '"';
            throw new \Exception($er);
        }
        if (! empty($arUrl['scheme']) && ! empty($arUrl['host'])  && $this->_site != $arUrl['scheme'] . '://' . $arUrl['host'] . '/') {
            $er = 'Проверяемый URL "' . $url . '" принадлежит другому домену';
            //throw new \Exception($er);
        }
        $path = empty($arUrl['path']) ? '/' : $arUrl['path'];

        if (isset($this->_allows[strtolower($bot)])) {
            $directives = $this->_allows[strtolower($bot)];
        } elseif (isset($this->_allows['*'])) {
            $directives = $this->_allows['*'];
        } else {
            return true;
        }
        foreach ($directives as $v) {
            if ('a' != $v[0] && 'd' != $v[0]) {
                continue;
            }
            if (! strlen($v[1])) {
                return ('a' != $v[0]);
            }
            if ($v[1]{0} === '#') {
                if(preg_match($v[1],$path))  
                    return ('a' == $v[0]);
            } else {
                $subPath = substr($path, 0, strlen($v[1]));
                if ($subPath == $v[1]) {
                    return ('a' == $v[0]);
                }
            }
        }
        return true;
    }
}