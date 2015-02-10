<?php
/**
 * Silverstripe JSON Request Decorator
 *
 * This class helps you convert your SilverStripe Data Objects into a
 * JSON standard that Ember Data can interpret right out of the box
 *
 * Last tested with: Ember 1.10, Ember Data 1.0.0-beta.14.1 and
 * SilverStripe: Framework 3.1.9, CMS 3.0.5
 *
 * @package     EmberJSON
 * @author      Trevor Wistaff <trev@a07.com.au>
 * @license     Scotch, scotch, scotch, I love scotch
 * @version     1.0.0
 * @link        https://github.com/trev/EmberJSON
 */
class EmberJSON {

  protected $classname = '';

  protected $relationships;

  protected $request;

  /**
   * Constructor
   *
   * @param string $classname The DataObject class name
   * @param object $request The HTTP_Request object
   * @param array $relationships The relationships you want included in the output
   */
  public function __construct(string $classname, $request, array $relationships = array()) {

    $this->classname = $classname;
    $this->relationships = $relationships;
    $this->request = $request;
  }

  /**
   * Get's a JSON representation of all the model data and it's requested relationships.
   *
   * @param array|function $output Associated array or function representing the JSON output format
   * @param array $keys_to_exclude Exclude keys from delivered JSON payload
   *
   * @return string json
   */
  public function getJSON($output, $keys_to_exclude = array()) {

    $classname = $this->classname;
    $relationships = $this->relationships;
    $relationStack = array();
    $stack = array();
    $final = array();
    $filter = array();
    // Deals with a single record request of format: /posts/3
    $id = $this->request->allParams()['ID']; 
    // Deals with an array of records request of format: /posts?ids[]=1&ids[]=2
    $ids = $this->request->getVar('ids');
    // Deals with filtered requests
    $gets = $this->request->getVars();
    foreach($gets as $param => $value) {

      if($param != 'url' && $param != 'ids') $filter[$param] = $value;
    }

    if($ids) $results = $classname::get()->byIDs($ids);
    elseif(count($filter)) $results = $classname::get()->filter($filter);
    elseif($id) $results = $classname::get()->byID($id);
    else $results = $classname::get();

    if($results) {
      // Loop through the results
      foreach($results as $row) {

        // Loop through relationships (i.e. has_many, many_many)
        foreach($relationships as $relationship) {

          // Check to see if relationship actually exists before looping through it
          if($classname::${$relationship}) {

            // Loop through every relationship entry (i.e has_many)
            foreach($classname::${$relationship} as $object => $class) {

              // Loop through each individual relationship relations (i.e $has_many => 'Images')
              foreach($row->$object() as $relation) {
                
                if($relationship === 'has_one') 
                  $relationStack[$class] = $relation->ID;
                else
                $relationStack[$class][] = $relation->ID;
              }
            }
          }
        }

        $final = (is_callable($output)) ? $output($row) : $this->prepareArray($row, $output);

        foreach($relationStack as $class => $ids) {
          $class = $this->sanitizeString($class . (is_array($ids) ? '_ids' : '_id'));
          $final[$class] = $ids;
        }

        foreach($keys_to_exclude as $key) {
          unset($final[$key]);
        }

        // Reset relationStack for the next row
        $relationStack = array();

        // If we're requesting a single record we return a single item
        // array with the model name in singular as the json root
        if($id) $stack[lcfirst($this->classname)] = $final;
        // If we're requesting multiple records we return multiple items
        // in a nested array with the model name in its pluralized form as the json root
        else $stack[$this->getPluralClassName()][] = $final;
      }
    }

    return json_encode($stack);
  }

  /**
   * Prepares the array format to be json encoded.
   *
   * @return array associative
   */
  protected function prepareArray($row, array $output) {

    foreach($output as $k => $v) {
      $output[$k] = $row->$v;
    }

    return $output;
  }

  /**
   * Very simple pluralization.
   * 
   * @return string pluralized class name
   */
  protected function getPluralClassName() {

    $classname = $this->classname;

    if(substr($this->classname, -1) === 'y')
      $classname = substr($classname, 0, -1) . 'ie';

    return lcfirst($classname . 's');
  }

  /**
   * Take a camel cased string and underscores
   *
   * return string underscored
   */
  protected function sanitizeString($str) {

    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
  }
}
