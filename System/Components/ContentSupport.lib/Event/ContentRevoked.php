<?php
class Event_ContentRevoked
	extends _Event
{
    /**
	 * @var Interface_Content
	 */
	protected $Content;

	public function __construct($sender, Interface_Content $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		$this->informHandlers();
	}
}
?>