<?php

class WPTTest extends PHPUnit_Framework_TestCase
{

	// Demo code to call WPT API, wait for a retrieve result
	// Lots of code borrowed from HTTPArchive.org
	
	protected $wptAPIKey = "andysdemorocks";
	protected $wptServer = "http://velocity.webpagetest.org/";
	protected $location = "Europe";
	
	//$testURL = "http://news.bbc.co.uk";
	protected $testURL = "http://andydavies.github.com/veudemo/";
	
	// Fetch file and retry if it fails.
	protected function fetchUrl($fn) {
		$contents = file_get_contents($fn);
		return $contents;
	}
	
	protected function submitTest() {
		
//		global $wptAPIKey, $wptServer, $testURL, $location;
		
		$id = "";
	
		$request = $wptServer . 'runtest.php?f=xml&url=' . urlencode($testURL) . 
			"&location=" . $location .
			( $wptAPIKey ? "&k=" . $wptAPIKey : "" );
	
		$doc = new DOMDocument();
		
		if ( $doc ) {
			$response = fetchUrl($request);
			
			if ( strlen($response) ) {
				$doc->loadXML($response);
				$nodes = $doc->getElementsByTagName('statusCode');
				$code = (int)trim($nodes->item(0)->nodeValue);
	
				echo $code . "\n";
	
				if ( $code == 200 ) {
					// Update status col in status table
					$nodes = $doc->getElementsByTagName('testId');
					$id = trim($nodes->item(0)->nodeValue);
				} 
			}
			unset( $doc );
		}
		return $id;
	}
	
	protected function getTestStatus($id) {
//		global $wptServer;
		
		$code = "";
	
		$request = $wptServer . 'testStatus.php?f=xml&test=' . urlencode($id);
		
		$doc = new DOMDocument();
		
		if ( $doc ) {
			$response = fetchUrl($request);
	
			if ( strlen($response) ) {
				$doc->loadXML($response);
				$nodes = $doc->getElementsByTagName('statusCode');
				$code = (int)trim($nodes->item(0)->nodeValue);
			}
			unset( $doc );
		}
		return $code;
	}
	
	protected function getTestResult($id) {
//		global $wptServer;
		
		$code = "";
		$scoreCompress = 0;
		$fail = true;
	
		$request = $wptServer . 'xmlResult/' . urlencode($id) . "/";
		
		$doc = new DOMDocument();
		
		if ( $doc ) {
			$response = fetchUrl($request);
	
			if ( strlen($response) ) {
				$doc->loadXML($response);
				$nodes = $doc->getElementsByTagName('statusCode');
				$code = (int)trim($nodes->item(0)->nodeValue);
				
				if($code == 200) {
					$nodes = $doc->getElementsByTagName('score_compress');
					$scoreCompress = (int)trim($nodes->item(0)->nodeValue);
					if ($scoreCompress < 75) {
						$fail = true;
						// throw the exception here
					}
					$this->assertGreaterThanOrEqual(75, score, "Make the images smaller");
				}
			}
			unset( $doc );
		}
		return $fail;
	}
	
	protected function testSite() {
		
		// submit test
		$testID = submitTest();
		
		if(strlen($testID)) {
			$status = 100;
		
			$wait = 10;
			$maxWait = 100;
		
			while($status < 200 && $wait < $maxWait) {
		
				$status = getTestStatus($testID);
		
				if($status < 200) {
					sleep($wait);
					$wait *= 1.5;
				}
		
			}
		
			if($status == 200) {
				getTestResult($testID);
			}
		}
	}  
}

?>