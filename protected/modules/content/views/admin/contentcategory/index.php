<?php
$this->breadcrumbs = array(
    'Категории контента',
);
?>

<h1>Категории контента</h1>

<style>
        .categories li { margin-top: 10px; }
</style>


<div class="categories">
        
<?php

        $current_level = 0;
        $counter = 0;

        echo CHtml::openTag('ul');

        foreach ($categories as $node) {

                $node_id = $node->id;

                if ($node->level == $current_level) {
                        if ($counter > 0)
                                echo CHtml::closeTag('li');

                } elseif ($node->level > $current_level) {
                        echo CHtml::openTag('ul');
                        $current_level = $current_level + ($node->level - $current_level);

                } elseif ($node->level < $current_level) {
                        echo str_repeat(CHtml::closeTag('li') . CHtml::closeTag('ul'), $current_level - $node->level) . CHtml::closeTag('li');
                        $current_level = $current_level - ($current_level - $node->level);
                }

                echo CHtml::openTag('li');
                echo CHtml::link($node->title, $this->createUrl('update', array('id'=>$node->id)));
                echo '&nbsp&nbsp';
                echo CHtml::link('(удал.)', $this->createUrl('delete', array('id'=>$node->id)));
                ++$counter;
        }

        echo str_repeat(CHtml::closeTag('li') . CHtml::closeTag('ul'), $current_level) . CHtml::closeTag('li');

        echo CHtml::closeTag('ul');

        ?>

</div>