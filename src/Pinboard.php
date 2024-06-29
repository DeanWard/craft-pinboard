<?php

namespace deanward\craftpinboard;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use deanward\craftpinboard\fields\PinBoardField;
use deanward\craftpinboard\models\Settings;
use yii\base\Event;

/**
 * Pinboard plugin
 *
 * @method static Pinboard getInstance()
 * @method Settings getSettings()
 * @author Dean Ward <craft-plugin-support@oveio.io>
 * @copyright Dean Ward
 * @license https://craftcms.github.io/license/ Craft License
 */
class Pinboard extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('pinboard/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {

        // Register our field type
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = PinBoardField::class;
        });

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        // Craft::$app->onInit(function() {
        //     // ...
        // });
    }
}
