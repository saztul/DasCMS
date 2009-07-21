<?php
class iCalendar_Entry_Event extends _iCalendar_Entry implements Interface_Calendar_Event
{
    protected $startTime, $endTime;
    /**
     * @var BContent
     */
    protected $content;
    
    public function __construct($startTime, $endTime, BContent $content)
    {
        $this->sectionName = 'VEVENT';
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->content = $content;
    }
    
    protected function getSectionBody()
    {
        $str = $this->makeDateCommand('DTSTART' ,$this->startTime,false);
        $str.= $this->makeDateCommand('DTEND',  $this->endTime,false);
        $str.= $this->makeDateCommand('CREATED',$this->content->getCreateDate(), false);
        $str.= $this->makeCommand('SUMMARY',$this->content->getTitle(), true);
        $str.= $this->makeCommand('UID',$this->content->getGUID());
        $str.= $this->makeCommand('DESCRIPTION',strip_tags($this->content->getDescription()), true);
        return $str;
    }
}
?>