<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UGoogleServices 
    extends BPlugin 
    implements 
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $e->addClassSettings($this, 'google_services', array(
        	'verify_v1' => array(LConfiguration::get('google_verify_header'), AConfiguration::TYPE_TEXT, null, 'google_verify_header'),
        	'google_maps_key' => array(LConfiguration::get('google_maps_key'), AConfiguration::TYPE_TEXT, null, 'google_maps_key'),
        	'load_maps_support' => array(LConfiguration::get('google_load_maps_support'), AConfiguration::TYPE_CHECKBOX, null, 'google_load_maps_support')
        ));
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        
        $data = $e->getClassSettings($this);
            if(isset($data['verify_v1']))
        {
            LConfiguration::set('google_verify_header', $data['verify_v1']);
        }
        if(isset($data['load_maps_support']))
        {
            LConfiguration::set('google_load_maps_support', $data['load_maps_support']);
        }
        if(isset($data['google_maps_key']))
        {
            LConfiguration::set('google_maps_key', $data['google_maps_key']);
        }
    }
    
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        $confMeta = array(
            'google_verify_header' => 'verify-v1',
        );
        foreach($confMeta as $key => $metaKey)
        {
            $val = LConfiguration::get($key);
            if(!empty($val))
            {
                $e->getHeader()->addMeta($val, $metaKey);
            }
        }
        if(LConfiguration::get('google_maps_key') != '') 
        {
            $e->getHeader()->addScript('text/javascript', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.LConfiguration::get('google_maps_key'));
        }
        if(LConfiguration::get('google_load_maps_support') != '')
        {
            $e->getHeader()->addScript('text/javascript', 'System/WebsiteSupport/JavaScript/GoogleMapsSupport.js');
        }
    }
}
?>