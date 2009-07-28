<?php
abstract class _Formatter_Attribute extends _Formatter
{
    protected static $title = null;//FIXME to be defined in extending class for display in config
    
    /**
     * @var Formatter_Container
     */
    protected $parentContainer = null;
    
    public function setParentContainer(Formatter_Container $container)
    {
        $this->parentContainer = $container;
    }
    
    /**
     * @return BContent
     */
    protected function getContent()
    {
        if($this->parentContainer == null)
        {
            throw new XUndefinedException('no parent');
        }
        $content = $this->parentContainer->getContent();
        if($content == null)
        {
            throw new XUndefinedException('no content');
        }
        return $content;
    }
    
    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled == true;
    }
    
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
        
    protected function createLink($alias, $htmlInLink)
    {
        try
        {
            $str = '';
            if(!empty($targetView))
            {
                $targetFrame = $this->getTargetFrame();
                $link = $this->getTargetView()->LinkTo($alias);
                $str = sprintf(
                	"<a href=\"%s\"%s>%s</a>\n"
                    ,$link
                    ,empty($targetFrame) ? '' : ' target="'.htmlentities($targetFrame,ENT_QUOTES,CHARSET).'"'
                    ,$htmlInLink
                );
            }
        }
        catch (Exception $e)
        {
            $str =  '';
        }
        return $str;
    }
    
    /**
     * @return string
     */
    abstract protected function getFormatterClass();
    
    /**
     * @return VSpore
     */
    public function getTargetView()
    {
        //FIXME
        $targetView = 'page';
        return VSpore::byName($targetView);
    }
    public function getTargetFrame()
    {
        //FIXME
        return null;
    }
    
    protected function escapeString($string)
    {
        return htmlentities($string, ENT_QUOTES, CHARSET);
    }
    
    /**
     * @return string
     */
    public function toXHTML($insertString = null)
    {
        return sprintf("<div class=\"%s\">\n%s</div>\n\n", $this->getFormatterClass(), $insertString);
    }
    
    public function __toString()
    {
        try
        {
            return $this->toXHTML();
        }
        catch (Exception $e)
        {
            return strval($e);
        }
    }
}
?>