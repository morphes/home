<?php

/**
 * Типограф лебедева
 * @author Roman Kuzakov
 */
class TypografController extends FrontController
{

        public function actionIndex()
        {
                if (isset($_POST['content']))
                {
                        $remoteTypograf = new Typograf('UTF-8');

                        $remoteTypograf->htmlEntities();
                        $remoteTypograf->br (false);
                        $remoteTypograf->p (true);
                        $remoteTypograf->nobr (3);
                        $remoteTypograf->quotA ('laquo raquo');
                        $remoteTypograf->quotB ('bdquo ldquo');

                        echo $remoteTypograf->processText($_POST['content']);				
                }
        }

}
