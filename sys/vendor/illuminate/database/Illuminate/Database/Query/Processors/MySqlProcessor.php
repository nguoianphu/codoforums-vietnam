<?php namespace Illuminate\Database\Query\Processors;

class MySqlProcessor extends Processor {

	/**
	 * Process the results of a column listing query.
	 *
	 * @param  array  $results
	 * @return array
	 */
	public function processColumnListing($results)
	{
		return array_map(function($r) { 
                    
                    if(!is_object($r)) {
                        
                        debug_print_backtrace();
                    }
                    
                    return $r->column_name;
                    
                    
                    
                }, $results);
	}

}
