<ul>
    	<li class="list_li">
                <h4><input type="radio" name="sel_num_gal" class="title_gallery" data-num="1"> Фотогалерея #1</h4>
		<div class="list_div">
		<?php
		$oldNum = -1;
		foreach($photos as $photo) {
	    		if ($photo['num'] != $oldNum and $oldNum > 0) {

				echo '</div></li><li class="list_li">';
				echo '<h4><input type="radio" name="sel_num_gal" class="title_gallery" data-num="'.$photo['num'].'"> Фотогалерея #'.$photo['num'].'</h4>';
				echo '<div class="list_div">';
			}

		    	$image = UploadedFile::model()->findByPk($photo['image_id']);
			echo CHtml::image('/'.$image->getPreviewName(Config::$preview['crop_80']), '', array('width' => 80));

			$oldNum = $photo['num'];
		}
		?>
		</div>
    	</li>
</ul>


<style>
	.list_li {overflow-x: auto; width: 320px;}
    	.list_div {
		width: 160px;
		background-color: transparent;
		padding: 6px 10px;
		border: 1px solid transparent;
	}
    	.list_div.select {
		background-color: pink;
		border: 1px solid #ddd;
	}
</style>
<script type="text/javascript">
	$('.list_div').each(function(index, element){
		var w = $(element).find('img').size()*80;
		$(element).css('width', w+'px');
	});
    	$('input.title_gallery').click(function(){
		    $('.list_div').removeClass('select');
		    $(this).parent('h4').next('div').addClass('select');
	});
</script>