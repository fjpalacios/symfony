<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Post
 *
 * @ORM\Table(name="post", options={"engine"="MyISAM"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 */
class Post
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=60)
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="mod_date", type="datetime")
     */
    private $modDate;

    /**
     * @var string
     *
     * @ORM\Column(name="title_es", type="text")
     */
    private $titleEs;

    /**
     * @var string
     *
     * @ORM\Column(name="title_en", type="text", nullable=true)
     */
    private $titleEn;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=200)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content_es", type="text")
     */
    private $contentEs;

    /**
     * @var string
     *
     * @ORM\Column(name="content_en", type="text", nullable=true)
     */
    private $contentEn;

    /**
     * @var string
     *
     * @ORM\Column(name="excerpt_es", type="text")
     */
    private $excerptEs;

    /**
     * @var string
     *
     * @ORM\Column(name="excerpt_en", type="text", nullable=true)
     */
    private $excerptEn;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=100, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="comment_status", type="string", length=20)
     */
    private $commentStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="comment_count", type="bigint")
     */
    private $commentCount;

    /**
     * @var int
     *
     * @ORM\Column(name="views", type="bigint")
     */
    private $views;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Post
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Post
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Post
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set modDate
     *
     * @param \DateTime $modDate
     *
     * @return Post
     */
    public function setModDate($modDate)
    {
        $this->modDate = $modDate;

        return $this;
    }

    /**
     * Get modDate
     *
     * @return \DateTime
     */
    public function getModDate()
    {
        return $this->modDate;
    }

    /**
     * Set title ES
     *
     * @param string $titleEs
     *
     * @return Post
     */
    public function setTitleEs($titleEs)
    {
        $this->titleEs = $titleEs;

        return $this;
    }

    /**
     * Get title ES
     *
     * @return string
     */
    public function getTitleEs()
    {
        return $this->titleEs;
    }

    /**
     * Set title EN
     *
     * @param string $titleEn
     *
     * @return Post
     */
    public function setTitleEn($titleEn)
    {
        $this->titleEn = $titleEn;

        return $this;
    }

    /**
     * Get title EN
     *
     * @return string
     */
    public function getTitleEn()
    {
        return $this->titleEn;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content ES
     *
     * @param string $contentEs
     *
     * @return Post
     */
    public function setContentEs($contentEs)
    {
        $this->contentEs = $contentEs;

        return $this;
    }

    /**
     * Get content ES
     *
     * @return string
     */
    public function getContentEs()
    {
        return $this->contentEs;
    }

    /**
     * Set content EN
     *
     * @param string $contentEn
     *
     * @return Post
     */
    public function setContentEn($contentEn)
    {
        $this->contentEn = $contentEn;

        return $this;
    }

    /**
     * Get content EN
     *
     * @return string
     */
    public function getContentEn()
    {
        return $this->contentEn;
    }

    /**
     * Set excerpt ES
     *
     * @param string $excerptEs
     *
     * @return Post
     */
    public function setExcerptEs($excerptEs)
    {
        $this->excerptEs = $excerptEs;

        return $this;
    }

    /**
     * Get excerpt ES
     *
     * @return string
     */
    public function getExcerptEs()
    {
        return $this->excerptEs;
    }

    /**
     * Set excerpt EN
     *
     * @param string $excerptEn
     *
     * @return Post
     */
    public function setExcerptEn($excerptEn)
    {
        $this->excerptEn = $excerptEn;

        return $this;
    }

    /**
     * Get excerpt EN
     *
     * @return string
     */
    public function getExcerptEn()
    {
        return $this->excerptEn;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Post
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Post
     */
    public function setImage($image)
    {
        $this->format = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set commentStatus
     *
     * @param string $commentStatus
     *
     * @return Post
     */
    public function setCommentStatus($commentStatus)
    {
        $this->commentStatus = $commentStatus;

        return $this;
    }

    /**
     * Get commentStatus
     *
     * @return string
     */
    public function getCommentStatus()
    {
        return $this->commentStatus;
    }

    /**
     * Set commentCount
     *
     * @param integer $commentCount
     *
     * @return Post
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    /**
     * Get commentCount
     *
     * @return int
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * Set views
     *
     * @param integer $views
     *
     * @return Post
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }
}
