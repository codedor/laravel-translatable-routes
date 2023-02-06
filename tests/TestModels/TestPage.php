<?php

namespace Codedor\TranslatableRoutes\Tests\TestModels;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TestPage extends Model
{
    use HasTranslations;

    public $translatable = ['name', 'slug'];

    public $fillable = ['name', 'slug'];

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $locale = app()->getLocale();

        return $this->where("slug->{$locale}", $value)->firstOrFail();
    }
}
