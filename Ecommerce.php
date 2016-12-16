<?php

namespace sya\ecommerce;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use sya\ecommerce\Module;

class Ecommerce extends \yii\base\Widget {

    /**
     * @var string The template for rendering the ecommerce within a panel.
     * The following special variables are recognized and will be replaced:
     * - {statistic}: string, render statistic order.
     * - {charts}: string, render chart order.
     * - {search}: string, render search order template
     * - {items}: string, render item order.
     */
    public $layout = "{statistic}\n{items}";

    /**
     * @var string The template for rendering the item statistic ecommerce within a statistic.
     */
    public $itemStatistic = "{statisticContent}";

    /**
     * @var array The multi column of statistic
     * $statisticColumns = [
     *      [
     *           'header' => '',
     *           'smallHeader' => '',
     *           'time' => '',
     *           'percent' => '',
     *           'totalStatistic' => ''
     *      ],
     *      [
     *           'header' => '',
     *           'smallHeader' => '',
     *           'time' => '',
     *           'percent' => '',
     *           'totalStatistic' => ''
     *      ],
     * ]
     */
    public $statisticColumns = [];

    /**
     * @var string The template for rendering the statistic ecommerce within a statistic.
     * - {header}: title box statistic.
     * - {time}: time of box statistic.
     */
    public $statisticTemplate = <<< HTML
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                {time}
                {header}
            </div>
            <div class="ibox-content">
                {totalStatistic}
                {percent}
                {smallHeader}
            </div>
        </div>
HTML;

    /**
     * @var boolean The ecommerce run realtime when cart order
     * Defaults to `false`. If set to `true`, ecommerce use nodejs run realtime with cart order, statistical order and chart order
     * If set to `false` ecommerce will be disabled and none of the realtime will be applied.
     */
    public $realtime = false;

    /**
     * @var boolean The gridview run pjax
     * Defaults to `false`.
     */
    public $pjax = false;

    /**
     * @var array the HTML attributes for the items element
     */
    public $itemOptions = [];

    /**
     * @var array setting item
     * $itemSettings = [
     *      // Namespace in gridview
     *      'namespaceGridview' => '\kartik\grid\GridView',
     *      'dataProvider' => $dataProvider,
     *      'searchModel' => $searchModel,
     *      'actions' => [
     *         'create' => Url::to(['/ecommerce/base/create']),
     *         'index' => Url::to(['/ecommerce/base/index']),
     *         'update' => Url::to(['/ecommerce/base/update']),
     *         'delete' => Url::to(['/ecommerce/base/delete']),
     *      ]
     * ];
     */
    public $itemSettings = [];

    /**
     * @var array the HTML attributes for the statistic element
     */
    public $statisticOptions = ['class' => 'row'];

    /**
     * @var array the HTML attributes for the search element
     */
    public $searchOptions = [];

    /**
     * @var array the HTML attribtes for the charts element
     */
    public $chartsOptions = [];

    /**
     * Gets the module
     *
     * @param string $m the module name
     *
     * @return Module
     */
    public static function getModule($m)
    {
        $mod = Yii::$app->controller->module;
        return $mod && $mod->getModule($m) ? $mod->getModule($m) : Yii::$app->getModule($m);
    }

    /**
     * Returns the ecommerce module
     *
     * @return Module
     */
    public static function module()
    {
        return self::getModule(Module::MODULE);
    }

    /**
     * Generates the configuration for the widget based on
     * module level defaults
     *
     * @param array $config the widget configuration
     *
     * @throws InvalidConfigException
     * @return array
     */
    public static function getConfig($config = [])
    {
        $module = self::module();
        if (!empty($module->itemSettings)) {
            $config = array_replace_recursive($module->itemSettings, $config);
        }
        return $config;
    }

    /**
     * @inherit doc
     */
    public static function begin($config = [])
    {
        $config = self::getConfig($config);
        return parent::begin($config);
    }

    /**
     * @inherit doc
     */
    public static function widget($config = [])
    {
        $config = self::getConfig($config);
        return parent::widget($config);
    }

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run() {
        parent::run();

        $this->registerAssets();
        $this->initLayout();

        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);

            return $content === false ? $matches[0] : $content;
        }, $this->layout);

        return $content;
    }

    /**
     * Build layout ecommerce template
     * @return string
     */
    protected function initLayout(){
        // Array init element panel template
        $replace = [];

        // If panel template have statistical then call function render statistical
        if (strpos($this->layout, '{statistic}') !== false) {
            $statisticTemplate = $this->renderStatistic();
            $replace['{statistic}'] = $statisticTemplate;
        }

        // If panel template have charts then call function render charts.
        if (strpos($this->layout, '{charts}') !== false) {
            $chartsTemplate = $this->renderCharts();
            $replace['{charts}'] = $chartsTemplate;
        }

        // If panel template have search then call function render search.
        if (strpos($this->layout, '{search}') !== false) {
            $searchTemplate = $this->renderSearch();
            $replace['{search}'] = $searchTemplate;
        }

        // If panel template have items then call function render items.
        if (strpos($this->layout, '{items}') !== false) {
            $items = $this->renderItems();
            $replace['{items}'] = $items;
        }

        $this->layout = strtr($this->layout, $replace);
    }

    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{items}`, `{itemsStatistic}`.
     * @return string|boolean the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{items}':
                return $this->renderItems();
            case '{statisticContent}':
                return $this->renderStatisticContent();
            default:
                return false;
        }
    }

    /**
     * Render statistic template
     * @return string
     */
    public function renderStatistic(){
        $content = Html::tag('div', $this->itemStatistic, $this->statisticOptions);

        return $content;
    }

    /**
     * Return statistic content
     * @return string
     */
    public function renderStatisticContent(){
        $content = '';
        if (!empty($this->statisticColumns) && is_array($this->statisticColumns)){
            $numberColumn = count($this->statisticColumns);
            foreach ($this->statisticColumns as $column) {
                $header = ArrayHelper::getValue($column, 'header', 'Orders');
                $time = ArrayHelper::getValue($column, 'time', 'Annual');
                $percent = ArrayHelper::getValue($column, 'percent', '85%');
                $totalStatistic = ArrayHelper::getValue($column, 'totalStatistic', '40 886,200');
                $smallHeader = ArrayHelper::getValue($column, 'smallHeader', 'New orders');

                // Options element in statistic
                $headerOptions = ArrayHelper::getValue($column, 'headerOptions', []);
                $timeOptions = ArrayHelper::getValue($column, 'timeOptions', []);
                $percentOptions = ArrayHelper::getValue($column, 'percentOptions', []);
                $totalStatisticOptions = ArrayHelper::getValue($column, 'totalStatisticOptions', []);
                $smallHeaderOptions = ArrayHelper::getValue($column, 'smallHeaderOptions', []);

                // Replace input
                if ($header !== false){
                    Html::addCssClass($headerOptions, '');
                    $header = Html::tag('h5', $header, $headerOptions);
                }

                if ($time !== false){
                    if (empty($timeOptions['class']))
                        $timeOptions['class'] = 'label-success';

                    Html::addCssClass($timeOptions, 'label pull-right');
                    $time = Html::tag('span', $time, $timeOptions);
                }

                if ($percent !== false){
                    Html::addCssClass($percentOptions, 'stat-percent font-bold text-success');
                    $percent = Html::tag('div', $percent . ' <i class="fa fa-bolt"></i>', $percentOptions);
                }

                if ($totalStatistic !== false){
                    Html::addCssClass($totalStatisticOptions, 'no-margins');
                    $totalStatistic = Html::tag('h1', $totalStatistic, $totalStatisticOptions);
                }

                if ($smallHeader !== false){
                    Html::addCssClass($smallHeaderOptions, '');
                    $smallHeader = Html::tag('small', $smallHeader, $smallHeaderOptions);
                }

                $statistic = strtr(
                    $this->statisticTemplate,
                    [
                        '{header}' => $header,
                        '{time}' => $time,
                        '{totalStatistic}' => $totalStatistic,
                        '{percent}' => $percent,
                        '{smallHeader}' => $smallHeader,
                    ]
                );

                $content .= Html::tag('div', $statistic, ['class' => 'col-lg-' . (round(12/$numberColumn))]);
            }
        }

        return $content;
    }

    /**
     * Render chart report
     * @return string
     */
    public function renderCharts(){
        return "chart";
    }

    /**
     * Render form search order
     * @return string
     */
    public function renderSearch(){
        return "search";
    }

    /**
     * Render item order
     * @return string
     */
    public function renderItems(){
        // Declare default value properties itemSettings.
        $namespaceGridview = ArrayHelper::getValue($this->itemSettings, 'namespaceGridview', '\kartik\grid\GridView');
        $panel = ArrayHelper::getValue($this->itemSettings, 'panel', [
            'heading' => Yii::t('ecommerce', 'Order'),
        ]);

        // If empty($dataProvider), empty($searchModel) then set default $dataProvider, $searchModel.
        $dataProvider = ArrayHelper::getValue($this->itemSettings, 'dataProvider', '');
        $searchModel = ArrayHelper::getValue($this->itemSettings, 'searchModel', '');

        // Controller action in ecommerce
        $actions = ArrayHelper::getValue($this->itemSettings, 'actions', []);

        // Get Module in ecommerce
        $ecommerce = $this->module();

        // Build default columns
        $columns = ArrayHelper::getValue($this->itemSettings, 'columns', [
            [
                'class'=>'kartik\grid\SerialColumn',
                'contentOptions'=>['class'=>'kartik-sheet-style'],
                'width'=>'36px',
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
            [
                'attribute' => 'quote_id',
                'hAlign'=>'center',
                'vAlign'=>'middle',
            ],
            [
                'attribute' => 'ecommerce_id',
                'hAlign'=>'center',
                'vAlign'=>'middle',
                'value'=>function ($model, $key, $index, $widget) {
                    return Html::a($model->ecommerce_id,
                        Url::to(['update','id'=>$model->_id]),
                        ['title'=>'Xem chi tiết '.$model->ecommerce_id.'']);
                },
                'format'=>'raw',
            ],
            [
                'attribute' => 'customer',
                'vAlign'=>'middle',
                'value'=>function ($model, $key, $index, $widget) {
                    $customer = [];
                    foreach ($model->customer as $filedCustomerOrder => $fieldCustomerTable) {
                        $customer[] = '-' . ArrayHelper::getValue($model->customer, $filedCustomerOrder);
                    }

                    return implode("<br>", $customer);
                },
                'format'=>'raw',
            ],
            [
                'attribute' => 'product_text',
                'vAlign'=>'middle',
                'value'=>function ($model, $key, $index, $widget) {
                    $product = '-'.implode("<br>-", explode(',', $model->product_text));
                    return $product;
                },
                'format'=>'raw',
            ],
            [
                'attribute'=>'created_at',
                'filterType'=>$namespaceGridview::FILTER_DATE_RANGE,
                'format'=>'raw',
                'width'=>'270px',
                'hAlign'=>'center',
                'vAlign'=>'middle',
                'filterWidgetOptions'=>[
                    'pluginOptions'=>[
                        'locale' => [
                            'format'=>'Y-m-d',
                            'separator' => ' to ',
                        ],
                        'opens'=>'left'
                    ],
                    'presetDropdown'=>true,
                    'hideInput'=>true,
                    'convertFormat'=>true,
                ],
                'value'=>function ($model, $key, $index, $widget) {
                    return date('d-m-Y H:i:s', $model->created_at);
                },
            ],
            [
                'class'=>'kartik\grid\EditableColumn',
                'attribute'=>'status',
                'vAlign'=>'middle',
                'width'=>'150px',
                'filterType'=>$namespaceGridview::FILTER_SELECT2,
                'filter'=> Module::$status,
                'filterWidgetOptions'=>[
                    'pluginOptions'=>['allowClear'=>true, 'width' => '150px'],
                ],
                'filterInputOptions'=>['placeholder'=>Yii::t('ecommerce', 'Status')],
                'editableOptions'=> function ($model, $key, $index){
                    return [
                        'header' => Yii::t('ecommerce', 'Status'),
                        'size'=>'md',
                        'placement' => 'top',
                        'inputType'=>\kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'buttonsTemplate' => '',
                        'data' => Module::getListStatus($model->status),
                        'options'=>[
                            'class' => 'syaSelectStatus form-control',
                            'options'=>[
                                'pluginOptions'=>['allowClear'=>true],
                            ],
                        ],
                        'pluginEvents' => [
                            "editableChange"=>"function(event, val) {
                                var element = $(this);
                                var id = element.parents('tr').attr('data-key');
                                var status = jQuery.parseJSON('" . json_encode(Module::$status) . "');
                                var check = confirm('Are you sure?');
                                if (check == true){
                                    $.ajax({
                                        url: '" . \yii\helpers\Url::to(['/ecommerce/ajax/changestatus']) . "',
                                        type: 'post',
                                        dataType: 'json',
                                        data: {status: val, id: id},
                                    }).done(function (data) {
                                        element.find('.syaStatus').text(status[val]);
                                        element.find('.syaSelectStatus').empty();
                                        $.each(data.status, function(key, value) {
                                            element.find('.syaSelectStatus').append( new Option(value, key) );
                                        });
                                    });
                                } else {
                                    element.find('.syaSelectStatus').val('" . Module::STATUS_EMPTY . "');
                                }
                            }",
                        ]
                    ];
                },
                'format'=>'raw',
                'value'=>function ($model, $key, $index, $widget) {
                    return Html::tag('span', ArrayHelper::getValue(Module::$status, $model->status), ['class' => 'syaStatus']);
                },
            ],
            'note_customer',
            [
                'class'=>'kartik\grid\ActionColumn',
                'urlCreator'=>function($action, $model, $key, $index) { return Url::to(['delete','id'=>$model->_id]); },
                'template' => '{delete}',
            ]
        ]);

        $button = Html::a(Yii::t('ecommerce', 'Create'). ' ' .Yii::t('ecommerce', 'Order'), $actions[Module::ACTION_CREATE] , [
            'class' => 'btn btn-info',
            'style' => 'margin-bottom: 10px;',
        ]);

        return $button . $namespaceGridview::widget([
            'panel' => $panel,
            'pjax' => $this->pjax,
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $columns,
            'responsive' => true,
            'hover' => true,
        ]);
    }

    private function registerAssets(){
    }
}
