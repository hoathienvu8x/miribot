<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:03
 */

namespace MiribotBundle\Helper;

use Symfony\Component\HttpKernel\Kernel;

class Helper
{
    public $string;
    public $memory;
    public $template;
    protected $kernel;

    public function __construct(Kernel $kernel, MemoryHelper $memory, StringHelper $string, TemplateHelper $template)
    {
        $this->string = $string;
        $this->memory = $memory;
        $this->template = $template;
        $this->kernel = $kernel;
    }

    /**
     * Save user input and bot answer to a chat log
     * @param $userInput
     * @param $botAnswer
     */
    public function saveToChatLog($userInput, $botAnswer)
    {
        $file = $this->kernel->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . "chatlog.txt";
        $chatlog = @fopen($file, "a+b");
        if ($chatlog) {
            $file = "\xEF\xBB\xBF".$file;
            fputs($chatlog, "{$file}\n");
            fputs($chatlog, "[User] >> {$userInput}\n");
            fputs($chatlog, "[Bot] >> {$botAnswer}\n");
            fclose($chatlog);
        }
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