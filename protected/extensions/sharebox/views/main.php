<?php
echo CHtml::openTag('p', $this->htmlOptions);
echo CHtml::tag('span', array(), 'Рассказать друзьям ', true);
foreach ($this->shareDefinitions as $name => $def) {
	$linkText = $def['name'];
	$url = strtr($def['url'], array(
		'{title}' => htmlentities(urlencode($this->title)),
		'{message}' => $this->message,
		'{url}' => htmlentities(urlencode($this->url)),
	));
	$link = CHtml::link($linkText, $url, array(
		'rel' => 'nofollow',
		'target' => '_blank',
		'title' => $def['title'],
		'class' => $classDefinition[$name],
	));
	echo $link.' ';
}
echo CHtml::closeTag('p');
?>