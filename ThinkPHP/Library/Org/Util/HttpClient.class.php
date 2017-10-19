<?php

/* Version 4.0
*/
namespace Org\Util;
class HttpClient {
    // Request vars
    public $host;
    public $port;
    public $path;
    public $method;
    public $postdata = '';
	public $content;
	
    private function get($path, $data = false) {
        $this->path = $path;
        $this->method = 'GET';
        if ($data) {
            $this->path .= '?'.$this->buildQueryString($data);
        }
        return $this->doRequest();
    }
    private function post($path, $data) {
        $this->path = $path;
        $this->method = 'POST';
        $this->postdata = $this->buildQueryString($data);
    	return $this->doRequest();
    }
    private function buildQueryString($data) {
        $querystring = '';
        if (is_array($data)) {
    		foreach ($data as $key => $val) {
    			if (is_array($val)) {
    				foreach ($val as $val2) {
    					$querystring .= urlencode($key).'='.urlencode($val2).'&';
    				}
    			} else {
    				$querystring .= urlencode($key).'='.urlencode($val).'&';
    			}
    		}
    		$querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
    	} else {
    	    $querystring = $data;
    	}
    	return $querystring;
    }
    private function doRequest() {
		$url = $this->getRequestURL();
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );		
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		if($this->method==='POST'){
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $this->postdata );
		}
		$reStr = curl_exec ( $ch );
		curl_close ( $ch );
		$this->content = $reStr;
		return true;		
    }
    private function getRequestURL() {
        $url = 'http://'.$this->host;
        if ($this->port != 80) {
            $url .= ':'.$this->port;
        }            
        $url .= $this->path;
        return $url;
    }
	public function getContent(){
		return $this->content;
	}
    public function quickGet($url) {
        $bits = parse_url($url);
        $this->host = $host = $bits['host'];
        $this->port = $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?'.$bits['query'];
        }
        if (!$this->get($path)) {
            return false;
        } else {
            return $this->getContent();
        }
    }
    public function quickPost($url, $data) {
        $bits = parse_url($url);
        $this->host = $host = $bits['host'];
        $this->port = $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (!$this->post($path, $data)) {
            return false;
        } else {
            return $this->getContent();
        }
    }
}

?>