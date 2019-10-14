<?php
class CustomClientScript extends CClientScript
{
	public $lessFiles = array();


	/**
	 * Внедрили новый метод для генерации ссылок на less файлы для того,
	 * чтобы была возможность откорректировать порядок вывода css и less
	 * файлов. Метод является клоном CClientScript->registerLinkTag(...)
	 *
	 * @param null  $relation
	 * @param null  $type
	 * @param null  $href
	 * @param null  $media
	 * @param array $options
	 *
	 * @return $this
	 */
	public function registerLessFile($relation=null,$type=null,$href=null,$media=null,$options=array())
	{
		$this->hasScripts=true;
		if($relation!==null)
			$options['rel']=$relation;
		if($type!==null)
			$options['type']=$type;
		if($href!==null)
			$options['href']=$href;
		if($media!==null)
			$options['media']=$media;
		$this->lessFiles[serialize($options)]=$options;
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerLessFiles',$params);
		return $this;
	}


	/**
	 * Метод был переопределен для того, чтобы поправить
	 * рендериг шапки в <head> Теперь скрипты и стили
	 * вставляются после загрывающего тега </title>, а не до
	 * открывающего <title>, как в оригинальной функции
	 *
	 * @param string $output the output to be inserted with scripts.
	 */
	public function renderHead(&$output)
	{
		
		$html='';

		/*
		 * Мета теги собираем в две промежуточные строки, чтобы обеспечить
		 * нужный порядок вывода данных в теге <head>
		 */
		$metaTemp_first = $metaTemp_second = '';
		foreach($this->metaTags as $meta)
		{
			if (isset($meta['name']) && $meta['name'] == 'description') {
				$metaTemp_first .= CHtml::metaTag($meta['content'],null,null,$meta)."\n";
			} else {
				$metaTemp_second .= CHtml::metaTag($meta['content'],null,null,$meta)."\n";
			}
		}
		$html .= $metaTemp_first . $metaTemp_second;


		foreach($this->linkTags as $link)
			$html.=CHtml::linkTag(null,null,null,null,$link)."\n";

		foreach($this->cssFiles as $url=>$media)
			$html.=CHtml::cssFile($url,$media)."\n";

		foreach($this->lessFiles as $link)
			$html.=CHtml::linkTag(null,null,null,null,$link)."\n";

		foreach($this->css as $css)
			$html.=CHtml::css($css[0],$css[1])."\n";

		if($this->enableJavaScript)
		{
			if(isset($this->scriptFiles[self::POS_HEAD]))
			{
				foreach($this->scriptFiles[self::POS_HEAD] as $scriptFile)
					$html.=CHtml::scriptFile($scriptFile)."\n";
			}

			if(isset($this->scripts[self::POS_HEAD]))
				$html.=CHtml::script(implode("\n",$this->scripts[self::POS_HEAD]))."\n";
		}

		

		
		if($html!=='')
		{
			$count=0;
			// Здесь вносилась правка
			$output=preg_replace('/(<\/title\b[^>]*>)/is','$1<###head###>',$output,1,$count);
			if($count)
				$output=str_replace('<###head###>',$html,$output);
			else
				$output=$html.$output;
		}
	}
}