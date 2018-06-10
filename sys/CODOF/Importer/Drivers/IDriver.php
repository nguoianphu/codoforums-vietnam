<?php

/*
 * @CODOLICENSE
 */

/**
 * Classes that implement this interface will fetch required information
 * from forum to be imported and give it to dear codoforum to insert it.
 */

interface IDriver {

    
    /**
     * No row limit applied, since no sane forum will have unlimited categories
     */
    public function getCategories();
    
    /**
     * 
     * @param int $start
     */
    public function getTopics($start);
    
    
    /**
     * 
     * @param int $start
     */
    public function getPosts($start);

    
    /**
     * 
     * @param int $start
     */
    public function getUsers($start);
    
    
    /**
     * 
     * @param string $mail
     */
    public function getUserIdByMail($mail);
}
