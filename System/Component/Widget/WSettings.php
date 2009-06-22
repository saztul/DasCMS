<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WSettings extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::PROPERTY_EDIT)
	    );
	}
	
	public function getName()
	{
	    return 'content_properties';
	}
	
	public function getIcon()
	{
	    return new WIcon('configure','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
		if(RSent::has('WSearch-Tags'))
		{
			$tagstr = RSent::get('WSearch-Tags', CHARSET);
			$chk = RSent::get('WSearch-Tags-chk', CHARSET);
			if($chk != md5($tagstr)) 
			{
				$this->targetObject->Tags = $tagstr;
			}
		}
		if(RSent::has('WSearch-PubDate'))
		{
			$dat = RSent::get('WSearch-PubDate');
			$chk = $this->targetObject->PubDate;
			if($chk != $dat) 
			{
				$this->targetObject->PubDate = $dat;
			}
		}
		$desc = RSent::get('WSearch-Desc', CHARSET);
		if(RSent::has('WSearch-Desc') && $desc != $this->targetObject->Description)
		{
			$this->targetObject->Description = $desc;
		}
		if(RSent::has('WSearch-PreviewImage-Alias'))
		{
		    $prevAlias = RSent::get('WSearch-PreviewImage-Alias', CHARSET);
		    $this->targetObject->PreviewImage = $prevAlias;
		}
		if(RSent::has('WSearch-sent'))
		{
		    try{
		    $allowSearchIndexing = RSent::hasValue('WSearch-AllowSearchIndexing');
		    if($this->targetObject instanceof ISearchDirectives && $this->targetObject->isSearchIndexingEditable())
		    {
		        $this->targetObject->changeSearchIndexingStatus($allowSearchIndexing);
		    }
		    }catch (Exception $e)
		    {
		        echo $e->getMessage();
		        echo $e->getTraceAsString();
		    }
		}
	}
	
	public function __toString()
	{
		$html = '<div id="WSearch">';
		try{
		//init values
		$Items = new WNamedList();
		$Items->setTitleTranslation(false);
	    $tags = $this->targetObject->Tags;
		$tagstr = (is_array($tags)) ? implode(', ', $tags) : '';
		$prev = $this->targetObject->PreviewImage;
		$alias = $prev->getAlias();
		$pubDate = $this->targetObject->PubDate;
		
		//preview
		if($alias !== null)
		{
		    $html .= sprintf('<input type="hidden" name="WSearch-PreviewImage-Alias" id="WSearch-PreviewImage-Alias" value="%s" />', htmlentities($alias, ENT_QUOTES, CHARSET));
		}
		//tags changed?		
		$html .= sprintf('<input type="hidden" class="hidden" name="WSearch-Tags-chk" value="%s" />', md5($tagstr));
		$html .= '<input type="hidden" class="hidden" name="WSearch-sent" value="1" />';
		
		
		$Items->add(
		    sprintf("<label>%s</label>", SLocalization::get('preview_image')),
		    sprintf('<div id="WSearch-PreviewImage"%s>%s</div>'
		        , ($alias === null) ? ' class="WSearch-PreviewImage-readonly"' : ''    
		        ,$prev->scaled(128,96,WImage::MODE_SCALE_TO_MAX)
		    )
		);
		$Items->add(
		    sprintf("<label for=\"WSearch-PubDate\">%s</label>", SLocalization::get('pubDate')),
		    sprintf(//onfocus="this.select();"
		    	'<input type="text" id="WSearch-PubDate" name="WSearch-PubDate" value="%s" />'
		        , (is_numeric($pubDate) && !empty($pubDate))? date('Y-m-d H:i:s', $this->targetObject->PubDate) : ''
	        )
		);
		$Items->add(   
		    sprintf("<label for=\"WSearch-Tags\">%s</label>", SLocalization::get('tags')),
		    sprintf('<textarea id="WSearch-Tags" name="WSearch-Tags">%s</textarea>', htmlentities($tagstr, ENT_QUOTES, CHARSET))
	    );
		
		$Items->add(   
		    sprintf("<label for=\"WSearch-Desc\">%s</label>", SLocalization::get('description')),
		    sprintf('<textarea id="WSearch-Desc" name="WSearch-Desc">%s</textarea>', htmlentities($this->targetObject->Description, ENT_QUOTES, CHARSET))
	    );
	    $si_on = true;
	    $si_changeable = false;
	    if($this->targetObject instanceof ISearchDirectives)
	    {
	        $si_on = $this->targetObject->allowSearchIndex();
	        $si_changeable = $this->targetObject->isSearchIndexingEditable();
	    }
	    $Items->add(
		    sprintf("<label for=\"WSearch-AllowSearchIndexing\">%s</label>", SLocalization::get('include_in_search_index')),
		    sprintf(
		    	'<input type="checkbox" id="WSearch-AllowSearchIndexing" name="WSearch-AllowSearchIndexing"%s%s />'
		        , $si_on ? ' checked="checked"' : ''
		        , $si_changeable ? '' : ' disabled="disabled"'
		        , SLocalization::get('include_in_search_index')
	        )
		);
		
//	    $MetaItems = new WNamedList();
//	    $MetaItems->setTitleTranslation(true);
//		$meta = array('Alias' => 'alias','GUID' => 'id', 'PubDate' => 'pubDate','ModifyDate' => 'modified','ModifiedBy' => 'modified_by', 'CreateDate' => 'created', 'CreatedBy' => 'created_by', 'Size' => 'size');
//		foreach ($meta as $key => $name) 
//		{
//		    $val = '-';
//		    if(isset($this->targetObject->{$key}) && strlen($this->targetObject->{$key}) > 0) 
//		    {
//		        if(substr($key,-4) == 'Date') 
//		        {
//		            $date = $this->targetObject->{$key};
//		            $val = $date > 0 ? date('r',$this->targetObject->{$key}) : '';
//		        }
//		        elseif(substr($key,-4) == 'Size')
//		        {
//		            $val = DFileSystem::formatSize($this->targetObject->{$key});
//		        }
//		        else
//		        {
//		            $val = htmlentities($this->targetObject->{$key}, ENT_QUOTES, CHARSET);
//		        }
//		    }
//		    $MetaItems->add($name, $val);
//		}
//		$Items->add(   
//		    sprintf("<label>%s</label>", SLocalization::get('meta_data')),
//		    $MetaItems
//	    );
		$html .= $Items;
		$html .= '</div>';
		}catch (Exception $e)
		{
		    return $e->getTraceAsString();
		}
		return $html;
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>