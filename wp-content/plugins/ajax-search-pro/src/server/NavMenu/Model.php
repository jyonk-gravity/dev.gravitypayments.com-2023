<?php

namespace WPDRMS\ASP\NavMenu;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Nav menu data model
 *
 * @phpstan-type MenuData array{
 *     title: string,
 *     url: string,
 *     search_id: integer,
 *     prevent_events: int<0,1>
 * }
 */
class Model {

	/**
	 * @var string
	 */
	public string $title = '';

	/**
	 * @var string
	 */
	public string $url = '';

	/**
	 * @var int
	 */
	public int $search_id = 0;

	/**
	 * @var int<0,1>
	 */
	public int $prevent_events = 0;

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data ) {
		$this->title          = strval( $data['title'] ?? $this->title );
		$this->url            = strval( $data['url'] ?? $this->url );
		$this->search_id      = intval( $data['search_id'] ?? $this->search_id );
		$this->prevent_events = intval( $data['prevent_events'] ?? $this->prevent_events ) === 0 ? 0 : 1;
	}

	/**
	 * Array of model values (for storage)
	 *
	 * @return MenuData
	 */
	public function value(): array {
		return array(
			'title'          => $this->title,
			'url'            => $this->url,
			'search_id'      => $this->search_id,
			'prevent_events' => $this->prevent_events,
		);
	}
}
