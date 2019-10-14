<?php
/**
  	'url':'/test/fileapi',
	'postParams':{'testPost':100},
	'fileName':'file',
	'maxConnections':2,
	'multiple':false, // multiple send

	'onSuccess':function(response){ console.log(response); },
  		Возвращает в функцию json Объект.

	'onProgress':function(event, data){ console.log('progress', event, data); },
	'onError':function(response){ console.log('error', response); },
	'onFinished':function(data){ console.log('success', data); },
 	'onStart':function(data){ }
 */
class FileUpload extends CWidget
{
	public $postParams=array();
	public $config=array();
	public $url = '';

	/* Если переменную выставить в true,
	   то виджет ничего не рендерит, а только подключает скрипты */
	public $onlyScript = false;

	public $htmlOptions = array();

	private static $defOptions = array(
		'fileName' => 'UploadedFile',
		'maxConnections' => 2,
		'multiple' => false,
	);

	public function init()
	{
		if (!isset( $this->htmlOptions['id'] ))
			$this->htmlOptions['id']=$this->getId();
		if (!isset( $this->htmlOptions['multiple'] ))
			$this->htmlOptions['multiple'] = 'multiple';

		$baseDir = dirname(__FILE__);
		$assets = Yii::app()->getAssetManager()->publish($baseDir.DIRECTORY_SEPARATOR.'assets');

		Yii::app()->getClientScript()->registerCoreScript('jquery');
		Yii::app()->getClientScript()->registerScriptFile($assets.'/fileUpload.js');

	}
        
        public function run()
        {
		if ($this->onlyScript)
			return;

		$config = array_merge(self::$defOptions, $this->config);
		$config['postParams']=$this->postParams;
		$config['url']=$this->url;
		$config = CJavaScript::encode($config);

		$output = CHtml::fileField('', '', $this->htmlOptions);
		$output .= '<script type="text/javascript"> /*<![CDATA[*/ $("#'.$this->htmlOptions['id'].'").fileUpload('.$config.'); /*]]>*/ </script>';
		echo $output;
	}


}