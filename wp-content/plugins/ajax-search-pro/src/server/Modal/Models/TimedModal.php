<?php

namespace WPDRMS\ASP\Modal\Models;

/**
 * @phpstan-type ModalButtons Array<string, array{
 *      text: string,
 *      href: string,
 *      type?: 'okay'|'cancel'|string,
 *      target?: '_blank',
 *      dismmisses_forever?: boolean
 * }>
 */
class TimedModal {
	public string $type;
	public string $name;
	public string $displayed_cookie_name;
	public string $clicked_okay_cookie_name;
	public string $heading;
	public string $content;
	public bool $show_close_icon;
	public bool $close_on_background_click;

	/**
	 * @var ModalButtons
	 */
	public ?array $buttons;

	/**
	 * @param string       $type
	 * @param string       $name
	 * @param string       $displayed_cookie_name
	 * @param string       $clicked_okay_cookie_name
	 * @param string       $heading
	 * @param string       $content
	 * @param bool         $show_close_icon
	 * @param bool         $close_on_background_click
	 * @param ModalButtons $buttons
	 */
	public function __construct(
		string $type,
		string $name,
		string $displayed_cookie_name,
		string $clicked_okay_cookie_name,
		string $heading,
		string $content,
		bool $show_close_icon = true,
		bool $close_on_background_click = true,
		?array $buttons = null
	) {
		$this->type                      = $type;
		$this->name                      = $name;
		$this->displayed_cookie_name     = $displayed_cookie_name;
		$this->show_close_icon           = $show_close_icon;
		$this->clicked_okay_cookie_name  = $clicked_okay_cookie_name;
		$this->heading                   = $heading;
		$this->content                   = $content;
		$this->close_on_background_click = $close_on_background_click;
		$this->buttons                   = $buttons;
	}
}
