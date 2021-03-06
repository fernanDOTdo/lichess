<?php

namespace Application\ForumBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Bundle\ForumBundle\Document\Post as BasePost;
use Application\UserBundle\Document\User;

/**
 * @MongoDB\Document(
 *   repositoryClass="Bundle\ForumBundle\Document\PostRepository",
 *   collection="forum_post"
 * )
 */
class Post extends BasePost
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Topic")
     */
    protected $topic;

    /**
     * The author name
     *
     * @MongoDB\String
     * @var string
     */
    protected $authorName = '';

    /**
     * The author user if any
     *
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     * @var User
     */
    protected $author = null;

    /**
     * @Assert\MaxLength(10000)
     */
    protected $message;

    /**
     * @Assert\Blank
     */
    protected $trap;

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param  User
     * @return null
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
        $this->authorName = $author->getUsername();
    }

    /**
     * Get authorName
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    public function getTrap()
    {
        return $this->trap;
    }

    public function setTrap($trap)
    {
        $this->trap = $trap;
    }

    /**
     * Set authorName
     * @param  string
     * @return null
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    public function setMessage($message)
    {
        $this->message = $this->processMessage($message);
    }

    protected function processMessage($message)
    {
        $message = wordwrap($message, 200);
        $message = preg_replace('#(?:http://)?lichess\.org/([\w-]{8})[\w-]{4}#si', 'http://lichess.org/$1', $message);

        return $message;
    }
}
