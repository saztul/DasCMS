<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.10.2008
 * @license GNU General Public License 3
 */
class CFeed extends BContent implements ISupportsSidebar, IGlobalUniqueId, IGeneratesFeed 
{
    const GUID = 'org.bambuscms.content.cfeed';
    
    public function getGUID()
    {
        return self::GUID;
    }
    
    const HEADER = 0;
    const ITEM = 1;
    const FOOTER = 2;
    const SETTINGS = 3;
    const HEADER_AND_FOOTER = 0;
    
    const PREFIX = 0;
    const SUFFIX = 1;
    
    const OPTIONS = 2;
    const ORDER = 1;
    const CAPTIONS = 0; 
    
    private $_contentLoaded = false;
    
    private $_data = array(
        self::CAPTIONS => array(
            self::HEADER => array(
                'NumberOfEnd' => array('',''),
                'NumberOfStart' => array('',''),
                'FoundItems' => array('Found: ',' Items'),
                'Link' => array('prev','next'),
                'Pagina' => array('Page: ','')
            ),
            self::ITEM => array(
                'Link' => array('',''),
                'NoItemsFound' => array('','')
            ),
            self::FOOTER => array(
                'NumberOfEnd' => array('',''),
                'NumberOfStart' => array('',''),
                'FoundItems' => array('Found: ',' Items'),
                'Link' => array('prev','next'),
                'Pagina' => array('Page: ','')
            )
        ),
        self::ORDER => array(
            self::HEADER => array(
                'PrevLink' => 1,
                'NextLink' => 3,
                'Pagina' => 2,
                'NumberOfStart' => null,
                'NumberOfEnd' => null,
                'FoundItems' => null
            ),
            self::ITEM => array(
                'Desciption' => 2,
                'Content' => null,
                'Link' => null,
                'Author' => 3,
                'Tags' => null,
                'PubDate' => 4,
                'ModDate' => null,
                'Title' => 1
            ),
            self::FOOTER => array(
                'PrevLink' => 1,
                'NextLink' => 2,
                'Pagina' => null,
                'NumberOfStart' => null,
                'NumberOfEnd' => null,
                'FoundItems' => 3
            )
        ),
        self::OPTIONS => array(
            self::HEADER => array(
                'PaginaType' => true
			),
            self::FOOTER => array(
                'PaginaType' => true
			),
			self::ITEM => array(
                'ModDateFormat' => 'c',
                'PubDateFormat' => 'c',
                'LinkTitle' => true,
                'LinkTags' => false
            ),
            self::SETTINGS => array(
                'ItemsPerPage' => 1,
                'MaxPages' => 1000,
                'Filter' => array(),
                'FilterMethod' => 'All',
                'TargetView' => '',
                'SortOrder' => true,
                'SortBy' => 'title'
                
            )
        )
    );
    
    private function _getConfVal($type, $target, $key)
    {
        if(!isset($this->_data[$type]) || !isset($this->_data[$type][$target]) || !isset($this->_data[$type][$target][$key]))
        {
            throw new XArgumentException(sprintf('key /%s/%s/%s not found', $type, $target, $key));
        }
        return $this->_data[$type][$target][$key];
    }
    
    //captions
    public function changeCaption($forType, $andKey, $toPrefix, $andSuffix)
    {
        if(!isset($this->_data[self::CAPTIONS][$forType]) || !isset($this->_data[self::CAPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /captions/%s/%s not found', $forType, $andKey));
        }
        $this->_data[self::CAPTIONS][$forType][$andKey][self::PREFIX] = $toPrefix;
        $this->_data[self::CAPTIONS][$forType][$andKey][self::SUFFIX] = $andSuffix;
    }
    
    public function caption($forType, $andKey)
    {
        if(!isset($this->_data[self::CAPTIONS][$forType]) || !isset($this->_data[self::CAPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /captions/%s/%s not found', $forType, $andKey));
        }
        return $this->_data[self::CAPTIONS][$forType][$andKey]; 
    }
    
    //order 1..n - unused are null
	public function changeOrder($forType, array $toData)
	{
	    //right key
        if(!isset($this->_data[self::ORDER][$forType]))
        {
            throw new XArgumentException(sprintf('key /order/%s not found', $forType));
        }	    
        //set to whatever data is given
        foreach ($this->_data[self::ORDER][$forType] as $key => $pos) 
	    {
	    	$this->_data[self::ORDER][$forType][$key] = (isset($toData[$key]))
	    	     ? $toData[$key]
	    	     : null;
	    }
	    //order by given data
	    asort($this->_data[self::ORDER][$forType]);
	    //change data to correct numbers (1..n or null) 
	    $i = 0;
	    foreach ($this->_data[self::ORDER][$forType] as $key => $data) 
	    {
	    	$this->_data[self::ORDER][$forType] = ($key === null)
	    	    ? null
	    	    : ++$i;
	    }
	}
	
	public function order($forType)
	{
	    //right key
        if(!isset($this->_data[self::ORDER][$forType]))
        {
            throw new XArgumentException(sprintf('key /order/%s not found', $forType));
        }
        return $this->_data[self::ORDER][$forType];
	}

    //options
	public function changeOption($forType, $andKey, $toValue)
	{
        if(!isset($this->_data[self::OPTIONS][$forType]) || !isset($this->_data[self::OPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /options/%s/%s not found', $forType, $andKey));
        }
        return $this->_data[self::OPTIONS][$forType][$andKey];
	}
    
	public function option($forType, $andKey)
	{
	    if(!isset($this->_data[self::OPTIONS][$forType]) || !isset($this->_data[self::OPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /options/%s/%s not found', $forType, $andKey));
        }
        return $this->_data[self::OPTIONS][$forType][$andKey]; 
	}
	
	public function options($forType)
	{
	    if(!isset($this->_data[self::OPTIONS][$forType]))
        {
            throw new XArgumentException(sprintf('key /options/%s/%s not found', $forType, $andKey));
        }
        return $this->_data[self::OPTIONS][$forType]; 
	}
	//FIXME remove redundant redundancy
	
    
	/**
	 * @return CFeed
	 */
	public static function Create($title)
	{
	    $SCI = SContentIndex::alloc()->init();
	    list($dbid, $alias) = $SCI->createContent('CFeed', $title);
	    DFileSystem::Save(SPath::TEMPLATES.$dbid.'.php', ' ');
	    //FIXME compile new tpl
	    $tpl = new CFeed($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->deleteContent($alias, 'CFeed');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CFeed');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CFeed', false);;
	}
		
	public static function Open($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    if($SCI->exists($alias, 'CFeed'))
	    {
	        return new CFeed($alias);
	    }
	    else
	    {
	        throw new XUndefinedIndexException($alias);
	    }
	}
	
	
	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($alias)
	{
	    if(!self::Exists($alias))
	    {
	        throw new XArgumentException('content not found');
	    }
	    $this->initBasicMetaFromDB($alias);
	}

	private function link($arg, $target = false)
	{
	    $iqo = $this->invokingQueryObject;
	    if(!$target && $iqo != null && $iqo instanceof QSpore)
	    {
	        //link to self
	        //only param page=
            $iqo->SetLinkParameter('page', $arg, true);
            $iqo->LinkTo($this->getAlias());
            return strval($iqo);
        }   
        elseif($target && QSpore::isActive($target))
        {
            //link page to target view
            $linker = new QSpore($target);
            $linker->LinkTo($arg);
            return strval($linker);
        } 
        else
        {
            return '#';        
        }
	}
	
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getContent()
	{
	    //fetch meta data
	    $res = QCFeed::countItemsForFeed($this->getId());
	    list($count) = $res->fetch();
        $res->free();
        
        $hasMorePages = true;
        
        //max items per page
        $itemsPerPage = $this->option(self::SETTINGS, 'ItemsPerPage');
        
        //available pages
        $pages = max(1,ceil($count/max(1,$itemsPerPage)));
        
        //current page        
        $currentPage = 1;
        $iqo = $this->invokingQueryObject;
        if($iqo != null && $iqo instanceof QSpore)
        {
            $currentPage = intval($iqo->GetParameter('page'));
        }
        $currentPage = max(1, $currentPage);
        //last page
        $maxPages = $this->option(self::SETTINGS, 'MaxPages') 
            ? min($this->option(self::SETTINGS, 'MaxPages'), $pages) 
            : $pages;
        
        //displayable items    
        $maxItems = min($maxPages * $itemsPerPage, $count);
        
        //page to display
        if($currentPage >= $maxPages)
        {
            $currentPage = min($currentPage, $maxPages);
            $hasMorePages = false;
        }
        
        //item count on page
        $startItem = ($currentPage-1)*$itemsPerPage+1;
        $endItem = min($startItem+$itemsPerPage-1, $maxItems);

        //which items to fetch?
        $fetch = array();
        foreach ($this->order(self::ITEM) as $prop => $rank) 
        {
        	if($rank != null)
        	{
        	    $fetch[] = $prop;
        	}
        }
        
        $res = QCFeed::getItemsForPage(
            $this->getId(), 
            $this->option(self::SETTINGS, 'SortBy'),
            $this->option(self::SETTINGS, 'SortOrder'),
            $currentPage,
            $itemsPerPage,
            $fetch
            );
        
        //html building
        $content = '<div id="CFeed_'.$this->getAlias().'" class="CFeed">';
        if($count > 0)
        {
            $content .= $this->buildControlHtml(self::HEADER, $hasMorePages, $currentPage, $startItem, $endItem, $maxItems);
            while($row = $res->fetch())
            {
                $content .= $this->buildItemHtml($row);
            }
            $res->free();
            $content .= $this->buildControlHtml(self::FOOTER, $hasMorePages, $currentPage, $startItem, $endItem, $maxItems);
        }
        else
        {
            $content .= '<p>'.implode('<br />', $this->caption(self::ITEM, 'NoItemsFound')).'</p>';
        }
        $content .= '</div>';
        return $content;
	}
	
	private function buildControlHtml($type, $hasMorePages, $Pagina, $NumberOfStart, $NumberOfEnd, $FoundItems)
	{
        $html = sprintf("\n\t<div class=\"CFeed_control CFeed_control_%s\">", ($type == self::HEADER) ? 'header' : 'footer');
        $tpl = "\n\t\t<%s class=\"CFeed_control_%s\">%s</%s>";
        
        foreach ($this->order($type) as $key => $pos) 
        {
            $set = false;
        	if(!$pos)
        	{
        	    continue;
        	}
        	$class = strtolower($key);
        	$tag = 'div';
        	switch ($key) 
        	{
        	    case 'NextLink' :
        	        if($hasMorePages)
        	        {
        	            $set = true;
                        $captions = $this->caption($type, 'Link');
                        $caption = $captions[self::SUFFIX];
                        $page = $Pagina + 1;
                        $content = sprintf('<a href="%s">%s</a>', $this->link($page), $caption);
        	        }
                    break;
    	        case 'PrevLink' :
        	        if($Pagina > 1)
        	        {
        	            $set = true;
                        $captions = $this->caption($type, 'Link');
                        $caption = $captions[self::PREFIX];
                        $page = $Pagina - 1;
                        $content = sprintf('<a href="%s">%s</a>', $this->link($page), $caption);
        	        }
                    break;
                case 'Pagina':
                case 'NumberOfStart' :
                case 'NumberOfEnd' :
                case 'FoundItems' :
                    $set = true;
                    $captions = $this->caption($type, $key);
        		    $content = $captions[self::PREFIX];
        		    $content .= ${$key};
        		    $content .= $captions[self::SUFFIX];
        		    break;
                default: continue;
        	}
        	if($set)
        	{
        	    $html .= sprintf($tpl, $tag, $class, $content, $tag);
        	}
        }
        $html .= "\n\t</div>";
        return $html;
	}
		
	private function buildItemHtml(array $data)
	{
        $map = array(
            'Title' => 0,
            'Desciption' => 1,
            'PubDate' => 2,
            'Alias' => 3,
            'Author' => 4,
            'ModDate' => 5,
            'Tags' => 6
        );
        $html = "\n\t<div class=\"CFeed_item\">";
        //add all active attributes in order
        $tpl = "\n\t\t<%s class=\"CFeed_item_%s\">%s</%s>";
        foreach ($this->order(self::ITEM) as $key => $pos) 
        {
        	if(!$pos)
        	{
        	    continue;
        	}
        	$class = strtolower($key);
        	$tag = 'span';
        	switch ($key) 
        	{
                case 'Tags':
        	    case 'Desciption':
        		    $tag = 'div';
        		    $content = htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                case 'Content':
                    $tag = 'div';
                    $content = 'not implemented';
                    break;
                case 'Link':
        		    $content = sprintf('<a href="#%s">%s</a>', $data[$map['Alias']], implode($this->caption(self::ITEM,'Link')));
        		    break;
                case 'Author':
                case 'PubDate':
                case 'ModDate':
        		    $content = htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                case 'Title':
        		    $tag = 'h2';
        		    $content = ($this->option(self::ITEM, 'LinkTitle')) 
        		        ? sprintf('<a href="#%s">%s</a>', $data[$map['Alias']], htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8'))
        		        : htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                default: continue;
        	}
        	$html .= sprintf($tpl, $tag, $class, $content, $tag);
        }
        $html .= "</div>\n";
        return $html;
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('feeds are generated');
	}
	
	public function generateFeed()
	{
	    //build feed header
	    //build feed items
	    $xml = new SSimpleXMLWriter();
	    $xml->openTag('data');
	    $res = QCFeed::getItemsForFeed($this->getId());
	    while($row = $res->fetch())
	    {
	        $xml->openTag('item');
	        $xml->tag('title', array(), $row[0]);
	        $xml->tag('desc', array(), $row[1]);
	        $xml->tag('pubdate', array(), date('r', strtotime($row[2])));
	        $xml->tag('link', array(), $row[3]);
	        $xml->tag('lastmodified', array(), $row[4]);
	        $xml->tag('categories', array(), $row[5]);
	        $xml->closeTag();
	    }
	    $xml->closeTag();
	    echo strval($xml);
	}
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			//FIXME DFileSystem::Save(SPath::TEMPLATES.$this->Id.'.php',$this->RAWContent);
		}
		$this->saveMetaToDB();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'media', 'settings', 'information', 'search'));
	}
}
?>