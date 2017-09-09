<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PublicControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'bob',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/es/login/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testSitemap()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/sitemap.xml');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testRss()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/es/rss/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testAdminRoleAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/admin/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testAdminRoleUser()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'bob',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/admin/');
        $this->assertSame(Response::HTTP_FORBIDDEN,
            $client->getResponse()->getStatusCode());
    }

    public function testRedirectionAfterLoginSuccess()
    {
        $this->assertTrue(
            $this->submitLoginForm('alice', '123456')
                ->getResponse()->isRedirect('/es/admin/')
        );
        $this->assertTrue(
            $this->submitLoginForm('bob', '123456')
                ->getResponse()->isRedirect('/es/')
        );
        $this->assertTrue(
            $this->submitLoginForm('charlie', '123456')
                ->getResponse()->isRedirect('/es/admin/')
        );
    }

    private function submitLoginForm($username, $password)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/es/login/');
        $form = $crawler->selectButton('submit')->form(array(
            '_username' => $username,
            '_password' => $password,
        ));
        $client->submit($form);
        return $client;
    }
}
