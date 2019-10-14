<?php

class EAutoSuggest extends CWidget
{
        public $target;
        public $url;
        public $options;

        /**
         * Apply Chosen plugin to select boxes.
         */
        public function run()
        {
                // Publish extension assets
                $assets = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias(
                        'ext.EAutoSuggest') . '/assets');

                // Register extension assets
                $cs = Yii::app()->getClientScript();
                $cs->registerCoreScript('jquery');
                $cs->registerCssFile($assets . '/autoSuggest.css');



                // Register jQuery scripts
                $options = CJavaScript::encode($this->options);
                $cs->registerScriptFile($assets . '/jquery.autoSuggest.minified.js',CClientScript::POS_END);

                $cs->registerScript('autosuggest',
                        "$( '{$this->target}' ).autoSuggest('{$this->url}', {$options});", CClientScript::POS_READY);

        }
}

