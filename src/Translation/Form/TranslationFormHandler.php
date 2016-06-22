<?php namespace Anomaly\TranslatorModule\Translation\Form;

use Anomaly\Streams\Platform\Addon\AddonCollection;
use Anomaly\Streams\Platform\Addon\FieldType\FieldType;
use Illuminate\Filesystem\Filesystem;

/**
 * Class TranslationFormHandler
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\TranslatorModule\Translation\Form
 */
class TranslationFormHandler
{

    /**
     * Handle the form.
     *
     * @param TranslationFormBuilder $builder
     * @param AddonCollection        $addons
     * @param Filesystem             $files
     */
    public function handle(TranslationFormBuilder $builder, AddonCollection $addons, Filesystem $files)
    {
        $translated = [];

        /* @var FieldType $field */
        foreach ($builder->getFormFields()->enabled() as $field) {
            $translated[$field->getLocale()][$field->getConfig()['file']][$field->getField()] = $builder->getFormValue(
                $field->getInputName()
            );
        }

        $addon = $addons->get($builder->getEntry());

        foreach ($translated as $locale => $translations) {

            $directory = $addon->getPath('resources/lang/' . $locale);

            $files->makeDirectory($directory, 0755, true, true);

            foreach ($translations as $file => $keys) {

                $require = [];

                foreach ($keys as $key => $value) {

                    if (empty($value)) {
                        continue;
                    }

                    $key = explode('.', $key);

                    array_shift($key);

                    $key = implode('.', $key);

                    array_set($require, $key, $value);
                }

                $require = view('module::stub', compact('require'))->render();

                $files->put($directory . DIRECTORY_SEPARATOR . $file, '<?php' . $require);
            }
        }
    }
}