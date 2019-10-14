<?php
class TenderTest extends WebTestCase
{
    protected function setUp(){
        parent::setUp();
        $this->setElements();
    }

    public function setElements()
    {
        return array_merge(parent::setElements(), array(
            )
        );
    }   

    public function microtimeData()
    {
        return array(
            array('1'),
            array('2')
        );
    }

    /**
    *   @dataProvider microtimeData
    */
    public function testMicrotime($iteration)
    {
        $timestamp = explode(' ', microtime());
        echo $timestamp[1];
    }
/*
    public function testTender()
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction('/', array('login' => 'zotov', 'password' => '1'));
    }

    public function testReply()
    {

    }

    public function testTenderClose()
    {

    }

    public function testFileAPI()
    {
        $this->setSpeed('200');
        $this->open('/tenders/list/');
        $this->click('link=Создать тендер');

        if ($this->isElementPresent('id=popup-message-guest'))
        {
            $this->click("css=#popup-message-guest > div.popup-header > div.popup-header-wrapper > span.popup-close");
            $this->authorize('logonarium', '1');
        }
        $this->clickAndWait("link=asdfasdf");
        $this->clickAndWait("link=Редактировать тендер");

        foreach($this->filesSet() as $a){
            foreach($a as $file){
                $this->type('id=inputFile', '/home/gmv/tmp/normal/'.$file);
                $this->fireEvent('id=inputFile', 'blur');
                $this->assertText('link='.$file, $file);
            }
        }
        $this->click("css=input.btn_grey");
        $this->waitForPageToLoad("4000");
    }

    public function authorize($login, $password)
    {
        if($this->isTextPresent('Войти'))
        {
            $this->click('link=Войти');
            $this->waitForElementPresent('css=div.popup-login');
            $this->type('id=p-login-name', $login);
            $this->type('id=p-login-pass', $password);
            $this->click("css=p.p-login-submit > button.btn_grey");
            $this->waitForPageToLoad('2000');
            $this->verifyTextPresent('Мой профиль');
        }
    }

    public function filesSet(){
        return array(
            array('doc.doc'),
            array('docx.docx'),
            array('xls.xls'),
            array('xlsx.xlsx'),
            array('gif.gif'),
            array('jpg.jpg'),
            array('png.png'),
            array('pdf.pdf'),
            array('zip.zip')
        );
    }
*/
}
?>