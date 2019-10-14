<?php

class EmailComponent extends CComponent
{
        /**
         * @var string table name for logging mail's
         */
        public $mail_table = 'mail_log';

        /**
         * @var string the content-type of the email
         */
        public $contentType = 'UTF-8';

        /**
         * @var string language to encode the message in (eg "Japanese", "ja", "English", "en" and "uni" (UTF-8))
         */
        public $language = 'uni';

        /**
         * @var integer line length of email as per RFC2822 Section 2.1.1
         */
        public $lineLength = 70;

        /**
         * @var integer Gearman Client response timeout. Uses for non-background tasks
         */
        public $gc_timeout = 3000;

        /**
         * @var string Path to views for emails
         */
        public $viewPath = 'themes/myhome/views/email/template.php';

        /**
         * @var unique mail hash, generated on md5(to, subject, time())
         */
        private $mailhash;

        private $template;
        private $params;
        private $priority;
        private $status;
        private $useView;
        private $enable_log;

        private $to;
        private $subject;
        private $from_email;
        private $from_author;
        private $message;
        private $headers;
        private $notifier;

        const PRT_LOW = 'Low';
        const PRT_NORMAL = '';
        const PRT_HIGHT = 'High';

        const STATUS_GENERATING = 0;
        const STATUS_SENDED = 1;
        const STATUS_NOT_SENDED = 2;
        const STATUS_OPENED = 3;


        public function init()
        {
                $this->template = null;
                $this->status = self::STATUS_GENERATING;
                $this->params = array();
                $this->subject = null;
                $this->from_email = null;
                $this->from_author = null;
                $this->message = null;
                $this->priority = self::PRT_NORMAL;
                $this->headers = '';
                $this->useView = true;
                $this->notifier = false;
                $this->mailhash = '';
                $this->enable_log = true;
        }

        /**
         * Init attributes for template mail
         * @param string $template
         * @return EmailComponent
         * @throws CException
         */
        public function create($template = null)
        {
                /**
                 * Clearing attributes
                 */
                $mail = new self();
                $mail->init();

                if($template) {
                        $template_data = Yii::app()->db->createCommand()
                                ->from('mail_template t')
                                ->where('t.key = :template', array(':template'=>$template))
                                ->queryRow();

                        if(empty($template_data))
                                throw new CException('Template not exist');

                        $mail->template = $template_data;

                        if(is_null($mail->subject))
                                $mail->subject = $template_data['subject'];
                        if(is_null($mail->from_email))
                                $mail->from_email = $template_data['from'];
                        if(is_null($mail->from_author))
                                $mail->from_author = $template_data['author'];
                        if(is_null($mail->message))
                                $mail->message = $template_data['data'];
                }

                return $mail;
        }

       /**
        * Setter for recipient's
        * @param string|array $emails Emails of recipients
        * @return EmailComponent
        */
        public function to($emails)
        {
                if(!is_array($emails))
                        $emails = array($emails);

                $this->to = $emails;

                return $this;
        }

        /**
         * Setter for from_author and from_email
         * @param mixed $from author name or array("author"=>"", "email"=>"")
         * @return EmailComponent
         */
        public function from($from)
        {
                if(!is_array($from)) {
                        $this->from_author = $from;
                        return $this;
                }

                if(isset($from['author']))
                        $this->from_author = $from['author'];

                if(isset($from['email']))
                        $this->from_email = $from['email'];

                return $this;
        }

        /**
         * Setter for subject
         * @param string $subject
         * @return EmailComponent
         */
        public function subject($subject)
        {
                $this->subject = $subject;
                return $this;
        }

        /**
         * Setter for message
         * @param string $message
         * @return EmailComponent
         */
        public function message($message)
        {
                $this->message = $message;
                return $this;
        }

        /**
         * Setter for notifier (Notifier enabled if true)
         * @param bool $enabled
         * @return EmailComponent
         */
        public function notifier($enabled=false)
        {
                if($enabled)
                        $this->notifier = true;
                else
                        $this->notifier = false;

                return $this;
        }

        /**
         * Setter for params
         * @param $params
         * @return EmailComponent
         * @throws CException
         */
        public function params($params)
        {
                if(!is_array($params))
                        throw new CException('Incorrected params format');

                $this->params = $params;
                return $this;
        }

        /**
         * Setter for usage view flag
         * If true - message body will be generated with view file
         * @param bool $flag
         * @return EmailComponent
         */
        public function useView($flag = true)
        {
                if(!$flag)
                        $this->useView = false;
                else
                        $this->useView = true;

                return $this;
        }

        /**
         * Setter for mail priority
         * @param string $priority value of priority (@see self::PRT_ const's)
         * @return EmailComponent
         */
        public function priority($priority = null)
        {
                if($priority === self::PRT_LOW || $priority === self::PRT_HIGHT)
                        $this->priority = $priority;
                return $this;
        }

        /**
         * Prepare mail and insert into queue for sending
         * @param bool $background - if false, function will return result from gearman worker
         * if true - mail will be send as background task
         * @param bool $debug - mail send simulation flag
         * @throws CException
         * @return mixed
         */
        public function send($background = true, $debug = false)
        {
                if(empty($this->to))
                        throw new CException('Empty recipient list');

                $this->mailhash = md5(serialize($this->to) . $this->subject . time());

                $this->generateMail();

                $command = "do{$this->priority}";

                if($background)
                        $command.= "Background";

                $gearman_client = Yii::app()->gearman->client();
                $gearman_client->setTimeout($this->gc_timeout);

                $mail = array(
			'mailhash'     => $this->mailhash,
			'language'     => $this->language,
			'subject'      => $this->subject,
			'to'           => $this->to,
			'from_email'   => $this->from_email,
			'from_author'  => $this->from_author,
			'message'      => $this->message,
			'headers'      => $this->headers,
			'mail_table'   => $this->mail_table,
			'enable_log'   => $this->enable_log,
			'template_key' => isset($this->template['key'])
				? $this->template['key']
				: '',
                );

                return $gearman_client->$command('mail:sendmail', serialize($mail));
        }

        /**
         * Preparing message for send.
         * Not required for sending mail.
         * Usage for getting generated mail attributes.
         * @return $this
         */
        public function prepare()
        {
                $this->generateMail();
                return $this;
        }

        /**
         * Replace message, subject and author params, init default values for empty attributes
         * Call function generateHeaders
         * @throws CException
         * @return bool
         */
        private function generateMail()
        {
                if(is_array($this->to))
                        $this->to = implode(', ', $this->to);
                if(is_null($this->subject))
                        $this->subject = '';
                if(is_null($this->from_email))
                        $this->from_email = '';
                if(is_null($this->from_author))
                        $this->from_author = '';
                if(is_null($this->message))
                        $this->message = '';
                foreach ($this->params as $key => $value) {
                        $this->subject = str_replace(':'.$key.':', $value, $this->subject);
                        $this->from_author = str_replace(':'.$key.':', $value, $this->from_author);
                        $this->message = str_replace(':'.$key.':', $value, $this->message);
                }

                if($this->notifier) {
                        $url = Yii::app()->homeUrl . "/download/emailstub/mid/{$this->mailhash}";
                        $this->message.="<img src='" . $url . "'>";
                }

                $wrapped_message = wordwrap($this->message, $this->lineLength);
                $this->message = $this->renderFile($wrapped_message);
                $this->generateHeaders();

                return true;
        }

        /**
         * Generate mail headers
         * @return bool
         */
        private function generateHeaders()
        {
                $headers = "";
                $headers .= "From: =?{$this->contentType}?B?" . base64_encode($this->from_author) . "?=<{$this->from_email}>\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-Type: text/html; charset={$this->contentType}\n";
                $headers .= "Content-Transfer-Encoding: 8bit\n";
                $this->headers = $headers;
                return true;
        }

        /**
         * Init new mail from existed mail in db
         * @param integer $mail_id
         * @return EmailComponent|null
         */
        public function select($mail_id)
        {
                $mail = Yii::app()->db->createCommand()
                        ->from($this->mail_table)
                        ->where('id=:id', array(':id'=>(int) $mail_id))
                        ->queryRow();

                if(!$mail)
                        return null;

                $this->from_author = $mail['from_author'];
                $this->from_email = $mail['from_email'];
                $this->message = $mail['message'];
                $this->subject = $mail['subject'];
                $this->to = $mail['to'];
                $this->priority = $mail['priority'];
                $this->status = $mail['status'];

                return $this;
        }

        /**
         * Getter for mail status
         * @return mixed
         */
        public function getStatus()
        {
                return $this->status;
        }


        /**
         * Getter for subject
         * @return string
         */
        public function getSubject()
        {
                return $this->subject;
        }

        /**
         * Getter for from
         * @return array
         */
        public function getFrom()
        {
                return array(
                        'from_author'=>$this->from_author,
                        'from_email'=>$this->from_email,
                );
        }

        /**
         * Getter for message
         * @return string
         */
        public function getMessage()
        {
                return $this->message;
        }

        /**
         * Getter for priority
         * @return string
         */
        public function getPriority()
        {
                return $this->priority;
        }

        /**
         * Renders a view file.
         * @param array $content optional data to be extracted as local view variables
         * @return mixed the rendering result if required. Null otherwise.
         */
        private function renderFile($content=null)
        {
                if(!$this->useView)
                        return $content;

                $viewFile = Yii::getPathOfAlias('application') . '/../' . $this->viewPath;

                if(!file_exists($viewFile))
                        return $content;

                ob_start();
                ob_implicit_flush(false);
                require($viewFile);
                return ob_get_clean();
        }


	/**
	 * Отключает логирование письма при отправке
	 */
	public function disableLog()
	{
		$this->enable_log = false;

		return $this;
	}


	/**
	 * Включет логирование письма при отправке
	 */
	public function enableLog()
	{
		$this->enable_log = true;

		return $this;
	}
}