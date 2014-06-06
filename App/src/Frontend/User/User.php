<?php

namespace Src\Frontend\User;

use Src\Lib\ControllerBase;

class User extends ControllerBase
{
	function register()
	{
		if($this->auth()->isAuthenticated())
		{
			return $this->redirect($this->generateUrl('rt_root'));
		}

		return $this->renderResponse('user/register.twig');
	}

	function active($token)
	{
		$data = $this->subRequestRouteJson('rta_usr_active', array('token' => $token));

		if(isset($data['error']))
		{
			return $this->renderResponse('error.twig', array('mensaje' => $data['error']));
		}

		return $this->renderResponse('info.twig', array('mensaje' => 'Usuario activado con éxito. Ya puedes iniciar sesión.'));
	}

	function login()
	{
		if($this->auth()->isAuthenticated())
		{
			return $this->redirect($this->generateUrl('rt_main'));
		}

		return $this->renderResponse('user/login.twig', array('redirect_path' => $this->requestData('POST', 'redirect_path')));
	}

	function password_change()
	{
		if(! $this->auth()->isAuthenticated())
		{
			return $this->redirect($this->generateUrl('rt_root'));
		}

		return $this->renderResponse('user/password_change.twig');
	}

	function password_change_token($token)
	{
		$data = $this->subRequestRouteJson('rta_usr_password_change_token_check', array('token' => $token));

		if(isset($data['error']))
		{
			return $this->renderResponse('error.twig', array('mensaje' => $data['error']));
		}

		return $this->renderResponse('user/password_change_token.twig', array('password_change_token' => $token));
	}

	function password_forgot()
	{
		if($this->auth()->isAuthenticated())
		{
			return $this->redirect($this->generateUrl('rt_root'));
		}

		return $this->renderResponse('user/password_forgot.twig');
	}

	function logout()
	{
		if(! $this->auth()->isAuthenticated())
		{
			return $this->redirect($this->generateUrl('rt_root'));
		}

		$this->subRequestRoute('rta_usr_logout');

		return $this->renderResponse('info.twig', array('mensaje' => 'Sesión cerrada'));
	}
}
