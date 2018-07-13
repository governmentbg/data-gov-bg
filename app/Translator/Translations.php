<?php

namespace App\Translator;

use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Traits\RecordSignature;

class Translations
{
    use RecordSignature;

    public $group_id;
    public $translations = [];

    public function __construct($group_id = null, Collection $data = null, $type)
    {
        $this->group_id = $group_id ?: self::nextGroupId();
        $this->type = $type;

        if (!empty($data)) {
            $this->add_collection($data);
        }
    }

    public function toArray()
    {
        $data = array_map(function ($item) {
            return $item->{$this->type};
        }, $this->translations);

        return $data;
    }

    public function add_collection(Collection $data)
    {
        if (!empty($data)) {
            foreach ($data as $translation) {
                $this->translations[$translation->locale] = $translation;
            }
        }
    }

    /**
     * Gets the next "group_id"
     * @method nextGroupId
     * @return integer
     */
    public static function nextGroupId()
    {
        return Translation::max('group_id') + 1;
    }

    /**
     * Gets the translation in the corresponding locale
     * @method get
     * @param  string $locale
     * @return string|boolean|null
     */
    public function get($locale)
    {
        if (!array_key_exists($locale, $this->translations)) {
            $translation = Translation::where('group_id',$this->group_id)->where('locale', $locale)->first();
            $this->translations[$locale] = $translation ?: new Translation([
                'group_id'  => $this->group_id,
                $this->type => null,
                'locale'    => $locale
            ]);
        }

        return $this->translations[$locale];
    }

    /**
     * Check if translation exists in the corresponding locale
     * @method has
     * @param  string  $locale
     * @return boolean
     */
    public function has($locale)
    {
        $result = $this->get($locale);

        return boolval($result);
    }

    /**
     * Creates or updates translations
     * @method set
     * @param  string|array $locale
     * @param  string $value
     */
    public function set($locale, $value = null)
    {
        if (is_array($locale)) {
            foreach ($locale as $loc => $value) {
                $this->set($loc, $value);
            }

            return;
        }

        if ($this->has($locale)) {
            $this->get($locale)->{$this->type} = $value;
            $this->get($locale)->save();
        } else {
            $this->translations[$locale] = Translation::create([
                'group_id'  => $this->group_id,
                $this->type => $value,
                'locale'    => $locale,
            ]);
        }

        $dummy = $this->get('xx');

        if ($locale !== 'xx' && $dummy) {
            $dummy->delete();
        }
    }

    /**
     * Add translation to the current collection
     * @method attach
     * @param  Translation $translation
     * @return void
     */
    public function attach(Translation $translation)
    {
        $translation->group_id = $this->group_id;
        $this->translations[$translation->locale] = $translation;
    }

    /**
     * Get the translation value in main or fallback locale
     * @method in
     * @param  [type] $locale   [description]
     * @param  [type] $fallback [description]
     * @return [type]           [description]
     */
    public function in($locale, $fallback = null)
    {
        if ($this->has($locale) && $this->get($locale)->{$this->type} != null) {
            return $this->get($locale)->{$this->type};
        }

        if ($fallback) {
            return $this->in($fallback);
        }

        // throw new Exceptions\TranslationNotFound($this->group_id);
        return '';
    }
}
