<div class="clearfix" id="coauthor" style="margin-bottom: 20px;">
        
        <table class="bordered-table coauthor-container span12">
                <tr>
                        <th>Соавтор</th>
                        <th>Специализация</th>
                        <th>URL</th>
                        <th  style="width: 62px;"></th>
                </tr>
                <?php 
                        foreach ($coauthors as $coauthor) {
                                $coauthorError = empty($coauthorErrors[$coauthor->id]) ? array() : $coauthorErrors[$coauthor->id];
                                $this->renderPartial('_getCoAuthorItem', array('coauthor' => $coauthor, 'errors' => $coauthorError));
                        }
                ?>
                
        </table>
       
        <?php echo Chtml::button('Новый соавтор', array('class'=>'btn primary small', 'id'=>'coauthor-create-button'))?>
</div>