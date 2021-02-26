<?php

if (!function_exists('editor'))
{
	/**
	 * Render an editor
	 * 
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array   $atts
	 * @return  Response
	 */
	function editor($name, $value, $atts = array())
	{
		event($event = new App\Modules\Core\Events\EditorIsRendering($name, $value, $atts));

		return $event->render();
		//return view('core::components.textarea', compact('name', 'value', 'atts'));
	}
}

if (!function_exists('captcha'))
{
	/**
	 * Render a CAPTCHA
	 * 
	 * @param   string  $name
	 * @param   array   $atts
	 * @return  Response
	 */
	function captcha($name, $atts = array())
	{
		event($event = new App\Modules\Core\Events\CaptchaIsRendering($name, $atts));

		return $event->render();
	}
}
