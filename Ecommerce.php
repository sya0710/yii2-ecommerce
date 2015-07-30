<?php

namespace sya\ecommerce;

use Yii;
use yii\bootstrap\Html;

class Ecommerce extends \yii\base\Widget {
    
    /**
     * @var string The template for rendering the ecommerce within a panel.
     * The following special variables are recognized and will be replaced:
     * - {statistic}: string, render statistic order.
     * - {charts}: string, render chart order.
     * - {search}: string, render search order template
     * - {items}: string, render item order.
     */
    public $layout = "{statistic}\n{charts}\n{items}";
    
    /**
     * @var string The template for rendering the statistic ecommerce within a statistic. 
     * The following special variables are recognized and will be replaced:
     * - {items}: string, render item statistic
     */
    public $statisticTemplate = "{itemsStatistic}";
    
    /**
     * @var string The template for rendering the item statistic ecommerce within a statistic.
     * The following special variables are recognized and will be replaced:
     * - {statisticHeader}: string, render header statistic
     * - {statisticContent}: string, render content statistic
     */
    public $itemStatistic = <<< HTML
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                {statisticHeader}
                {statisticContent}
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                {statisticHeader}
                {statisticContent}
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                {statisticHeader}
                {statisticContent}
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                {statisticHeader}
                {statisticContent}
            </div>
        </div>
HTML;
    
    /**
     * @var string The template for rendering the header statistic ecommerce within a statistic.
     */
    public $statisticHeader = <<< HTML
        <div class="ibox-title">
            <span class="label label-success pull-right">Monthly</span>
            <h5>Income</h5>
        </div>
HTML;
    
    /**
     * @var string The template for rendering the content statistic ecommerce within a statistic.
     */
    public $statisticContent = <<< HTML
        <div class="ibox-content">
            <h1 class="no-margins">40 886,200</h1>
            <div class="stat-percent font-bold text-success">98% </div>
            <small>Total income</small>
        </div>
HTML;
    
    /**
     * @var boolean The ecommerce run realtime when cart order
     * Defaults to `false`. If set to `true`, ecommerce use nodejs run realtime with cart order, statistical order and chart order
     * If set to `false` ecommerce will be disabled and none of the realtime will be applied.
     */
    public $realtime = false;
    
    /**
     * @var array the HTML attributes for the items element
     */
    public $itemOptions = [];
    
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
            case '{itemsStatistic}':
                return $this->renderItemsStatistic();
            default:
                return false;
        }
    }
    
    /**
     * Render statistic template
     * @return string
     */
    public function renderStatistic(){
        $content = Html::tag('div', $this->statisticTemplate, $this->statisticOptions);
        
        return $content;
    }
    
    /**
     * Render box item statistic
     * @return string
     */
    public function renderItemsStatistic(){
        $replace['{statisticHeader}'] = $this->renderStatisticHeader();
        $replace['{statisticContent}'] = $this->renderStatisticContent();
        
        $this->itemStatistic = strtr($this->itemStatistic, $replace);
        return $this->itemStatistic;
    }
    
    /**
     * Return statistic header
     * @return string
     */
    public function renderStatisticHeader(){
        return $this->statisticHeader;
    }
    
    /**
     * Return statistic content
     * @return string
     */
    public function renderStatisticContent(){
        return $this->statisticContent;
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
        return "items";
    }
    
    private function registerAssets(){
        
    }
}
