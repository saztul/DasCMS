<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//document title
echo '<div id="objectInspectorActiveFullBox">';
//editing allowed?
	
if(PAuthorisation::has('org.bambus-cms.content.cpage.change') && isset($Page) && $Page instanceof CPage)
{
		
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
		, htmlentities($Page->Title, ENT_QUOTES, 'UTF-8')
		, htmlentities($Page->Title, ENT_QUOTES, 'UTF-8')
		);
	//////////////
	//css editor//
	//////////////
	echo LGui::beginEditorWrapper();
	echo LGui::editorTextarea($Page->Content);
	echo LGui::endEditorWrapper();
	echo new WScript('initeditor();');
}
else
{
	echo new WScript('BCMSRunFX[BCMSRunFX.length] = function(){OBJ_ofd.show()};');
}
echo '</form></div>';
?>