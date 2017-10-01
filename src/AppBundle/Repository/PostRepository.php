<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Post;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends \Doctrine\ORM\EntityRepository
{
    public function getPaginatedIndex($currentPage = 1, $perPage = Post::NUM_ITEMS)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT p, c FROM AppBundle\Entity\Post p JOIN p.category c
                WHERE p.type = 'post' AND p.status = 'publish'
                ORDER BY p.date DESC";
        $query = $em->createQuery($dql)
            ->setFirstResult($perPage * ($currentPage - 1))
            ->setMaxResults($perPage);
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator;
    }

    public function getPaginatedProfile($currentPage = 1, $userId, $perPage = Post::NUM_ITEMS)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT p, c FROM AppBundle\Entity\Post p JOIN p.category c
                WHERE p.type = 'post' AND p.status = 'publish' AND p.author = :userId
                ORDER BY p.date DESC";
        $query = $em->createQuery($dql)
            ->setParameter('userId', $userId)
            ->setFirstResult($perPage * ($currentPage - 1))
            ->setMaxResults($perPage);
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator;
    }

    public function getPaginatedSearch($locale, $search, $currentPage = 1, $perPage = Post::NUM_ITEMS)
    {
        $em = $this->getEntityManager();
        if ($locale == 'es') {
            $dql = "SELECT p, c FROM AppBundle\Entity\Post p JOIN p.category c
                WHERE p.type = 'post' AND p.status = 'publish'
                AND (p.titleEs LIKE :search OR p.contentEs LIKE :search)
                GROUP BY p.id ORDER BY p.date DESC";
        } else {
            $dql = "SELECT p, c FROM AppBundle\Entity\Post p JOIN p.category c
                WHERE p.type = 'post' AND p.status = 'publish'
                AND (p.titleEn LIKE :search OR p.contentEn LIKE :search)
                GROUP BY p.id ORDER BY p.date DESC";
        }
        $query = $em->createQuery($dql)
            ->setParameter('search', '%' . $search . '%')
            ->setFirstResult($perPage * ($currentPage - 1))
            ->setMaxResults($perPage);
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator;
    }
}
