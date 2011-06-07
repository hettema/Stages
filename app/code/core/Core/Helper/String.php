<?php
/**
 * class Core_Helper_String
 * 
 * @package Core
 * @category Helper
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Helper_String extends Core_Helper_Abstract
{
    const ICONV_CHARSET = 'UTF-8';

    /**
     * Truncate a string to a certain length if necessary, appending the $etc string.
     * $remainder will contain the string that has been replaced with $etc.
     *
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncate($string, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        $remainder = '';
        if (0 == $length) {
            return '';
        }

        $originalLength = iconv_strlen($string, self::ICONV_CHARSET);
        if ($originalLength > $length) {
            $length -= iconv_strlen($etc, self::ICONV_CHARSET);
            if ($length <= 0) {
                return '';
            }
            $preparedString = $string;
            $preparedlength = $length;
            if (!$breakWords) {
                $preparedString = preg_replace('/\s+?(\S+)?$/', '', iconv_substr($string, 0, $length + 1, self::ICONV_CHARSET));
                $preparedlength = iconv_strlen($preparedString, self::ICONV_CHARSET);
            }
            $remainder = iconv_substr($string, $preparedlength, $originalLength, self::ICONV_CHARSET);
            return iconv_substr($preparedString, 0, $length, self::ICONV_CHARSET) . $etc;
        }

        return $string;
    }

    /**
     * Passthrough to iconv_strlen()
     *
     * @param string $str
     * @return int
     */
    public function strlen($str)
    {
        return iconv_strlen($str, self::ICONV_CHARSET);
    }

    /**
     * Passthrough to iconv_substr()
     *
     * @param string $str
     * @param int $offset
     * @param int $length
     * @return string
     */
    public function substr($str, $offset, $length = null)
    {
        if (is_null($length)) {
            $length = iconv_strlen($str, self::ICONV_CHARSET) - $offset;
        }
        return iconv_substr($str, $offset, $length, self::ICONV_CHARSET);
    }

    /**
     * Split string and appending $insert string after $needle
     *
     * @param string $str
     * @param integer $length
     * @param string $needle
     * @param string $insert
     * @return string
     */
    public function splitInjection($str, $length = 50, $needle = '-', $insert = ' ')
    {
        $str = $this->str_split($str, $length);
        $newStr = '';
        foreach ($str as $part) {
            if ($this->strlen($part) >= $length) {
                $lastDelimetr = iconv_strpos(strrev($part), $needle, null, self::ICONV_CHARSET);
                $tmpNewStr = '';
                $tmpNewStr = $this->substr(strrev($part), 0, $lastDelimetr) . $insert . $this->substr(strrev($part), $lastDelimetr);
                $newStr .= strrev($tmpNewStr);
            } else {
                $newStr .= $part;
            }
        }
        return $newStr;
    }

    /**
     * Binary-safe strrev()
     *
     * @param string $str
     * @return string
     */
    public function strrev($str)
    {
        $result = '';
        $strlen = $this->strlen($str);
        if (!$strlen) {
            return $result;
        }
        for ($i = $strlen-1; $i >= 0; $i--) {
            $result .= iconv_substr($str, $i, 1, self::ICONV_CHARSET);
        }
        return $result;
    }

    /**
     * Binary-safe variant of str_split()
     * + option not to break words
     * + option to trim spaces (between each word)
     * + option to set character(s) (pcre pattern) to be considered as words separator
     *
     * @param string $str
     * @param int $length
     * @param bool $keepWords
     * @param bool $trim
     * @param string $wordSeparatorRegex
     * @return array
     */
    public function str_split($str, $length = 1, $keepWords = false, $trim = false, $wordSeparatorRegex = '\s')
    {
        $result = array();
        $strlen = $this->strlen($str);
        if ((!$strlen) || (!is_int($length)) || ($length <= 0)) {
            return $result;
        }
        // trim
        if ($trim) {
            $str = trim(preg_replace('/\s{2,}/is', ' ', $str));
        }
        // do a usual str_split, but safe for our encoding
        if ((!$keepWords) || ($length < 2)) {
            for ($offset = 0; $offset < $strlen; $offset += $length) {
                $result[] = iconv_substr($str, $offset, $length, self::ICONV_CHARSET);
            }
        }
        // split smartly, keeping words
        else {
            $split = preg_split('/(' . $wordSeparatorRegex . '+)/is', $str, null, PREG_SPLIT_DELIM_CAPTURE);
            $i        = 0;
            $space    = '';
            $spaceLen = 0;
            foreach ($split as $key => $part) {
                if ($trim) {
                    // ignore spaces (even keys)
                    if ($key % 2) {
                        continue;
                    }
                    $space    = ' ';
                    $spaceLen = 1;
                }
                if (empty($result[$i])) {
                    $currentLength = 0;
                    $result[$i]    = '';
                    $space         = '';
                    $spaceLen      = 0;
                }
                else {
                    $currentLength = iconv_strlen($result[$i], self::ICONV_CHARSET);
                }
                $partLength = iconv_strlen($part, self::ICONV_CHARSET);
                // add part to current last element
                if (($currentLength + $spaceLen + $partLength) <= $length) {
                    $result[$i] .= $space . $part;
                }
                // add part to new element
                elseif ($partLength <= $length) {
                    $i++;
                    $result[$i] = $part;
                }
                // break too long part recursively
                else {
                    foreach ($this->str_split($part, $length, false, $trim, $wordSeparatorRegex) as $subpart) {
                        $i++;
                        $result[$i] = $subpart;
                    }
                }
            }
        }
        // remove last element, if empty
        if ($count = count($result)) {
            if (empty($result[$count - 1])) {
                unset($result[$count - 1]);
            }
        }
        // remove first element, if empty
        if (isset($result[0]) && empty($result[0])) {
            array_shift($result);
        }
        return $result;
    }

    /**
     * Split words
     *
     * @param string $str The source string
     * @param bool $uniqueOnly Unique words only
     * @param int $maxWordLenght Limit words count
     * @param string $wordSeparatorRegexp
     * @return array
     */
    function splitWords($str, $uniqueOnly = false, $maxWordLenght = 0, $wordSeparatorRegexp = '\s')
    {
        $result = array();
        $split = preg_split('#' . $wordSeparatorRegexp . '#si', $str, null, PREG_SPLIT_NO_EMPTY);
        foreach ($split as $key => $word) {
            if ($uniqueOnly) {
                $result[$word] = $word;
            }
            else {
                $result[] = $word;
            }
        }
        if ($maxWordLenght && count($result) > $maxWordLenght) {
            $result = array_slice($result, 0, $maxWordLenght);
        }
        return $result;
    }

    /**
     * Clean non UTF-8 characters
     *
     * @param string $string
     * @return string
     */
    public function cleanString($string)
    {
        return iconv(self::ICONV_CHARSET, self::ICONV_CHARSET . '//IGNORE', $string);
    }

    /**
     * Convert the string to UTF-8
     * @param string $string
     * @param string $fromEnc possible values are :: pass, auto, wchar, byte2be, byte2le, byte4be, byte4le, BASE64, UUENCODE, HTML-ENTITIES, Quoted-Printable, 7bit, 8bit, UCS-4, UCS-4BE, UCS-4LE, UCS-2, UCS-2BE, UCS-2LE, UTF-32, UTF-32BE, UTF-32LE, UTF-16, UTF-16BE, UTF-16LE, UTF-8, UTF-7, UTF7-IMAP, ASCII, EUC-JP, SJIS, eucJP-win, SJIS-win, CP51932, JIS, ISO-2022-JP, ISO-2022-JP-MS, Windows-1252, ISO-8859-1, ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6, ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16, EUC-CN, CP936, HZ, EUC-TW, BIG-5, EUC-KR, UHC, ISO-2022-KR, Windows-1251, CP866, KOI8-R, ArmSCII-8
     * @return string Convertst he
     */
    public function convertToUtf8($string, $fromEnc = 'HTML-ENTITIES')
    {
        return mb_convert_encoding($string, $fromEnc, 'UTF-8');
    }
}
