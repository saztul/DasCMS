<?php
class Event_ContentAccess
	extends _EventContent
{
	public function __construct($sender, Interface_Content $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		$this->informHandlers();
	}
}
?>