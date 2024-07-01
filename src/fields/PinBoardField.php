<?php

namespace deanward\craftpinboard\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use yii\db\ExpressionInterface;
use yii\db\Schema;
use deanward\craftpinboard\PinboardBundle;

use craft\elements\Entry;
use craft\elements\Asset;
use craft\elements\User;
use craft\elements\Category;

use function Symfony\Component\String\b;

/**
 * Pin Board Field field type
 */
class PinBoardField extends Field
{

    public array $backdropSources = [];
    public array $entrySources = [];
    public array $userSources = [];
    public array $categorySources = [];
    public $entryId = null;

    public array $assets = [];


    public static function displayName(): string
    {
        return Craft::t('pinboard', 'Pin Board Field');
    }

    public static function icon(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 500 500"><path stroke="null" d="m284.31784,302.36577c0.7262,4.28654 0.33513,9.08255 0.31863,13.42328c-0.11426,30.487 -11.48674,59.74187 -31.37576,82.73772c-2.82707,-1.11493 -5.55107,-2.70692 -8.2468,-4.10867c-11.10626,-5.77429 -22.31736,-12.01858 -33.36767,-17.99371c-6.97874,-3.77355 -26.17395,-15.46878 -32.82345,-17.20331l20.36373,-32.20327c5.3891,-8.56543 8.86286,-14.30851 11.33478,-24.30455c4.43143,0.77097 13.29194,0.31628 18.13388,0.30862l48.52724,-0.0053c1.52897,0.00059 6.1954,0.76036 7.13541,-0.65081z" transform="translate(0 -288.533) scale(1.6 1.6)" fill="#2098A6"/><path stroke="null" d="m34.47086,302.67321c5.22301,0.44703 10.42894,0.36163 15.66491,0.36163l42.83776,-0.00589c5.19297,0 10.39655,0.09541 15.58245,-0.20673c2.18391,11.29827 8.18378,19.53505 14.24254,28.92917l11.9909,18.88424c2.05846,3.22109 4.39609,6.39212 6.27079,9.71275c5.94568,8.42467 11.56507,19.95617 18.62568,27.2612c3.19459,-3.2476 5.68182,-7.4723 8.15904,-11.27412l10.97786,-17.11437c6.6495,1.73452 25.84472,13.42976 32.82345,17.20331c11.05031,5.97513 22.26141,12.21942 33.36767,17.99371c2.69573,1.40176 5.41973,2.99375 8.2468,4.10867c-10.23517,12.60166 -27.75829,24.57253 -42.21345,31.15136c-18.42071,8.38344 -12.43086,1.94891 -22.7797,18.27524c-3.73821,5.8968 -19.99504,34.18221 -26.35064,35.67408c-8.172,1.91887 -11.68286,-5.64825 -15.4894,-11.27706c-2.30347,-3.40721 -22.38332,-35.30833 -24.09606,-36.24185c-4.3637,-2.37886 -9.32697,-3.90194 -13.855,-6.0034c-37.7137,-17.5025 -68.57116,-55.13551 -73.18458,-97.12692c-1.25039,-5.51514 -1.74454,-24.84935 -0.82103,-30.30501z" transform="translate(0 -288.533) scale(1.6 1.6)" fill="#65D0D2"/><path stroke="null" d="m34.47086,302.67321c5.22301,0.44703 10.42894,0.36163 15.66491,0.36163l42.83776,-0.00589c5.19297,0 10.39655,0.09541 15.58245,-0.20673c2.18391,11.29827 8.18378,19.53505 14.24254,28.92917l11.9909,18.88424c2.05846,3.22109 4.39609,6.39212 6.27079,9.71275c-6.47693,-1.47773 -12.91912,-3.35538 -19.34069,-5.06281l-66.17463,-16.8888c-6.71547,-1.61732 -13.86148,-2.75227 -20.25301,-5.41855c-1.25039,-5.51514 -1.74454,-24.84935 -0.82103,-30.30501z" transform="translate(0 -288.533) scale(1.6 1.6)" fill="#2098A6"/><path stroke="null" d="m34.47086,302.67321c0.5183,-2.04904 0.44998,-4.5133 0.71796,-6.6336c12.03566,-95.25752 129.137,-143.28419 204.80351,-78.92471c25.0979,21.34732 41.67513,52.27782 44.3255,85.25087c-0.94,1.41118 -5.60643,0.6514 -7.13541,0.65081l-48.52724,0.0053c-4.84195,0.00766 -13.70245,0.46234 -18.13388,-0.30862c-2.47192,9.99605 -5.94568,15.73912 -11.33478,24.30455l-20.36373,32.20327l-10.97786,17.11437c-2.47722,3.80182 -4.96445,8.02652 -8.15904,11.27412c-7.06061,-7.30503 -12.68,-18.83653 -18.62568,-27.2612c-1.8747,-3.32063 -4.21233,-6.49166 -6.27079,-9.71275l-11.9909,-18.88424c-6.05876,-9.39412 -12.05863,-17.6309 -14.24254,-28.92917c-5.18591,0.30214 -10.38948,0.20673 -15.58245,0.20673l-42.83776,0.00589c-5.23597,0 -10.4419,0.0854 -15.66491,-0.36163z" transform="translate(0 -288.533) scale(1.6 1.6)" fill="#05495D"/><path stroke="null" d="m108.55598,302.82222c-5.36613,-36.61349 23.17785,-69.07237 60.89272,-61.8498c27.76064,5.31548 46.35746,34.21638 41.07261,61.74084c-2.47192,9.99605 -5.94568,15.73912 -11.33478,24.30455l-20.36373,32.20327l-10.97786,17.11437c-2.47722,3.80182 -4.96445,8.02652 -8.15904,11.27412c-7.06061,-7.30503 -12.68,-18.83653 -18.62568,-27.2612c-1.8747,-3.32063 -4.21233,-6.49166 -6.27079,-9.71275l-11.9909,-18.88424c-6.05876,-9.39412 -12.05863,-17.6309 -14.24254,-28.92917z" transform="translate(0 -288.533) scale(1.6 1.6)" fill="#F36337"/><path stroke="null" d="m158.0403,274.77769c21.72721,-2.53317 29.89273,32.71803 3.01672,36.72245c-25.83176,0.82633 -27.09098,-34.27763 -3.01672,-36.72245z" transform="translate(0 -288.533) scale(1.6 1.6)" fill="#921C2D"/></svg>';
    }

    public static function phpType(): string
    {
        return 'mixed';
    }

    public static function dbType(): array|string|null
    {
        // Replace with the appropriate data type this field will store in the database,
        // or `null` if the field is managing its own data storage.
        return Schema::TYPE_STRING;
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // ...
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return is_array($value) ? $value : [];
    }



    public function getSettingsHtml(): ?string
    {
        $view = Craft::$app->getView();
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        $entrySources = $this->getEntrySources();

        return $view->renderTemplate($pluginHandle . '/fields/_pinBoard_settings.twig', [
            'field' => $this,
            'sources' => [
                'assets' => $this->getAssetSources(),
                'entries' => $entrySources,
                'users' => $this->getUserSources(),
                'categories' => $this->getCategorySources(),
            ]
        ]);
    }

    private function getEntrySources(): array
    {
        $entrySources = [];

        $types = Entry::sources('settings');

        foreach ($types as $type) {

            if (isset($type['key']) && isset($type['label'])) {
                $entrySources[] = [
                    'value' => $type['key'],
                    'label' => Html::encode($type['label'])
                ];
            }
        }

        return $entrySources;
    }

    private function getAssetSources(): array
    {
        $assetSources = [];

        $types = Asset::sources('settings');

        foreach ($types as $type) {

            if (isset($type['key']) && isset($type['label'])) {
                $assetSources[] = [
                    'value' => $type['key'],
                    'label' => $type['label'],
                ];
            }
        }

        return $assetSources;
    }

    private function getUserSources(): array
    {
        $userSources = [];

        $types = User::sources('settings');

        foreach ($types as $type) {

            if (isset($type['key']) && isset($type['label'])) {
                $userSources[] = [
                    'value' => $type['key'],
                    'label' => $type['label'],
                ];
            }
        }

        return $userSources;
    }

    private function getCategorySources(): array
    {
        $categorySources = [];

        $types = Category::sources('settings');

        foreach ($types as $type) {

            if (isset($type['key']) && isset($type['label'])) {
                $categorySources[] = [
                    'value' => $type['key'],
                    'label' => $type['label'],
                ];
            }
        }

        return $categorySources;
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        //are we in the CP?
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return $value;
        } else {
            return $this->prepareDataForFrontEnd($value);
        }

        return $value;
    }

    private function prepareDataForFrontEnd($value)
    {
        $backdropElement = null;

        if ($value['backdropId']) {
            $backdropElement = Craft::$app->getElements()->getElementById($value['backdropId'][0]);
        }

        $pinData = json_decode($value['vuedata'], true);

        $pins = [];

        $ids = array_column($pinData, 'id');

        $elementQueryResult = Entry::find()->id($ids)->all();
        $userQueryResult = User::find()->id($ids)->all();
        $categoryQueryResult = Category::find()->id($ids)->all();

        $queryResult = array_merge($elementQueryResult, $userQueryResult, $categoryQueryResult);


        foreach ($pinData as $pin) {

            $elements = array_values(array_filter($queryResult, function ($entry) use ($pin) {
                return $entry->id == $pin['id'];
            }));

            //Skip missing elements to avoid errors
            //Skip pins with no coordinates
            if (count($elements) == 0 || !$pin['x'] || !$pin['y']) {
                continue;
            }

            $element = $elements[0];

            $pins[] = [
                'x' => $pin['x'],
                'y' => $pin['y'],
                'title' => $element->title ?? $element->friendlyName ?? $element->username,
                'element' => $element
            ];
        }

        return [
            'backdrop' => $backdropElement,
            'pins' => $pins
        ];
    }

    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $site = Craft::$app->getSites()->getCurrentSite();
        $view = Craft::$app->getView();
        $view->registerAssetBundle(PinboardBundle::class);
        $namespacedId = $view->namespaceInputId($this->handle);
        $view->registerJs("window.dispatchEvent(new CustomEvent('bootPinbaordField', { detail: 'vue-app-$namespacedId' }));", $view::POS_END);
        $backdropElement = null;

        if ($value['backdropId'] ?? null) {
            $backdropElement = Craft::$app->getElements()->getElementById($value['backdropId'][0]);
        }

        return $view->renderTemplate('pinboard/fields/_pinBoard_input.twig', [
            'name' => $this->handle,
            'id' => Html::id($this->handle),
            'namespacedId' => $namespacedId,
            'field' => json_encode($this),
            'value' => $value,
            'element' => $element,
            'backdropElementType' => Asset::class,
            'site' => $site->handle,
            'backdropElement' => $backdropElement ? [$backdropElement] : [],
            'entryElementType' => Entry::class,
            'userElementType' => User::class,
            'categoryElementType' => Category::class,
            'sources' => [
                'backdrops' => $this->backdropSources,
                'assets' => $this->mapSources($this->getAssetSources()),
                'entries' => $this->mapSources($this->getEntrySources()),
                'users' => $this->mapSources($this->getUserSources()),
                'categories' => $this->mapSources($this->getCategorySources()),
            ],
            'pinSources' => [
                'entrySources' => $this->entrySources,
                'userSources' => $this->userSources,
                'categorySources' => $this->categorySources,
            ],
            'pins' => [
                'entries' => $this->getSelectedElements($value, Entry::class, 'entries'),
                'users' => $this->getSelectedElements($value, User::class, 'users'),
                'categories' => $this->getSelectedElements($value, Category::class, 'categories'),
            ]
        ]);
    }

    private function getSelectedElements($value, string $elementType, string $path): array
    {
        $elements = [];

        //does $value['pins'][$path] exist?
        if (!isset($value['pins'][$path])) {
            return $elements;
        }

        if ($value['pins'][$path]) {
            foreach ($value['pins'][$path] as $elementId) {
                $element = Craft::$app->getElements()->getElementById($elementId, $elementType);
                if ($element) {
                    $elements[] = $element;
                }
            }
        }

        return $elements;
    }

    private function mapSources(array $sources): array
    {
        $mapped = [];

        foreach ($sources as $source) {
            $mapped[] = $source['value'];
        }

        return $mapped;
    }

    public function getElementValidationRules(): array
    {
        return [];
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        return StringHelper::toString($value, ' ');
    }

    public function getElementConditionRuleType(): array|string|null
    {
        return null;
    }

    public static function queryCondition(
        array $instances,
        mixed $value,
        array &$params,
    ): ExpressionInterface|array|string|false|null {
        return parent::queryCondition($instances, $value, $params);
    }
}
