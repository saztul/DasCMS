<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CError extends _Content implements IGlobalUniqueId, ISearchDirectives
{
    const GUID = 'org.bambuscms.content.cerror';
    const CLASS_NAME = 'CError';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
	public static function Create($title)
	{
	    throw new Exception('errors are fixed');
	}
	
	public static function errdesc($code)
	{
		$code = SHTTPStatus::validate($code);
		$code = $code == null ? 501 : $code; 
		return array($code => SHTTPStatus::byCode($code, false));
	}

	/**
	 * initialite property in $var with $dataArray[$var] if it exists or use a default value
	 * only works if the property is null
	 *
	 * @param string $var
	 * @param array $dataArray
	 * @param mixed $defaultValue
	 */
	protected function initPropertyValues($var,array &$dataArray, $defaultValue)
	{
		if($this->{$var} == null)
		{
			$this->{$var} = (array_key_exists($var, $dataArray))
				? $dataArray[$var]
				: $defaultValue;
		}
	}
	
	public function __construct($Id)	
	{
	    $Id = SHTTPStatus::validate($Id);
	    if($Id == 401)
	    {
	        $tpl = Core::Settings()->get('login_template');
	        if(defined('BAMBUS_HTML_ACCESS') && !empty($tpl))
	        {
	            try 
	            {
	                //returns login form and ends function
	                return Controller_Content::getInstance()->openContent($tpl);
	            }
	            catch (Exception $e)
	            {
	            	/* not returned the login tpl, send header auth instead */
	            }
	        }
            header("HTTP/1.1 401 Authorization Required");
            header("WWW-Authenticate: Basic realm=\"BambusCMS\"");
	    }
		$dat = self::errdesc($Id);
		$Ids = array_keys($dat);
		$this->Id = $Ids[0];
		$meta = array();
		$defaults = array(
			'CreateDate' => time(),
			'CreatedBy' => 'System',
			'ModifyDate' => time(),
			'ModifiedBy' => 'System',
			'PubDate' => time(),
			'RevokeDate' => 0,
			'IsPublished' => true,
			'Size' => 0,
			'Tags' => array(),
			'Title' => 'ERROR '.$this->Id.' - '.$dat[$this->Id],
			'Content' => sprintf('<div class="%s"><b>ERROR %d - %s</b></div>',get_class($this),$this->Id,$dat[$this->Id]),
			'Alias' => sprintf('-Error-%d-', $this->Id)
		);
		foreach ($defaults as $var => $default) 
		{
			$this->initPropertyValues($var, $meta, $default);
		}
	}

	protected function saveContentData() {}
	
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return false;
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array();
	}
    public function isSearchIndexingEditable()
    {
        return false;
    }
    public function changeSearchIndexingStatus($allow)
    {}
	
}
?>