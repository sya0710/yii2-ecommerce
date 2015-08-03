<?php

namespace sya\ecommerce;

use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

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
        
        if (empty($searchModel)) {
            $searchModel = new models\Order;
            $searchModel->scenario = 'search';
        }
        
        if (empty($dataProvider)) {
            $queryParams = Yii::$app->request->getQueryParams();
            $dataProvider = $searchModel->search($queryParams);
        }
        
        $columns = ArrayHelper::getValue($this->itemSettings, 'columns', [
            [
                'class'=>'kartik\grid\SerialColumn',
                'contentOptions'=>['class'=>'kartik-sheet-style'],
                'width'=>'36px',
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
            '_id',
            'creator',
            'created_at',
            'status',
            'note'
        ]);
        $button = Html::a(Yii::t('ecommerce', 'Create'). ' ' .Yii::t('ecommerce', 'Order'), Url::to(['create']) , [
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
