<?php

namespace BrainBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class WernickeControllerTest extends WebTestCase
{
    public function testMarkovAction()
    {
        // Make new client
        $client = $this->makeClient();

        // Request for page
        $crawler = $client->request('GET', '/brain/markov');

        // Check if the request is success
        $this->assertStatusCode(200, $client);

        // Select a form
        $form = $crawler->selectButton('Submit')->form();

        // Submit without data
        $crawler = $client->submit($form);
        // Check if status code is 200
        $this->assertStatusCode(200, $client);
        // Check if contains error
        $this->assertContains('Input text must be provided', $client->getResponse()->getContent());

        // Submit the form with data
        $form = $crawler->selectButton('Submit')->form();
        $form->setValues(['form[input_text]' => 'Lorem Ipsum dolor sit amet', 'form[length]' => '10']);
        $client->submit($form);
        $this->assertStatusCode(200, $client);
    }
}
