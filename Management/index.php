<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Management (Login and Applcation loader)
************************************************/

chdir('..');
require_once('./System/Component/Loader.php');
WHeader::httpHeader('Content-Type: text/html; charset=utf-8');

RSession::start();
//you want to go? ok!
if(RURL::has('logout')){
    RSession::destroy(); 
    header('Location: ../?');
    exit;
}

PAuthentication::required();

WTemplate::globalSet('bcms_version', BAMBUS_VERSION);
WTemplate::globalSet('logout_text', SLocalization::get('logout'));
WTemplate::globalSet('WApplications', '');
WTemplate::globalSet('SNotificationCenter', SNotificationCenter::alloc()->init());
WTemplate::globalSet('bambus_my_uri', SLink::link());

/////////////////////////////////////
//load all related js and css files//
/////////////////////////////////////

WHeader::useScript('Management/localization.js.php');
WHeader::loadClientData();
WHeader::setBase(SLink::base());

WHeader::setTitle(BAMBUS_VERSION);
WHeader::meta('license', 'GNU General Public License/GPL 2 or newer');
WTemplate::globalSet('Header', new WHeader());

$SUsersAndGroups = SUsersAndGroups::alloc()->init();


if(PAuthorisation::has('org.bambuscms.login')) //login ok?
{
	if(RSent::has('bambus_cms_username'))
	{
		$SUsersAndGroups->setUserAttribute(PAuthentication::getUserID(), 'last_management_login', time());
		$logins = $SUsersAndGroups->getUserAttribute(PAuthentication::getUserID(), 'management_login_count');
		$count = (empty($logins)) ? 1 : ++$logins;
		$SUsersAndGroups->setUserAttribute(PAuthentication::getUserID(), 'management_login_count', $count);
	}

    $applications = LApplication::getAvailableApplications();
    $Application = LApplication::alloc()->init();
    $Application->selectApplicationFromPool($applications);
    
    WTemplate::globalSet('WApplications',  new WApplications());
    
 	//2nd: load application
    if(LApplication::getName() == '')
	{
		WTemplate::globalSet('TaskBar','');
		$headTpl = new WTemplate('header', WTemplate::SYSTEM);
    	$headTpl->render();
		echo "<div id=\"BambusContentArea\">\n<div id=\"BambusApplication\">\n</div>\n</div>\n";
        $footerTpl = new WTemplate('footer', WTemplate::SYSTEM);
        $footerTpl->render();
	}    
    else
    {
        $Application->initApp();
		//is there an application specific css or js file?
		$appFiles = array('style.css' => 'screen','print.css'=>'print', 'script.js' => 'script');
		foreach($appFiles as $file => $type)
		{
			if(!file_exists(LApplication::getDirectory().$file))
				continue;
			switch($type)
			{
				case 'script':
					WHeader::useScript(LApplication::getDirectory().$file);
					break;
				default: //css
					WHeader::useStylesheet(LApplication::getDirectory().$file, $type);
			}
		}
	    
	    WHeader::setTitle(LApplication::getTitle().' - '.LConfiguration::get('sitename'));
	    
	    //export the config into an array
    	//load application class
    	$controller = $Application->controller();
    	$ob = '';
    	if($controller != false)
    	{
    		ob_start();
    		require($controller);
    		$ob = ob_get_contents();
    		ob_end_clean();
    		$Application->autorun();
    	}
		WTemplate::globalSet('TaskBar',$Application->generateTaskBar());
    	$headTpl = new WTemplate('header', WTemplate::SYSTEM);
        $headTpl->render();
        $Application->initInterface();
		echo $ob;
    	echo WSidePanel::alloc()->init();
		echo "<div id=\"BambusContentArea\">\n<div id=\"BambusApplication\">\n";
		WSidePanel::openAppWrapperBox();
    	$erg = $Application->run();
    	if($erg !== true && (!file_exists($erg) || !include($erg)))
    	{
			//interface is coded in php an needs to be called here
			SNotificationCenter::report('alert', 'invalid_application');
    	}
    	WSidePanel::closeAppWrapperBox();
		echo "</div>\n</div>\n";
    	$footerTpl = new WTemplate('footer', WTemplate::SYSTEM);
        $footerTpl->render();
    }
}else{
    //Show Login
 
    WTemplate::globalSet('TaskBar','');
    LApplication::setAppData(array(
    	'title' => SLocalization::get('login'),
        'icon' => WIcon::pathFor('login')
    ));
	
    LApplication::alloc()->init()->selectApplicationFromPool(array());

	WHeader::useStylesheet('specialPurpose.login.css');
    if(RSent::has('bambus_cms_login'))
    {
        SNotificationCenter::report('warning', 'wrong_username_or_password');
    }
    $headTpl = new WTemplate('header', WTemplate::SYSTEM);
    $headTpl->render();
    echo "<div id=\"BambusContentArea\">\n<div id=\"BambusApplication\">\n";
    $loginTpl = new WTemplate('login', WTemplate::SYSTEM);
    $loginTpl->setEnvironment(array(
        'translate:username' => SLocalization::get('username'),
        'translate:password' => SLocalization::get('password'),
        'translate:login' => SLocalization::get('login')
    ));
    $loginTpl->render();
    echo "</div>\n</div>\n";
    $footerTpl = new WTemplate('footer', WTemplate::SYSTEM);
    $footerTpl->render();
}
?>