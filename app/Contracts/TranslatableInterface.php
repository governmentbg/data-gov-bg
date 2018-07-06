<?php

namespace App\Contracts;

interface TranslatableInterface {

    /**
     * Sets the main and fallback locales of the model.
     * @method translate
     * @param  string    $locale
     * @param  string    $fallback_locale
     * @return EloquentModel              Current instance
     */
    public function translate($locale, $fallback_locale = null);

    /**
     * Loads all of the translations
     * @method loadTranslations
     * @return EloquentModel            Current instance
     */
    public function loadTranslations($locale = null);

    /**
     * Gets the values of the translatable fields
     * @method get_translatable_values
     * @return array
     */
    public function get_translatable_values();


    /**
     * Check if the corresponding key is translatable using the
     * $translatable array
     * @method isTranslatable
     * @param  string         $key the key of the $translatable array
     * @return boolean
     */
    public static function isTranslatable($key);

    /**
     * Get the main model field value
     * @method getTranslationId
     * @param  string           $key the key of the $translatable array
     * @return integer|null
     */
    public function getTranslationId($key);


    /**
     * Get the translations for the corresponding key
     * @method translations
     * @param  string       $key the key of $translatable array
     * @return array
     */
    public function translations($key);

    /**
     * Creates new model and the corresponding translations
     * @method create
     * @param  array $attributes
     * @return EloquentModel
     */
    public static function create(array $attributes = []);

    /**
     * Updates the main model and the corresponding translations
     * @method update
     * @param  array $attributes
     * @param  array $options
     * @return EloquentModel
     */
    public function update(array $attributes = [], array $options = []);
}
