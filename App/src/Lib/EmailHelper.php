<?php

namespace Src\Lib;

use Src\Lib\Application;

class EmailHelper extends Application
{
	protected $swift_message;
	protected $charset;
	protected $date;
	protected $subject;
	protected $body;
	protected $altBody;
	protected $address_from;
	protected $address_sender;
	protected $address_replyTo;
	protected $address_returnPath;
	protected $address_to;
	protected $address_cc;
	protected $address_bcc;
	protected $priority;
	protected $attachments;
	protected $image_attachments;
	protected $error;

	function __construct($app)
	{
		parent::__construct($app);

		$this->reset();
	}

	// Resetea todas las variables (direcciones, variables, contenido, etc...)
	function reset()
	{
		$this->charset = 'utf-8';
		$this->date = time(); // strtotime(date('d-m-Y H:i:s'))
		$this->subject = '';
		$this->address_from = array();
		$this->address_sender = array();
		$this->address_replyTo = array();
		$this->address_returnPath = '';
		$this->address_to = array();
		$this->address_cc = array();
		$this->address_bcc = array();
		$this->priority = '3'; // Normal
		$this->body = '';
		$this->altBody = '';
		$this->attachments = array();
		$this->image_attachments = array();
		$this->error = '';

		$this->swift_message = \Swift_Message::newInstance();
	}

	// Elimina todos los destinatarios del email
	function clearRecipients()
	{
		$this->address_to = array();
		$this->address_cc = array();
		$this->address_bcc = array();

		$this->swift_message = \Swift_Message::newInstance();
	}

	// Elimina todos los ficheros adjuntos (incluyendo imagenes)
	function clearAllAttachments()
	{
		if(! empty($this->attachments))
		{
			$this->attachments = array();

			$this->swift_message = \Swift_Message::newInstance();

			return true;
		}

		return false;
	}

	// Especifica la codificacion del email
	function setCharset($charset)
	{
		$this->charset = $charset;
	}

	// Especifica la fecha del email
	function setDate($date)
	{
		$this->date = $date;
	}

	// Especifica el asunto
	function setSubject($subject)
	{
		$this->subject = $subject;
	}

	// Especifica el cuerpo del mensaje
	function setBody($body)
	{
		$this->body = $body;
	}

	// Renderiza el cuerpo del mensaje utilizando Twig
	function renderBody($template, $data = array())
	{
		$data['image_attachments'] = $this->image_attachments;

		$this->setBody($this->render($template, $data));
	}

	// Especifica el cuerpo alternativo del mensaje
	function setAltBody($altBody)
	{
		$this->altBody = $altBody;
	}

	// Añade un array de direcciones de origen
	function setFrom($addresses)
	{
		$this->address_from = array_merge($this->address_from, $addresses);
	}

	// Añade un array de direcciones de origen (creador original)
	function setSender($addresses)
	{
		$this->address_sender = array_merge($this->address_sender, $addresses);
	}

	// Añade un array de direcciones de origen (responder a)
	function setReplyTo($addresses)
	{
		$this->address_replyTo = array_merge($this->address_replyTo, $addresses);
	}

	// Especifica una direccion de origen (de retorno en caso de error)
	function setReturnPath($address)
	{
		$this->address_returnPath = $address;
	}

	// Añade un array de direcciones de destino
	function setTo($addresses)
	{
		foreach ($addresses as $address)
		{
			$found = false;

			foreach ($this->address_to as $stored_address)
			{
				if($address == $stored_address)
				{
					$found = true;
					break;
				}
			}

			foreach ($this->address_cc as $key => $stored_address)
			{
				if($address == $stored_address)
				{
					unset($this->address_cc[$key]);
					break;
				}
			}

			foreach ($this->address_bcc as $key => $stored_address)
			{
				if($address == $stored_address)
				{
					unset($this->address_bcc[$key]);
					break;
				}
			}

			if(! $found)
			{
				$this->address_to[] = $address;
			}
		}
	}

	// Obtiene el array de direcciones de destino
	function getTo()
	{
		return $this->address_to;
	}

	// Añade un array de direcciones de destino en copia (CC)
	function setCc($addresses)
	{
		foreach ($addresses as $address)
		{
			$found = false;

			foreach ($this->address_to as $stored_address)
			{
				if($address == $stored_address)
				{
					$found = true;
					break;
				}
			}

			foreach ($this->address_cc as $stored_address)
			{
				if($address == $stored_address)
				{
					$found = true;
					break;
				}
			}

			foreach ($this->address_bcc as $key => $stored_address)
			{
				if($address == $stored_address)
				{
					unset($this->address_bcc[$key]);
					break;
				}
			}

			if(! $found)
			{
				$this->address_cc[] = $address;
			}
		}
	}

	// Obtiene el array de direcciones de destino en copia (CC)
	function getCc()
	{
		return $this->address_cc;
	}

	// Añade un array de direcciones de destino en copia oculta (BCC)
	function setBcc($addresses)
	{
		foreach ($addresses as $address)
		{
			$found = false;

			foreach ($this->address_to as $stored_address)
			{
				if($address == $stored_address)
				{
					$found = true;
					break;
				}
			}

			foreach ($this->address_cc as $stored_address)
			{
				if($address == $stored_address)
				{
					$found = true;
					break;
				}
			}

			foreach ($this->address_bcc as $stored_address)
			{
				if($address == $stored_address)
				{
					$found = true;
					break;
				}
			}

			if(! $found)
			{
				$this->address_bcc[] = $address;
			}
		}
	}

	// Obtiene el array de direcciones de destino en copia oculta (BCC)
	function getBcc()
	{
		return $this->address_bcc;
	}

	// Especifica la prioridad de un email
	function setPriority($priority)
	{
		$this->priority = $priority;
	}

	// Añade un fichero adjunto
	function setAttachment($attachment_data, $attachment_name = '')
	{
		$this->attachments[] = \Swift_Attachment::newInstance()
			->setBody($attachment_data)
			->setFilename($attachment_name);

			//->setContentType('text/plain')
			//->setBody(file_get_contents('C:/..../file.dat'))
	}

	// Añade un fichero adjunto desde la ruta especificada
	function setAttachmentFromPath($attachment_path, $attachment_name = '')
	{
		$this->attachments[] = \Swift_Attachment::fromPath($attachment_path)
			->setFilename($attachment_name);
	}

	// Añade una imagen adjunta
	function setImageAttachment($attachment_id, $attachment_data, $attachment_name = '')
	{
		$image = \Swift_Image::newInstance()
			->setBody($attachment_data)
			->setFilename($attachment_name);

		$CID = $this->swift_message->embed($image);

		$this->image_attachments[$attachment_id] = $CID;
	}

	// Añade una imagen adjunta desde la ruta especificada
	function setImageAttachmentFromPath($attachment_id, $attachment_path, $attachment_name = '')
	{
		$image = \Swift_Image::fromPath($attachment_path)
			->setFilename($attachment_name);

		$CID = $this->swift_message->embed($image);

		$this->image_attachments[$attachment_id] = $CID;
	}

	// Devuelve un array con todos los identificadores de imagenes adjuntas para su inserción como CID
	function getImageAttachments()
	{
		return $this->image_attachments;
	}

	// Devuelve un array con los errores producidos en el envío del último email
	function getError()
	{
		return $this->error;
	}

	// Envía el email (se intentará enviar 5 veces si hubiera problemas en intervalos de 5 segundos)
	function send()
	{
		$this->error = array();

		$this->swift_message
			->setSubject($this->subject)
			->setFrom($this->address_from)
			->setSender($this->address_sender)
			->setReplyTo($this->address_replyTo)
			->setReturnPath($this->address_returnPath)
			->setDate($this->date)
			->setMaxLineLength(1000) // Nunca mayor de 1000 líneas! (RFC 2822)
			->setPriority($this->priority) // Highest (1), High (2), Normal (3), Low (4), Lowest (5)
			->setContentType('text/html')
			->setCharset($this->charset)
			->addPart($this->altBody, 'text/plain')
			->setBody($this->body, 'text/html');

		if(! $this->isDebug())
		{
			$this->swift_message
				->setTo($this->address_to)
				->setCc($this->address_cc)
				->setBcc($this->address_bcc);
		}
		else
		{
			$this->swift_message
				->setTo(array($this->app('email.debug')))
				->setBody($this->body . '<br><br><pre>'.
					print_r($this->address_to, true) .
					print_r($this->address_cc, true) .
					print_r($this->address_bcc, true)
					. '</pre>', 'text/html');
		}

		foreach ($this->attachments as $attachment)
		{
			$this->swift_message->attach($attachment);
		}

		try
		{
			if (!$this->app('mailer')->send($this->swift_message, $failures))
			{
				$this->error = $failures;

				return false;
			}

			return true;
		}
		catch(\Exception $e)
		{
			$this->error = array($e->getMessage());
		}

		return false;
	}
}
