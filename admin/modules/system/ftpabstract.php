<?php


        
        if(function_exists('ftp_connect')){
            
              require 'ftp_func.php';
            
         
            
        }else{
             require 'class-ftp.php';
          
        }
