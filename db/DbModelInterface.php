<?php

namespace common\db;


interface DbModelInterface {
    
    /**
     * Will insert a row into the database
     */
    public function insert();
    
    /**
     * Will delete a row from the database
     */
    public function delete();
    
    /**
     * Will update a row in the database 
     */
    public function update();
    
    /**
     * Gets a row from the database.  Returns null when the row is not found
     * 
     * @return array|null 
     */
    public function get() : ?array;
    
    /**
     * Populates the DbModel with a row from the database
     * 
     * @param string $id
     * @return bool
     */
    public function populateFromDb(string $id) : bool;
}