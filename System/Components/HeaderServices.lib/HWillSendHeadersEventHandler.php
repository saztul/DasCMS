<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-04
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage EventHandlers
 */
interface HWillSendHeadersEventHandler
{
	public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e);
}
?>