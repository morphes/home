<?php
echo CHtml::openTag('div', $this->htmlOptions);
echo CHtml::tag('p', array(), 'Поделиться с друзьями', true);
foreach ($this->shareDefinitions as $name => $def) {
	
	$url = strtr($def['url'], array(
		'{title}' => htmlentities(urlencode($this->title)),
		'{message}' => $this->message,
		'{url}' => htmlentities(urlencode($this->url)),
	));
	$link = CHtml::link('<i></i>', $url, array(
		'rel' => 'nofollow',
		'target' => '_blank',
		'title' => $def['title'],
		'class' => $classDefinition[$name],
	));
	echo $link.' ';
}
echo CHtml::tag('div', array('class' => 'clear'), '', true);
echo CHtml::closeTag('div');
?>