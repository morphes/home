<div class="clearfix" id="source">
        
        <table class="bordered-table source-container span12">
                <tr>
                        <th>Источник</th>
                        <th>URL</th>
                        <th style="width: 62px;"></th>
                </tr>
                <?php 
                        foreach ($sources as $source) {
                                $this->renderPartial('_sourceItem', array('sourceMultiple' => $source, 'form'=>$form));
                        }
                ?>
                
        </table>
       
        <?php echo Chtml::button('Новый источник', array('class'=>'btn primary small', 'id'=>'source-create-button'))?>
</div>
