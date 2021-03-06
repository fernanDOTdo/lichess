<?php

namespace Application\ForumBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Bundle\ForumBundle\Document\Category as BaseCategory;

/**
 * @MongoDB\Document(
 *   repositoryClass="Bundle\ForumBundle\Document\CategoryRepository",
 *   collection="forum_category"
 * )
 */
class Category extends BaseCategory
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Topic")
     */
    protected $lastTopic;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Post")
     */
    protected $lastPost;
}
