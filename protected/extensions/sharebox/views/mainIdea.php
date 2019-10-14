<?php
echo CHtml::openTag('div', $this->htmlOptions);
echo CHtml::tag('p', array(), 'Поделиться идеей с друзьями');
foreach ($this->shareDefinitions as $name => $def) {
	$url = strtr($def['url'], array(
		'{title}' => htmlentities(urlencode($this->title)),
		'{message}' => $this->message,
		'{url}' => htmlentities(urlencode($this->url)),
	));
	$link = CHtml::link('', $url, array(
		'rel' => 'nofollow',
		'target' => '_blank',
		'title' => $def['title'],
		'class' => $classDefinition[$name],
	));
	echo $link.' ';
}
echo CHtml::closeTag('div');