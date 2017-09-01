<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:03
 */

namespace MiribotBundle\Helper;

use MyProject\Proxies\__CG__\stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;
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
        $file = $this->kernel->getContainer()->getParameter('path_chatlog');
        $chatlog = @fopen($file, "a+b");
        if ($chatlog) {
            $userInfo = $this->memory->recallUserData('userinfo');
            $username = isset($userInfo['username']) ? $userInfo['username'] : "User";
            $file = "\xEF\xBB\xBF" . $file;
            fputs($chatlog, "[{$username}] >> {$userInput}\n");
            fputs($chatlog, "[Bot] >> {$botAnswer}\n");
            fclose($chatlog);
        }
    }

    /**
     * Calculate math expression
     * @param $mathString
     * @return int
     */
    public function calculateMathInString($mathString)
    {
        $mathString = trim($mathString); // trim white spaces
        $mathString = mb_eregi_replace('[^0-9\+\-\*\/]', '', $mathString); // remove any non-numbers chars; exception for math operators
        $compute = @create_function("", "return (" . $mathString . ");");
        if ($compute) {
            return 0 + $compute();
        }
        return false;
    }

    /**
     * Upload file to Dropbox
     * @param $filename
     * @return bool
     */
    public function uploadToDropbox($filename)
    {
        try {
            $accessToken = $this->kernel->getContainer()->getParameter('dropbox_api_token');
            $maxUploadSize = $this->kernel->getContainer()->getParameter('dropbox_max_upload_size');

            // Get file rev from memory if presented
            $filenameHash = md5($filename);
            $rev = $this->memory->recallUserData("file.{$filenameHash}.rev");
            $size = $this->memory->recallUserData("file.{$filenameHash}.size");

            if ($size > $maxUploadSize) {
                return false;
            }

            $params = json_encode(array(
                "path" => '/' . basename($filename),
                "mode" => array(
                    ".tag" => "update",
                    "update" => $rev
                ),
                "autorename" => true,
                "mute" => false
            ));

            $headers = array(
                "Authorization: Bearer {$accessToken}",
                "Dropbox-API-Arg: {$params}",
                "Content-Type: application/octet-stream"
            );

            $path = $filename;
            $fp = fopen($path, 'rb');
            $filesize = filesize($path);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, fread($fp, $filesize));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/upload');

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode != '200') {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Download file from Dropbox
     * @param $filename
     * @return bool
     */
    public function downloadFromDropbox($filename)
    {
        try {
            $accessToken = $this->kernel->getContainer()->getParameter('dropbox_api_token');

            $params = json_encode(array(
                "path" => '/' . basename($filename)
            ));

            $headers = array(
                "Authorization: Bearer {$accessToken}",
                "Dropbox-API-Arg: {$params}",
                "Content-Type: application/octet-stream"
            );

            $responseHeaders = array();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/download');
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $responseHeaders))
                    $responseHeaders[$name] = [trim($header[1])];
                else
                    $responseHeaders[$name][] = trim($header[1]);

                return $len;
            });

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == '200') {
                if (isset($responseHeaders['dropbox-api-result'])) {
                    $json = array_shift($responseHeaders['dropbox-api-result']);
                    $fileData = json_decode($json);

                    // Save revision to session for later update
                    $filenameHash = md5($filename);
                    $this->memory->rememberUserData("file.{$filenameHash}.rev", $fileData->rev);
                    $this->memory->rememberUserData("file.{$filenameHash}.size", $fileData->size);
                }

                // Save file content to path
                $uploaded = @file_put_contents($filename, $result);

                return ($uploaded !== FALSE);
            }

            return true;
        } catch (Exception $e) {
            return false;
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