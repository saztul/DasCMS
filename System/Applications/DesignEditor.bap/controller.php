<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$allowEdit = true;
$Files = DFileSystem::FilesOf(SPath::DESIGN, '/\.css/i');
$FileOpened = false;
//////////
//upload//
//////////
$allowed = array('css', 'gpl', 'jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf');
$succesfullUpload = false;
$uploadIsImage = false;
$File = null;
if(isset($_FILES['bambus_image_file']['name']) && PAuthorisation::has('org.bambus-cms.layout.image.create'))
{ 
    // we have got an upload
    if(file_exists(SPath::DESIGN.basename($_FILES['bambus_image_file']['name'])) 
      && RSent::hasValue('bambus_overwrite_image_file'))
    {
        SNotificationCenter::report('warning', 'upload_failed_because_file_already_exists');
    }
    else
    {
        //file does not exist or we are allowed to overwrite it
        //the suffixes of nice image types are:
        
        //everything else we treat as ordinary file
        $tmp = explode('.', utf8_decode($_FILES['bambus_image_file']['name']));
        $suffix = strtolower(array_pop($tmp));
        $tmp = null;
        if(in_array($suffix, $allowed))
        {
            //we like him
            if(move_uploaded_file($_FILES['bambus_image_file']['tmp_name'], SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name']))))
            {
                //i like to move it move it
                SNotificationCenter::report('message', 'file_uploaded');
                chmod(SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_image_file']['name']));
                //create thumbnail image
                $image = SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name']));
                $uploadIsImage = ($suffix != 'css' && $suffix != 'gpl');
            }
            else
            {
                SNotificationCenter::report('warning', 'uploded_failed');
            }
        }
        else
        {
            //run away and scream
            SNotificationCenter::report('warning', 'upload_failed_because_of_unsupported_file_type');
        }
        
    }
}

if(BAMBUS_APPLICATION_TAB == 'edit_css')
{
	//////////
	//create//
	//////////
		
	if(PAuthorisation::has('org.bambus-cms.layout.stylesheet.create') && RURL::get('_action') == 'create')
	{
		$i = 0;
		while(file_exists(SPath::DESIGN.SLocalization::get('new').'-'.++$i.'.css'))
			;
		$File = SLocalization::get('new').'-'.$i.'.css';
		$fileContent = '/* '.SLocalization::get('new_css_file').' */';
		DFileSystem::Save(SPath::DESIGN.$File, $fileContent);
		$FileName = SLocalization::get('new').'-'.$i;
		$allowEdit = false;
		$FileOpened = true;
	}
	
	if(count($Files) > 0)
	{
		if($allowEdit && RURL::hasValue('edit') && in_array(RURL::get('edit'), $Files))
		{
			$File = RURL::get('edit');
			$FileName = ($File == 'default.css') ? SLocalization::get('default.css') : htmlentities(substr($File, 0, -4));
			$allowEdit = true;
			$fileContent = DFileSystem::Load(SPath::DESIGN.$File);
			$FileOpened = true;
		}
		
		////////
		//save//
		////////
		
		if(PAuthorisation::has('org.bambus-cms.layout.stylesheet.change') && $allowEdit && $FileOpened)
		{
			//content changed?
			if(RSent::has('content'))
			{
			   	if(RSent::get('content') != $fileContent)
			   	{
			        //do the save operation
			        if(DFileSystem::Save(SPath::DESIGN.$File, RSent::get('content')))
			        {
			        	SNotificationCenter::report('message', '.file_saved');
			        	$fileContent = RSent::get('content');
			        }
			        else
			        {
			        	SNotificationCenter::report('alert', 'saving_failed');
			        }
			   	}
			}
		}
		
		//////////////////
		//manager delete//
		//////////////////
		if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambus-cms.layout.stylesheet.delete'))
		{
			$files = DFileSystem::FilesOf(SPath::DESIGN, '/\.('.implode('|', $allowed).')/i');
			foreach($files as $file)
			{
				if($file == 'default.css')
					continue;
				if(RSent::hasValue('select_'.md5($file)))
				{
			        if(@unlink(SPath::DESIGN.$file)){
			            SNotificationCenter::report('message', 'file_deleted');
			        }else{
			            SNotificationCenter::report('warning', 'could_not_delete_file');
			        }
					
				}
			}
		}
		
		//////////
		//delete//
		//////////
		
		if(PAuthorisation::has('org.bambus-cms.layout.stylesheet.delete') && RURL::get('_action') == 'delete' && $File != 'default.css' && $allowEdit)
		{
			//kill it
			unlink(SPath::DESIGN.$File);
		    SNotificationCenter::report('message', 'file_deleted');
			$FileOpened = false;
		}
		elseif(PAuthorisation::has('org.bambus-cms.layout.stylesheet.delete') && RURL::get('_action') == 'delete' && $File == 'default.css')
		{
			SNotificationCenter::report('warning', 'this_file_cannott_be_deleted');
		}
		
		//////////
		//rename//
		//////////
		
		if(PAuthorisation::has('org.bambus-cms.layout.stylesheet.create') && PAuthorisation::has('org.bambus-cms.layout.stylesheet.delete') && $allowEdit && $FileOpened)
		{
		    if(RSent::hasValue('filename') && $FileName != RSent::get('filename') && $FileName != 'default.css' && file_exists(SPath::DESIGN.$File))
		    {
				rename(SPath::DESIGN.$File, SPath::DESIGN.basename(RSent::get('filename')).'.css');
				$FileName = basename(RSent::get('filename'));
				$File = basename(RSent::get('filename')).'.css';
		        SNotificationCenter::report('message', 'file_renamed');
		    }
		}
	}	
	if(count($Files) > 0 && (!RURL::has('tab') || RURL::get('tab') == 'edit_css'))
	{
		$EditingObject = ($File == 'default.css') ? SLocalization::get('default.css').'.css' : $File;	
	}

}
elseif(BAMBUS_APPLICATION_TAB == 'edit_templates')
{
	$allowEdit = true;
	$Suffix = '.tpl';
	$DefaultFile = 'header.tpl';
	$Path = SPath::TEMPLATES;
	$doNotDelete = array('page.tpl');
	$ListTypes = array('tpl');
	$Files = DFileSystem::FilesOf($Path, '/\.('.implode('|', $ListTypes).')/i');
	
	//////////
	//create//
	//////////
	if(PAuthorisation::has('org.bambus-cms.layout.template.create') && RURL::get('_action') == 'create')
	{
		$i = 0;
		while(file_exists($Path.SLocalization::get('new').'-'.++$i.$Suffix))
			;
		$File = SLocalization::get('new').'-'.$i.$Suffix;
		$fileContent = '<!-- '.SLocalization::get('new_template').' -->';
		DFileSystem::Save($Path.$File, $fileContent);
		$FileName = SLocalization::get('new').'-'.$i;
		$allowEdit = false;
		$FileOpened = true;
	}
	
	if(count($Files) > 0)
	{
		if($allowEdit && RURL::hasValue('edit') && in_array(RURL::get('edit'), $Files))
		{
			$File = RURL::get('edit');
			$FileName = (in_array($File, $doNotDelete)) ? SLocalization::get($File) : htmlentities(substr($File, 0, -4));
			$allowEdit = true;
			$FileOpened = true;
			$fileContent = DFileSystem::Load($Path.$File);
		}
		
		////////
		//save//
		////////
		
		if(PAuthorisation::has('org.bambus-cms.layout.template.change') && $allowEdit && $FileOpened)
		{
			//content changed?
			if(RSent::has('content'))
			{
			   	if(RSent::get('content') != $fileContent)
			   	{
			        //do the save operation
			        if(DFileSystem::Save($Path.$File, RSent::get('content')))
			        {
			        	$fileContent = RSent::get('content');
			        }
			        else
			        {
			        	SNotificationCenter::report('warning', 'saving_failed');
			        }
			   	}
			}
		}
		
		//////////
		//delete//
		//////////
		
		if(PAuthorisation::has('org.bambus-cms.layout.template.delete') && RURL::get('_action') == 'delete' && !in_array($File, $doNotDelete) && $allowEdit)
		{
			//kill it
			unlink($Path.$File);
		    SNotificationCenter::report('message', 'file_deleted');
			$FileOpened = false;
		}
		elseif(PAuthorisation::has('org.bambus-cms.layout.template.delete') && RURL::get('_action') == 'delete' && in_array($File, $doNotDelete))
		{
			SNotificationCenter::report('warning', 'this_file_cannott_be_deleted');
		}
		
		//////////
		//rename//
		//////////
		
		if(PAuthorisation::has('org.bambus-cms.layout.template.create') && PAuthorisation::has('org.bambus-cms.layout.template.delete') && $allowEdit && $FileOpened)
		{
		    if(RSent::hasValue('filename') && $FileName != RSent::get('filename') && file_exists($Path.$File))
		    {
				rename($Path.$File, $Path.basename(RSent::get('filename')).$Suffix);
				$FileName = basename(RSent::get('filename'));
				$File = basename(RSent::get('filename')).$Suffix;
		        SNotificationCenter::report('message', 'file_renamed');
		    }
		}
		$EditingObject = $FileName.'.tpl';
		

	}
}
echo '<form method="post" id="documentform" name="documentform" action="'
	,SLink::link(array('edit' => $File))
	,'">';

if(BAMBUS_APPLICATION_TAB != 'manage')
{
	try{
		echo new WSidebar(null);
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
		
	}	
}

$OFD = new WOpenFileDialog();
$OFD->registerCategory('stylesheet');
$OFD->registerCategory('template');
$cssFiles = DFileSystem::FilesOf(SPath::DESIGN, '/\.css/i');
foreach($cssFiles as $item)
{
    $OFD->addItem('stylesheet',$item,SLink::link(array('edit' => $item,'tab' => 'edit_css')),'stylesheet', DFileSystem::formatSize(filesize(SPath::DESIGN.$item)));
}
$tplFiles = DFileSystem::FilesOf(SPath::TEMPLATES, '/\.tpl/i');
foreach($tplFiles as $item)
{
    $OFD->addItem('template',$item,SLink::link(array('edit' => $item,'tab' => 'edit_templates')),'template', DFileSystem::formatSize(filesize(SPath::TEMPLATES.$item)));
}
$OFD->render();
?>