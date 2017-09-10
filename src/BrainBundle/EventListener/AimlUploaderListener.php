<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 06-Sep-17
 * Time: 22:17
 */

namespace BrainBundle\EventListener;

use BrainBundle\Entity\Aiml;
use BrainBundle\Entity\AimlUploader;
use Doctrine\ORM\EntityManager;
use Vich\UploaderBundle\Event\Event;

class AimlUploaderListener
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Event $event
     */
    public function onVichUploaderPostUpload(Event $event)
    {
        $object = $event->getObject();

        if (get_class($object) == 'BrainBundle\Entity\AimlUploader') {
            /** @var AimlUploader $object */
            $file = $object->getFile();
            $object->setFileName($file->getFilename());

            // Parsing AIML content here!
            $this->parseAimlContent($file->getRealPath());
        }

    }

    /**
     * @param $fileName
     */
    private function parseAimlContent($fileName)
    {
        $aiml = new \DOMDocument();
        $fileContent = @file_get_contents($fileName, true);

        if ($fileContent) {
            $aiml->loadXML($fileContent);
            $categories = $aiml->getElementsByTagName('category');

            /** @var \DOMElement $category */
            foreach ($categories as $category) {
                $obj = new Aiml();
                $fingerprint = "";

                $pattern = $category->getElementsByTagName('pattern')->item(0);
                if ($pattern) {
                    $patternString = $category->ownerDocument->saveXML($pattern, LIBXML_NOEMPTYTAG);
                    $obj->setPattern($patternString);
                    $fingerprint .= $patternString;
                }

                $that = $category->getElementsByTagName('that')->item(0);
                if ($that) {
                    $thatString = $category->ownerDocument->saveXML($that, LIBXML_NOEMPTYTAG);
                    $obj->setThat($thatString);
                    $fingerprint .= "_" . $thatString;
                }

                if ($category->parentNode && $category->parentNode->nodeName == 'topic') {
                    $topic = $category->parentNode->getAttribute('name');
                    $obj->setTopic($topic);
                    $fingerprint .= "_" . $topic;
                }

                if ($template = $category->getElementsByTagName('template')->item(0)) {
                    $templateString = $category->ownerDocument->saveXML($template, LIBXML_NOEMPTYTAG);
                    $obj->setTemplate($templateString);
                }

                $hash = md5($fingerprint);
                $obj->setHash($hash);
                $obj->setUpdatedAt(new \DateTimeImmutable());

                $aimlCategory = $this->em->getRepository(get_class($obj))->findOneBy(array(
                    'hash' => $hash
                ));

                if (!$aimlCategory) {
                    $this->em->persist($obj);
                } else {
                    /** @var Aiml $aimlCategory */
                    $aimlCategory->setUpdatedAt(new \DateTimeImmutable());
                    $aimlCategory->setTemplate($template);
                    $this->em->persist($aimlCategory);
                }
            }

            $this->em->flush();
        }

    }

}