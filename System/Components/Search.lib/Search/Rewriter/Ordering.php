<?php
class Search_Rewriter_Ordering
	implements Search_Rewriter
{
	public function rewriteSearchRequest(Search_Request $request) {
		$orders = array(
			'orderby' => 'date',
			'order'   => 'down'
		);
		foreach ($orders as $section => $value){
			if(!$request->hasSection($section)){
				$request->addSection($section);
				$request->addRequestElement($section, $request->createRequestElement($value));
			}
		}
	}
}
?>