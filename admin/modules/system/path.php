<?php



class finder {

   public $ftp=null;
   public $mstring="";
   
   
    function connect($ftp) {


        
        $this->ftp=$ftp;
        
             
       
    }
    
    function searcher(){
        
        $p=trim(ABSPATH,"/");
        
        $parts=explode("/",$p);
        //$parts[]="";
        
        
        
        var_dump($parts);
        $parts=array_reverse($parts);
        $cpath="";
        
        $paths=array("./");
        
        foreach($parts as $part){
            
            $cpath=$part."/".$cpath;
            $paths[]=$cpath;
         
            
        }
       
        $the_path="";
        
        foreach($paths as $path){
            
            if($this->ftp->is_exists($path.$this->mstring)){
                
                $the_path=$path;
                break;
                
            }
            
        }
        
        str_replace($this->mstring, "", $the_path);
        
        var_dump($the_path);
        
        return $the_path;
        
    }
    
    

    
    
    
}




/*

/
index.php


/htdocs/
       /index.php
       
       
/hdocs/
      /forum/index.php
      
/www/
    /forum/index.php

/codoforum
          /index.php
         /codoforum/
                    /index.php

 /o/l/h/c/c/i.php

 
   /

 * 
 *  */  