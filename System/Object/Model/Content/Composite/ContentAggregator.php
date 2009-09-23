<?php
class Model_Content_Composite_ContentAggregator extends _Model_Content_Composite
{
    private $aggregatorName = null;
    
    public static function getCompositeMethods()
    {
        return array(
        	'getChildContentAggregator', 
        	'setChildContentAggregator',
        );
    }
    
    public function __construct(BContent $compositeFor)
    {
        parent::__construct($compositeFor);
    }

    public function setChildContentFormatter($formatter)
	{
	    QContentFormatter::setFormatter($this->compositeFor->getId(), $formatter);
	} 
	
	public function getChildContentFormatter()
	{
	    if($this->formatterName === null)
	    {
	        $this->formatterName = false;
    	    $res = QContentFormatter::getFormatterName($this->compositeFor->getId());
    	    if($res->getRowCount() == 1)
    	    {
    	        list($this->formatterName) = $res->fetch(); 
    	    }
    	    $res->free();
	    }
	    return $this->formatterName;
	}
	
	public function formatChildContent(BContent $content)
	{
        $f = $this->getChildContentFormatter();
        if($f)    
        {
            return Formatter_Container::unfreezeForFormatting($f, $content);
        }
        else
        {
            throw new XUndefinedException('no formatter');
        }
	}
} 
?>