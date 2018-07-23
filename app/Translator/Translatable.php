<?php

namespace App\Translator;

use App\Repositories\TranslationsRepository;

trait Translatable
{
    protected $repo;

    protected $locale = null;
    protected $fallback_locale = null;
    protected $translatable_handled;

    /**
     * Returns the current model main locale
     * @method translation_locale
     * @return string
     */
    protected function translation_locale()
    {
        return $this->locale ?: \LaravelLocalization::getCurrentLocale();
    }

    public function __construct($attributes = [])
    {
        $this->repo = app(TranslationsRepository::class);
        parent::__construct($attributes);
    }

    /**
     * Returns the current model fallback locale
     * @method translation_fallback
     * @return string
     */
    protected function translation_fallback()
    {
        return $this->fallback_locale ?: config('app.fallback_locale');
    }

    /**
     * Sets the main and fallback locales of the model.
     * @method translate
     * @param  string    $locale
     * @param  string    $fallback_locale
     * @return EloquentModel              Current instance
     */
    public function translate($locale, $fallback_locale = null)
    {
        $this->locale = $locale;
        $this->fallback_locale = $fallback_locale;

        return $this;
    }

    public function loadTranslations($locale = null)
    {
        $translatable_values = $this->get_keys_with_types();
        $this->repo->load($translatable_values, $locale);

        return $this;
    }

    public function get_translatable_values()
    {
        $values = [];

        foreach (array_keys(static::$translatable) as $key) {
            $values[$this->attributes[$key]] = $key;
        }

        return $values;
    }

    public function get_keys_with_types()
    {
        $values = [];

        foreach (static::$translatable as $key => $type) {
            if (isset($this->attributes[$key])) {
                $values[$this->attributes[$key]] = $type;
            }
        }

        return $values;
    }

    public function get_translatable()
    {
        return static::$translatable;
    }

    /**
     * Gets the corresponding key e.g "name" in the current locale.
     * If the translation is not present in the current locale - use fallback
     * @method translatable_get
     * @param  string           $key
     * @return string|boolean
     */
    protected function translatable_get($key)
    {
        $this->translatable_handled = false;

        if (self::isTranslatable($key)) {
            $this->translatable_handled = true;

            // Get the translations for the corresponding key
            // or create new if it does not exist yet
            $translations = $this->translations($key);

            // Get the result for the current or fallback locale
            $result = $translations->in(
                $this->translation_locale(),
                $this->translation_fallback()
            );

            // Reset main and fallback locales
            $this->translate(null, null);

            return $result;
        }
    }

    /**
     * Set the translation for the specific field e.g "name"
     * The value can be an arraym with the locale as an index key (['en'=>'val'])
     * or a string, in which case the value will be set in the current locale
     * @method translatable_set
     * @param  string                 $key
     * @param  string|array           $value
     */
    protected function translatable_set($key, $value, $isUpdate = false)
    {
        $this->translatable_handled = false;

        if (self::isTranslatable($key)) {
            $translations = $this->translations($key);

            if (is_array($value)) {
                $translations->set($value, null, $isUpdate, true);
            } else {
                $translations->set($this->translation_locale(), $value, $isUpdate, true);
            }

            // Make sure model key (e.g "name") matches the translation "group_id"
            $this->attributes[$key] = $translations->group_id;

            $this->translate(null, null);

            $this->translatable_handled = true;
        }
    }

    /**
     * Check if the corresponding key is translatable using the
     * $translatable array
     * @method isTranslatable
     * @param  string         $key the key of the $translatable array
     * @return boolean
     */
    public static function isTranslatable($key)
    {
        return isset(
            static::$translatable)
            && in_array($key, array_keys(static::$translatable)
        );
    }

    /**
     * Get the main model field value
     * @method getTranslationId
     * @param  string           $key the key of the $translatable array
     * @return integer|null
     */
    public function getTranslationId($key)
    {
        if (!self::isTranslatable($key)) {
            throw new Exceptions\KeyNotTranslatable($key);
        }
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } else {
            return null;
        }
    }

    /**
     * Get the type of the translations (varchar, text, bool)
     * @method getTranslationsType
     * @param  string              $key the key of the $translatable array
     * @return string
     */
    protected function getTranslationsType($key)
    {
        return static::$translatable[$key];
    }

    /**
     * Get the translations for the corresponding key
     * @method translations
     * @param  string       $key the key of $translatable array
     * @return array
     */
    public function translations($key)
    {
        $group_id = $this->getTranslationId($key);
        if (! $this->repo->has($group_id)) {
            $translations = $this->repo
                ->add($this->getTranslationsType($key), $group_id)
                ->getLast();

            $group_id = $translations->group_id;
            $this->attributes[$key] = $group_id;
        }

        return $this->repo->get($group_id);
    }

    /**
     * Creates new model and the corresponding translations
     * @method create
     * @param  array $attributes
     * @return EloquentModel
     */
    public static function create(array $attributes = [])
    {
        $translations = [];

        /**
         * select the translatable fields from $attributes create
         * translations from them and then unset them from the $attributes array.
         * The rest of the $attributes fields are used to create the main model
         */
        foreach ($attributes as $key => $value) {
            if (self::isTranslatable($key)) {
                $translations[$key] = $value;
                unset($attributes[$key]);
            }
        }

        // Create the main model
        $model = new static($attributes);

        // Replace empty with Dummy translations
        foreach (self::$translatable as $key => $type) {
            if (!isset($translations[$key]) || $translations[$key] == []) {
                $value = $type == 'bool' ? null : '';
                $translations[$key] = [
                    'xx' => $value, // Dummy
                ];
            }
        }

        // Add translations and point them to the corresponding model keys
        foreach ($translations as $key => $value) {
            $model->translatable_set($key, $value);
        }

        // Persist the model in the DB
        $model->save();

        return $model;
    }

    /**
     * Updates the main model and the corresponding translations
     * @method update
     * @param  array $attributes
     * @param  array $options
     * @return EloquentModel
     */
    public function update(array $attributes = [], array $options = [])
    {
        $translations = [];

        /**
         * select the translatable fields from $attributes, create
         * translations from them and then unset them from the $attributes array.
         * The rest of the $attributes fields are used to create the main modelhe $attributes fields are used to create the main model
        */
        foreach ($attributes as $key => $value) {
            if (self::isTranslatable($key)) {
                $translations[$key] = $value;
                unset($attributes[$key]);
            }
        }

        // Update main model
        parent::update($attributes, $options);

        // Update translations
        foreach ($translations as $key => $value) {
            $this->translatable_set($key, $value, true);
        }

        $this->save();

        return $this;
    }

    public function __set($key, $value)
    {
        // Handle Translatable keys
        $this->translatable_set($key, $value);
        if ($this->translatable_handled) {
            return;
        }

        // Handle not translatable fields
        parent::__set($key, $value);
    }

    public function __get($key)
    {
        $result = $this->translatable_get($key);

        if ($this->translatable_handled) {
            return $result;
        }

        // Handle not translatable fields
        return parent::__get($key);
    }

    /**
     * Use translated fields when casting to array
     * @method attributesToArray
     * @return array
     */
    public function translatedAttributesToArray($all)
    {
        $this->loadTranslations();
        $attributes = parent::attributesToArray();

        foreach ($attributes as $key => $value) {
            if (self::isTranslatable($key)) {
                if ($all) {
                    $attributes[$key] = $this->translations($key)->toArray();
                } else {
                    $attributes[$key] = $this->translatable_get($key)?:null;
                }
            }
        }

        return $attributes;
    }

    public function toTranslatedArray($all = false)
    {
        return array_merge($this->translatedAttributesToArray($all), $this->relationsToArray());
    }

    public function toTranslatedJson($options = 0)
    {
        $json = json_encode($this->jsonSerializeWithTranslations(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    public function jsonSerializeWithTranslations()
    {
        return $this->toTranslatedArray();
    }

    public static function bootTranslatable()
    {
        // When model is deleted, Delete the corresponding translations as well
        self::deleting(function ($model) {
            Translation::whereIn(
                'group_id',
                array_keys($model->get_translatable_values())
            )->delete();
        });
    }

    /**
     * Use custom collection instance when handling translatable models.
     * Inside we can have translation specific functions
     * like toTranslatedArray(), toTranslatedJson(), etc...
     * @method newCollection
     * @param  array        $models
     * @return TranslatableCollection
     */
    public function newCollection(array $models = [])
    {
        return new \App\Collections\TranslatableCollection($models);
    }

    /**
     * Concatenate all translations and their transliterations for given key.
     * @param string $key
     * @return string
     */
    public function concatTranslations($key)
    {
        $localesAll = config('laravellocalization.supportedLocales');
        $translations = collect();
        foreach ($localesAll as $k => $locale) {
            $this->translate($k);
            $translation = $this->translatable_get($key);
            $transliteration = preg_replace('/[^a-z]/i', '', iconv("UTF-8", "US-ASCII//TRANSLIT//IGNORE", $translation));
            $translations->push($translation);
            $translations->push($transliteration);
        }

        $uniqueTranslations = $translations->unique();

        return $uniqueTranslations->implode(' ');
    }
}
