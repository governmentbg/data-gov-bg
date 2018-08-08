<?php

/**
 * Translates the string and converts it to title case
 *
 * @method utrans
 * @param string  $value string to translate
 * @param integer $count singular or plural
 *
 * @return string
 */
function utrans($value, $count = 1, $params = [], $lang = null)
{
    return title_case(trans_choice($value, $count, $params, $lang));
}

/**
 * Translates the string and converts it to upper case
 *
 * @method utrans
 * @param string  $value string to translate
 * @param integer $count singular or plural
 *
 * @return string
 */
function uptrans($value, $count = 1, $params = [], $lang = null)
{
    return mb_strtoupper(trans_choice($value, $count, $params, $lang));
}

/**
 * Translates the string and converts its first letter to upper case
 * Works for cyrillic strings
 *
 * @method mb_ucfirst
 * @param string  $string
 *
 * @return string
 */

function mb_ucfirst($string)
{
    return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
}

/**
 * Translates the string and converts its first letter to capital
 *
 * @method utrans
 * @param string  $value string to translate
 * @param integer $count singular or plural
 *
 * @return string
 */
function uctrans($value, $count = 1, $params = [], $lang = null)
{
    return mb_ucfirst(trans_choice($value, $count, $params, $lang));
}

/**
 * Translates the string and converts it to lower case
 *
 * @method utrans
 * @param string  $value string to translate
 * @param integer $count singular or plural
 *
 * @return string
 */
function ultrans($value, $count = 1, $params = [], $lang = null)
{
    return mb_strtolower(trans_choice($value, $count, $params, $lang));
}

/**
 * Returns an URL adapted to $locale
 *
 * @param string|false   $url        URL to adapt in the current language. If not passed, the current url would be taken
 * @param string|boolean $locale     Locale to adapt, false to remove locale
 * @param array          $attributes attributes to add to the route, if empty, the system would try to extract them from the url
 *
 * @throws UnsupportedLocaleException
 *
 * @return string|false URL translated, False if url does not exist
 */
function turl($url = null, $locale = null, $attributes = null)
{
    return LaravelLocalization::getLocalizedUrl($locale, $url, $attributes);
}

function translate_current($url = null, $locale = null, $attributes = null)
{
    if (count(request()->segments()) != 2) {
        return LaravelLocalization::getLocalizedUrl($locale, $url, $attributes);
    }

    $url_name = request()->segment(2);
    $pages = \App\Content::page()->likePath('/pages')->get()->loadTranslations();

    $page = $pages->first(function ($item) use ($locale, $url_name) {
        foreach ($item->toTranslatedArray(true)['url_name'] as $url) {
            if ($url == $url_name) {
                return true;
            }
        }
    });

    if ($page) {
        $url_name = $page->translate($locale, null)->url_name;

        return LaravelLocalization::getLocalizedUrl($locale, $url_name, $attributes);
    }

    return LaravelLocalization::getLocalizedUrl($locale, $url, $attributes);
}

/**
 * Translates the string without case convertion
 *
 * @param string  $value string to translate
 * @param integer $count singular or plural
 *
 * @return string
 */
function untrans($value, $count = 1, $params = [], $lang = null)
{
    return trans_choice($value, $count, $params, $lang);
}

/* html escaped plural version */
function _hn($singular, $plural, $number)
{
    return htmlspecialchars(ngettext($singular, $plural, $number));
}

/**
 * Converts the locale code ('en') to a country flag
 * matching the "flag-icon-css" npm package
 * @method locale_to_flag
 * @param  string $locale
 * @return string country code
 */
function locale_to_flag($locale)
{
    switch ($locale) {
        case 'en':
            return 'gb';
        break;
        default:
            return $locale;
    }
}

class Lang {
    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function getActive()
    {
        if (!empty($this->activeLocales)) {
           return $this->activeLocales;
        }

        $this->activeLocales = \App\Locale::where('active', 1)->get();

        return $this->activeLocales;
    }
}
