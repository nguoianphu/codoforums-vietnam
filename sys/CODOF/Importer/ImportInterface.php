<?php


/*
 * @CODOLICENSE
 */

namespace CODOF\Importer;

/**
 * 
 * 
 * CODOFORUM STANDARDS
 * 
 * ! -> implies required
 * 
 * All functions accept Array of below properties
 * Required properties must exist in the object 
 * 
 * 1. forum categories
 * 
 *   !cat_pid => parent id of category
 *   !cat_name => name of category
 *   cat_description => description of category
 *   cat_img => image for category 
 *   cat_order => category order
 */


interface ImportInterface {
    
    /**
     * 
     * Inserts a new category in codoforum categories table
     * 
     * @param Array $cat
     * Accepts array of (See 1. forum categories)
     */
    public function ins_cat($cat);
    
    /**
     * 
     * Inserts a new category in codoforum categories table
     * 
     * @param Array $cat
     * Accepts array of (See 1. forum categories)
     */
    public function ins_topics($cat);
    
}
