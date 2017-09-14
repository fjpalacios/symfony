<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\FixturesTrait;
use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testAdminHomePage()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->request('GET', '/es/admin/');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
    }

    public function testAddPost()
    {
        $postTitleEs = 'Testing title ' . mt_rand();
        $postContentEs = $this->getPostContent();
        $postExcerptEs = $this->getRandomPostExcerpt();
        $postTitleEn = 'Testing title ' . mt_rand();
        $postContentEn = $this->getPostContent();
        $postExcerptEn = $this->getRandomPostExcerpt();
        $postStatus = 'draft';
        $postCommentStatus = 'open';
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'charlie',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/admin/posts/add');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_post[titleEs]' => $postTitleEs,
            'appbundle_post[contentEs]' => $postContentEs,
            'appbundle_post[excerptEs]' => $postExcerptEs,
            'appbundle_post[titleEn]' => $postTitleEn,
            'appbundle_post[contentEn]' => $postContentEn,
            'appbundle_post[excerptEn]' => $postExcerptEn,
            'appbundle_post[status]' => $postStatus,
            'appbundle_post[commentStatus]' => $postCommentStatus
        ));
        $client->submit($form);
        $post = $client->getContainer()->get('doctrine')
            ->getRepository(Post::class)->findOneBy(array(
                'titleEs' => $postTitleEs,
            ));
        $this->assertNotNull($post);
        $this->assertSame($postExcerptEs, $post->getExcerptEs());
        $this->assertSame($postContentEs, $post->getContentEs());
    }

    public function testEditPost()
    {
        $newBlogPostTitle = 'Testing title ' . mt_rand();
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/admin/posts/edit/1');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_post[titleEn]' => $newBlogPostTitle,
        ));
        $client->submit($form);
        $post = $client->getContainer()->get('doctrine')
            ->getRepository(Post::class)->find(1);
        $this->assertSame($newBlogPostTitle, $post->getTitleEn());
    }

    public function testDeletePost()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/es/admin/posts/del/1');
        $post = $client->getContainer()->get('doctrine')
            ->getRepository(Post::class)->find(1);
        $this->assertNull($post);
    }

    public function testAddUser()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'charlie',
            'PHP_AUTH_PW' => '123456',
        ]);
        $newUserPassword = '123456';
        $newUserUsername = 'dan';
        $newUserName = 'Dan';
        $newUserEmail = 'dan@test.com';
        $newUserBio = 'Testing Bio';
        $newUserUrl = 'hhtps://sargantanacode.es/';
        $crawler = $client->request('GET', '/es/admin/users/add');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_user[plainPassword][first]' => $newUserPassword,
            'appbundle_user[plainPassword][second]' => $newUserPassword,
            'appbundle_user[username]' => $newUserUsername,
            'appbundle_user[email]' => $newUserEmail,
            'appbundle_user[name]' => $newUserName,
            'appbundle_user[url]' => $newUserUrl,
            'appbundle_user[bio]' => $newUserBio
        ));
        $client->submit($form);
        $user = $client->getContainer()->get('doctrine')
            ->getRepository(User::class)->findOneBy(array(
                'username' => $newUserUsername,
            ));
        $this->assertNotNull($user);
        $this->assertSame($newUserUsername, $user->getUsername());
        $this->assertSame($newUserEmail, $user->getEmail());
    }

    public function testEditUser()
    {
        $newUsername = 'dan' . mt_rand();
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/admin/users/edit/2');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_user[username]' => $newUsername,
        ));
        $client->submit($form);
        $post = $client->getContainer()->get('doctrine')
            ->getRepository(User::class)->find(2);
        $this->assertSame($newUsername, $post->getUsername());
    }

    public function testDeleteUser()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/es/admin/users/del/2');
        $user = $client->getContainer()->get('doctrine')
            ->getRepository(User::class)->find(2);
        $this->assertNull($user);
    }

    public function testAddCategory()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'charlie',
            'PHP_AUTH_PW' => '123456',
        ]);
        $newCategoryName = 'category' . mt_rand();
        $newDescriptionEs = 'Nueva categoría de prueba';
        $newDescriptionEn = 'New test category';
        $crawler = $client->request('GET', '/es/admin/categories/add');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_category[name]' => $newCategoryName,
            'appbundle_category[descriptionEs]' => $newDescriptionEs,
            'appbundle_category[descriptionEn]' => $newDescriptionEn
        ));
        $client->submit($form);
        $category = $client->getContainer()->get('doctrine')
            ->getRepository(Category::class)->findOneBy(array(
                'name' => $newCategoryName,
            ));
        $this->assertNotNull($category);
        $this->assertSame($newCategoryName, $category->getName());
        $this->assertSame($newDescriptionEn, $category->getDescriptionEn());
    }

    public function testEditCategory()
    {
        $newCategoryName = 'category' . mt_rand();
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'charlie',
            'PHP_AUTH_PW' => '123456',
        ]);
        $crawler = $client->request('GET', '/es/admin/categories/edit/1');
        $this->assertSame(Response::HTTP_OK,
            $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('submit')->form(array(
            'appbundle_category[name]' => $newCategoryName
        ));
        $client->submit($form);
        $category = $client->getContainer()->get('doctrine')
            ->getRepository(Category::class)->find(1);
        $this->assertSame($newCategoryName, $category->getName());
    }

    public function testDeleteCategory()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'alice',
            'PHP_AUTH_PW' => '123456',
        ]);
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/es/admin/categories/del/1');
        $user = $client->getContainer()->get('doctrine')
            ->getRepository(Category::class)->find(1);
        $this->assertNull($user);
    }
}
