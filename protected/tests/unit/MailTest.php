<?php

class MailTest extends CDbTestCase
{
        public $fixtures = array(
                'user' => 'User',
                'mail_template' => ':mail_template',
        );

        /**
         * Mail send test
         */
        public function testSend()
        {
                /**
                 * Manual mail send
                 */
                $mail = Yii::app()->mail->create()
                        ->to('roman.kuzakov@gmail.com')
                        ->subject('Test')
                        ->from(array('author'=>'Tester', 'email'=>'test@myhome.ru'))
                        ->message('Example message')
                        ->priority(EmailComponent::PRT_HIGHT)
                        ->send(false);

                $mail = unserialize($mail);
                $this->assertNotNull($mail);
                $this->assertEquals(EmailComponent::STATUS_SENDED, $mail['status']);

                /**
                 * Template mail send
                 */
                $mail = Yii::app()->mail->create('tmpPass')
                        ->to(array('roman.kuzakov@gmail.com'))
                        ->params(array('login'=>$this->user['user']['login']))
                        ->notifier(true)
                        ->send(false);

                $mail = unserialize($mail);
                $this->assertNotNull($mail);
                $this->assertEquals(EmailComponent::STATUS_SENDED, $mail['status']);

                /**
                 * Mixed mail send
                 */
                $subject = 'Test';
                $author = 'MyHome Team';
                $mail = Yii::app()->mail->create('tmpPass')
                        ->to(array($this->user['user']['email'], $this->user['poweradmin']['email']))
                        ->params(array('login'=>$this->user['user']['login']))
                        ->subject($subject)
                        ->from(array('author'=>$author))
                        ->send(false);
                $mail = unserialize($mail);
                $this->assertNotNull($mail);
                $this->assertEquals(EmailComponent::STATUS_SENDED, $mail['status']);
                $this->assertEquals($subject, $mail['subject']);
                $this->assertEquals($author, $mail['from_author']);
        }
}