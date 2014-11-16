<?php

namespace \local\classes\curl;

class Main {
    private $info = array(
	array(
	    'HTTP_CODE',
	    'FILETIME',
	    'TOTAL_TIME',
	    'NAMELOOKUP_TIME',
	    'CONNECT_TIME',
	    'PRETRANSFER_TIME',
	    'START_TRANSFER_TIME',
	    'REDIRECT_COUNT',
	    'REDIRECT_TIME',
	    'SIZE_UPLOAD',
	    'SIZE_DOWNLOAD',
	    'SPEED_DOWNLOAD',
	    'SPEED_UPLOAD',
	    'HEADER_SIZE',
	    'HEADER_OUT',
	    'REQUEST_SIZE',
	    'SSL_VERIFYRESULT',
	    'CONTENT_LENGTH_DOWNLOAD',
	    'CONTENT_LENGTH_UPLOAD',
	    'CONTENT_TYPE'
	)
    );
    private $errors = array();
    private $mh = null;
    private $chs = array();
    private $lastChs = null;
    private $curChs = null;
    private $curlOptions = null;
	
	
    public function __construct($mh = FALSE) {
	
    }
    
    public function createChs() {
	$this->lastChs = $this->curChs;
	$this->curChs = $this->chs[] = curl_init();
	if ($this->mh !== null) {
	    curl_multi_add_handle($this->mh, $this->curChs);
	}
    }
    
	public function addOption($curlConstant, $value) {
		$curlOpt = new Opt();
		$curlOpt[$curlConstant] = $value;
		
		$this->curlOptions->attach($curlOpt);
	}
	
	public function resetOptions() {
		$this->curlOptions->removeAll();
	}
	
	private function setOpts(OptionGroupStorage $co) {
		foreach ($co as $opt) {
			curl_setopt($this->curChs, $opt->cName, $opt->value);
		}
	}
	
    public function info() {
	foreach ($this->chs as &$ch) {
	    $this->errors[] = curl_error($ch) . "\n";
	    $this->info[] = array(
		curl_getinfo($ch, CURLINFO_HTTP_CODE),
		curl_getinfo($ch, CURLINFO_FILETIME),
		curl_getinfo($ch, CURLINFO_TOTAL_TIME),
		curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME),
		curl_getinfo($ch, CURLINFO_CONNECT_TIME),
		curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME),
		curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
		curl_getinfo($ch, CURLINFO_REDIRECT_COUNT),
		curl_getinfo($ch, CURLINFO_REDIRECT_TIME),
		curl_getinfo($ch, CURLINFO_SIZE_UPLOAD),
		curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
		curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD),
		curl_getinfo($ch, CURLINFO_SPEED_UPLOAD),
		curl_getinfo($ch, CURLINFO_HEADER_SIZE),
		curl_getinfo($ch, CURLINFO_HEADER_OUT),
		curl_getinfo($ch, CURLINFO_REQUEST_SIZE),
		curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT),
		curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD),
		curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_UPLOAD),
		curl_getinfo($ch, CURLINFO_CONTENT_TYPE)
	    );

	    if ($this->mh) {
		curl_multi_remove_handle($this->mh, $ch);
	    }

	    curl_close($ch);
	}
    }

    public function run() {
	if ($this->mh) {
	    $active = null;

	    do {
		$result = curl_multi_exec($this->mh, $active);
		if ($result > 0) {
		    echo 'ERROR: ' . curl_multi_strerror($result);
		}
		if (curl_multi_select($this->mh) == -1) {
		    break;
		}
	    } while (($active && $result == CURLM_OK) || $result == CURLM_CALL_MULTI_PERFORM);
	} else {
	    $result = curl_exec($this->curChs);
	}
	
	return $result;
    }
    
    public function __destroy() {
	if (!$this->mh) {
	    foreach ($this->chs as &$ch) {
		curl_multi_remove_handle($this->mh, $ch);
		curl_close($ch);
	    }
	    curl_multi_close($this->mh);
	    unset($ch);
	} else {
	    curl_close($this->curChs);
	}
	
	unset($this->chs, $this->lastChs, $this->curChs, $this->mhs);
    }
}