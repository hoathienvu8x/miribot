<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:03
 */

namespace MiribotBundle\Helper;

class Helper
{
    public $string;
    public $memory;
    public $template;

    public function __construct(MemoryHelper $memory, StringHelper $string, TemplateHelper $template)
    {
        $this->string = $string;
        $this->memory = $memory;
        $this->template = $template;
    }

    /**
     * Make a cURL get request
     * @param $url
     * @param array $options
     * @return bool|mixed
     */
    public function cUrlGet($url, $options = array())
    {
        try {
            $ch = curl_init();
            $opts = array(
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36",
                CURLOPT_URL => $url,
            );
            curl_setopt_array($ch, $opts + $options);
            $data = curl_exec($ch);

            if (!$data) {
                return false;
            }

            curl_close($ch);
            return $data;
        } catch (\Exception $e) {
            // Nothing here at the moment
            //throw new \Exception($errorMessage);
        }
        return false;
    }
}