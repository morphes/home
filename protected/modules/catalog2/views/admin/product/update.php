<?php
$this->breadcrumbs=array(
        'Каталог товаров'=>array('/catalog2/admin/category/index'),
        $category->name=>array('index', 'cid'=>$category->id),
        'Редактирование товаров',
);
?>

<h1>Редактирование товаров (<?php echo $category->name; ?>)</h1>

<?php echo $this->renderPartial('_form', array('category'=>$category, 'product'=>$product, 'errors'=>$errors, 'products'=>$products)); ?>