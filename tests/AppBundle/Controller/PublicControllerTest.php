<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PublicControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'bob',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->request('GET', '/es/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/es/login/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testSitemap()
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testRss()
    {
        $client = static::createClient();
        $client->request('GET', '/es/rss/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testPost()
    {
        $client = static::createClient();
        $blogPost = $client->getContainer()->get('doctrine')
            ->getRepository(Post::class)->find(1);
        $client->request('GET', sprintf('/es/%s', $blogPost->getSlug()));
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testNewComment()
    {
        $newCommentAuthor = 'Bob';
        $newCommentEmail = 'testing@test.com';
        $newCommentComment = 'Hey, guys!';
        $client = static::createClient();
        $blogPost = $client->getContainer()->get('doctrine')
            ->getRepository(Post::class)->find(1);
        $crawler = $client->request('GET', sprintf('/es/%s', $blogPost->getSlug()));
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_comment[author]' => $newCommentAuthor,
            'appbundle_comment[email]' => $newCommentEmail,
            'appbundle_comment[comment]' => $newCommentComment
        ));
        $client->submit($form);
        $comment = $client->getContainer()->get('doctrine')
            ->getRepository(Comment::class)->findOneBy(array(
                'email' => $newCommentEmail,
            ));
        $this->assertSame($newCommentEmail, $comment->getEmail());
    }

    public function testProfile()
    {
        $client = static::createClient();
        $client->request('GET', '/es/profile/alice');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testAdminRoleAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->request('GET', '/es/admin/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testAdminRoleUser()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'bob',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->request('GET', '/es/admin/');
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
