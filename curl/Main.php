<?php
namespace common\curl;

use \common\collections\CurlOptionGroupStorage as curlOptStorage;
use \common\logging\Logger as Logger;
use common\collections\CurlOptionGroupStorage;

/**
 * cURL PHP wrapper.
 */
class Main implements \Iterator
{

    private $infoOptions = array(
        CURLINFO_EFFECTIVE_URL,
        CURLINFO_HTTP_CODE,
        CURLINFO_FILETIME,
        CURLINFO_TOTAL_TIME,
        CURLINFO_NAMELOOKUP_TIME,
        CURLINFO_CONNECT_TIME,
        CURLINFO_PRETRANSFER_TIME,
        // CURLINFO_START_TRANSFER_TIME,
        CURLINFO_REDIRECT_COUNT,
        CURLINFO_REDIRECT_TIME,
        CURLINFO_REDIRECT_URL,
        CURLINFO_PRIMARY_IP,
        CURLINFO_PRIMARY_PORT,
        CURLINFO_LOCAL_IP,
        CURLINFO_LOCAL_PORT,
        CURLINFO_SIZE_UPLOAD,
        CURLINFO_SIZE_DOWNLOAD,
        CURLINFO_SPEED_DOWNLOAD,
        CURLINFO_SPEED_UPLOAD,
        CURLINFO_HEADER_SIZE,
        CURLINFO_HEADER_OUT,
        CURLINFO_REQUEST_SIZE,
        CURLINFO_SSL_VERIFYRESULT,
        CURLINFO_CONTENT_LENGTH_DOWNLOAD,
        CURLINFO_CONTENT_LENGTH_UPLOAD,
        CURLINFO_CONTENT_TYPE,
        CURLINFO_PRIVATE
    );

    private $mh = FALSE;

    private $chs = [];

    private $ch = null;

    private $curlOptions = null;

    private $output = FALSE;
 // is an array if curl option return transfer is true
    
    /**
     *
     * @param boolean $mh
     *            TRUE to run a curl multi handle
     */
    public function __construct($mh = FALSE)
    {
        $this->reset($mh);
    }

    /**
     * resets
     * 
     * @param string $mh            
     */
    public function reset($mh = FALSE)
    {
        $this->close();
        $this->mh = $mh;
        $this->chs = [];
        $this->ch = null;
        $this->curlOptions = new CurlOptionGroupStorage();
        $this->resetOptions();
        $this->output = FALSE;
    }

    /**
     * Creates a curl handle or a curl multi handle
     * 
     * @return resource the current curl handle
     */
    public function create()
    {
        $this->chs[] = $this->ch = curl_init();
        if ($this->mh !== FALSE) {
            if ((is_resource($this->mh) && ! get_resource_type($this->mh) === 'curl_multi') || ($this->mh = curl_multi_init()) !== FALSE) {
                curl_multi_add_handle($this->mh, $this->ch);
            }
        }
        
        return $this->ch;
    }

    /**
     * Get current value of $chs
     * 
     * @return array
     */
    public function current()
    {
        return current($this->chs);
    }

    /**
     * Get key of $chs
     * 
     * @return mixed
     */
    public function key()
    {
        return key($this->chs);
    }

    /**
     * Get next value of $chs
     * 
     * @return array
     */
    public function next()
    {
        return next($this->chs);
    }

    /**
     * Rewind the $chs array
     * 
     * @return type
     */
    public function rewind()
    {
        return rewind($this->chs);
    }

    /**
     * Is the current position valid
     * 
     * @return type
     */
    public function valid()
    {
        return $this->current();
    }

    /**
     * Will return the current curl handle
     * 
     * @return resource
     */
    public function getCh()
    {
        return $this->ch;
    }

    /**
     * Will return the curl handles
     * 
     * @return resource
     */
    public function getChs()
    {
        return $this->chs;
    }

    /**
     * Add curl options to all handles in the $chs variable
     * 
     * @param type $curlOptions            
     */
    public function addOptions(array $curlOptions) : void
    {
        foreach ($this->chs as $chs) {
            foreach ($curlOptions as $curlConstant => $value) {
                $this->addOption($curlConstant, $value, $chs);
            }
        }
    }

    /**
     * Adds options to the curl handles
     * 
     * @param int $curlConstant
     *            a name of a curl constant for curl_setopt
     * @param mixed $value
     *            a value to a curl constant for curl_setopt
     * @param resource $ch
     *            a curl resource
     * @throws InvalidArgumentException when no option can be set
     */
    public function addOption(int $curlConstant, $value, $ch = null) : bool
    {
        $curlOpt = new Opt();
        try {
            $curlOpt->cName = $curlConstant;
            $curlOpt->value = $value;
            
            if ($curlOpt->cName === CURLOPT_RETURNTRANSFER && $curlOpt->value === TRUE) {
                $this->output = [];
            }
            
            if (is_resource($ch) && get_resource_type($ch) === 'curl' && in_array($ch, $this->chs, TRUE)) {
                curl_setopt($ch, $curlOpt->cName, $curlOpt->value);
                $this->ch = $ch;
            } else 
                if (strpos($curlOpt->cName, 'CURLMOPT_') === 0 && $this->mh !== FALSE) {
                    curl_multi_setopt($this->mh, $curlOpt->cName, $curlOpt->value);
                } else 
                    if ($ch === null) {
                        curl_setopt($this->ch, $curlOpt->cName, $curlOpt->value);
                    } else {
                        throw new InvalidArgumentException('Could not set curl option, is @param $ch invalid: ' . var_export($ch, TRUE));
                    }
            
            $this->curlOptions->attach($curlOpt);
            $result = TRUE;
        } catch (\RuntimeException | \UnexpectedValueException $e) {
            \common\logging\Error::handle($e);
            $result = FALSE;
        }
        
        return $result;
    }

    /**
     * Removes curl options set durng addOption from the curl option storage @var curlOptions
     */
    public function resetOptions()
    {
        $this->curlOptions->removeAll($this->curlOptions);
        $this->curlOptions = new CurlOptionGroupStorage();
    }

    /**
     * Will return an array with information from curl_getinfo populated for each curl handle
     * return array
     */
    public function info() : array
    {
        $info = [];
        foreach ($this->chs as $i => $ch) {
            foreach ($this->infoOptions as $option) {
                $info[$i][$option] = array(
                    $option,
                    curl_getinfo($ch, $option)
                );
            }
        }
        unset($ch, $i, $option);
        
        return $info;
    }

    /**
     * Runs all curl handles and will log curl errors using Logger.
     * Output can be stored in @var output if CURLOPT_RETURNTRANSFER is TRUE
     * 
     * @return mixed the result of curl_exec
     */
    public function run()
    {
        if ($this->mh !== FALSE) {
            $active = null;
            
            do {
                $result = curl_multi_exec($this->mh, $active);
                if ($result > 0) {
                    Logger::obj()->writeDebug(curl_multi_strerror($result), - 1);
                }
                if (curl_multi_select($this->mh) == - 1) {
                    break;
                }
            } while (($active && $result == CURLM_OK) || $result == CURLM_CALL_MULTI_PERFORM);
            if (is_array($this->output)) {
                $this->output = array_map(function ($ch) {
                    return array(
                        curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
                        curl_multi_getcontent($ch)
                    );
                }, $this->chs);
            }
        } else {
            $result = curl_exec($this->ch);
            if (($er = curl_errno($this->ch)) !== 0) {
                Logger::obj()->writeDebug(curl_strerror($er), - 1);
            }
            if (is_array($this->output)) {
                $this->output[] = array(
                    curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL),
                    $result
                );
            }
        }
        
        return $result;
    }

    /**
     *
     * @return mixed will return the output of a request when CURLOPT_RETURNTRANSFER is TRUE FALSE otherwise
     */
    public function getOutput()
    {
        return $this->output;
    }

    public function close()
    {
        if ($this->mh !== FALSE) {
            foreach ($this->chs as $ch) {
                curl_multi_remove_handle($this->mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($this->mh);
            unset($ch);
        } else 
            if (is_resource($this->ch) && get_resource_type($this->ch) === 'curl') {
                curl_close($this->ch);
            }
    }

    /**
     * Closes all curl handles
     */
    public function __destroy()
    {
        $this->close();
        unset($this->chs, $this->ch, $this->mh, $this->curlOptions, $this->info, $this->infoOptions, $this->output);
    }
}
