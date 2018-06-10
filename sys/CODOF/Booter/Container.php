<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Booter;

use ArrayAccess;
use Closure;

class Container  implements ArrayAccess{

    /**
     * Contains mapping to class aliases
     * @var array 
     */
    protected $mappings;

    /**
     * Stores instances of classes . 
     * Useful for singleton classes which have to be reusable
     * @var array 
     */
    protected $instances;

    
    /**
     * 
     * @var array 
     */
    protected $bindings;


    public function bind($alias, Closure $closure, $shared = false) {

        $this->bindings[$alias] = array(
            "class" => $closure,
            "shared" => $shared
        );
    }

    public function bindShared($alias, Closure $closure) {

        $this->bind($alias, $closure, TRUE);
    }

    /*public function mapCoreClasses() {

        $this->mappings = array(
            
            "\CODOF\Database\Connector" => "db"
        );
    }*/
    
    /**
     * 
     * Get the registered Closure from bindings
     * @param type $alias
     * @return type
     */
    protected function getBindedClass($alias) {
        
        return isset($this->bindings[$alias]) ? $this->bindings[$alias]['class'] : $alias;
    }

    /**
     * Resolve and return instance 
     * 
     * @param type $className
     */
    public function make($className) {

        //get alias name from class name to component mapping
        $alias = isset($this->mappings[$className]) ? $this->mappings[$className] : $className;
        
        $class = $this->getBindedClass($alias);
        
        if (isset($this->instances[$alias])) {

            return $this->instances[$alias];
        }

        $object = $this->build($class);

        //save for later re-use
        if($this->bindings[$alias]['shared']) {
            
            $this->instances[$alias] = $object;
        }
        
        return $object;
    }

    /**
     * Automatically resolves class dependencies and returns an instance of it
     * 
     * @param type $class
     * @return \CODOF\Booter\class
     */
    protected function build($class) {
       
        if ($class instanceof Closure) {
            
            return $class($this);
        }

        //first get a reflection of the class
        $ref = new \ReflectionClass($class);

        //get constructor of $class
        $constructor = $ref->getConstructor();

        //no dependencies need to be injected
        if ($constructor == null) {

            return new $class;
        }

        $dependencies = $constructor->getParameters();

        //now create resolved parameters for each required dependency
        $resolved_args = $this->resolveDependencies($dependencies);

        return $ref->newInstanceArgs($resolved_args);
    }

    /**
     * 
     * Resolved constructor argument dependencies by recursively calling 
     * make() if argument is a class else argument is optional
     * @param type $parameters
     * @return type
     */
    protected function resolveDependencies($parameters) {

        $resolved_args = array();

        foreach ($parameters as $parameter) {

            $class_name = $parameter->getClass();

            if ($class_name == null) {

                //this is not a class
                $resolved_args[] = $parameter->getDefaultValue();
            } else {

                //this is a class
                $resolved_args[] = $this->make($parameter->getClass()->name);
            }
        }

        return $resolved_args;
    }

    /**
     * 
     * Determine if the alias/class has been binded
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset) {
        
        return isset($this->bindings[$offset]);
    }

    /**
     * Return object instance after resolving any dependencies
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset) {
        
        return $this->make($offset);
    }

    public function offsetSet($offset, $value) {
     
        $this->bind($offset, $value);
    }

    public function offsetUnset($offset) {
        
        unset($this->bindings[$offset]);
        unset($this->instances[$offset]);
    }

}
