<?php

namespace SilexBlog;

class Post
{
    private $id, $author, $title, $created_date, $modified_date, $body;
    // $persisted is true when the data is saved and up-to-date in the database.
    private $persisted = false;

    // Basic constructor.  Pass null for $id when creating a new post that has not been persisted yet.
    public function __construct($id, $author, $title, $created_date, $modified_date, $body) {
        $this->id = $id;
        $this->author = $author;
        $this->title = $title;
        $this->created_date = $created_date;
        $this->modified_date = $modified_date;
        $this->body = $body;
    }

    // Grab all posts from the database written by the given author.
    // YOUR CODE HERE
    //
    // HINTS:
    // Use getAll as the basis for this method.  Modify the method parameters to accept an author variable.
    // You want use to fetchAll to get all the results.  For the SQL query, use LIKE to match the given author
    // against the author column. Look at the query for getById to see how to make the MySQL query using a
    // parameter (how to pass in author to the query).

    
    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @param mixed $created_date
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }

    /**
     * @return mixed
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }

    /**
     * @param mixed $modified_date
     */
    public function setModifiedDate($modified_date)
    {
        $this->modified_date = $modified_date;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */

    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getPersisted()
    {
        return $this->persisted;
    }

    /**
     * @param mixed $persisted
     */
    public function setPersisted($persisted)
    {
        $this->persisted = $persisted;
    }
}