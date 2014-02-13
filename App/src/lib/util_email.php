<?php

use Silex\Application;

class EmailHelperUtil
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	
	// TODO: Enviar emails (y guardarlos en BD)
	
	public function test()
	{
		echo "Testing class 'EmailHelperUtil'";

		$email_from = 'user@example.com';
		$email_to = 'user@example.com';

		$email_msg = \Swift_Message::newInstance();
		$email_msg->setSubject("Asunto")
			->setFrom($email_from)
			->setSender($email_from)
			->setReplyTo($email_from)
			->setReturnPath($email_from)
			->setTo($email_to)
			->setMaxLineLength(1000) // Nunca mayor de 1000 líneas! (RFC 2822)
			->setPriority(2) // Highest (1), High (2), Normal (3), Low (4), Lowest (5)
			->setContentType('text/html')
			->setCharset('utf-8')
			->setBody("<!DOCTYPE html><html><body><pre>TEST<br /></pre></body></html>", 'text/html');
		
		$app['mailer']->send($email_msg);
	}
}
