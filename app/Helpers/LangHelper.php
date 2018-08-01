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
    return strtoupper(trans_choice($value, $count, $params, $lang));
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
    return ucfirst(trans_choice($value, $count, $params, $lang));
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
    return strtolower(trans_choice($value, $count, $params, $lang));
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
