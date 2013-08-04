<?php
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
   *
   * @return string json
   */
  public function getJSON($output) {

    $classname = $this->classname;
    $relationships = $this->relationships;
    $relationStack = array();
    $stack = array();
    $final = array();
    // Deals with a single record request of format: /garments/3
    $id = $this->request->allParams()['ID']; 
    // Deals with an array of records request of format: /garments?ids[]=1&ids[]=2
    $ids = $this->request->getVar('ids');

    if($ids) $results = $classname::get()->byIDs($ids);
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

              // Loop through each individul relationship relations (i.e $has_many => 'Images')
              foreach($row->$object() as $relation) {

                $relationStack[$class][] = $relation->ID;
              }
            }
          }
        }

        $final = (is_callable($output)) ? $output($row) : $this->prepareArray($row, $output);

        foreach($relationStack as $class => $ids) {
          $class = $this->sanitizeString($class . '_ids');
          $final[$class] = $ids;
        }

        // Reset relationStack for the next row
        $relationStack = array();

        // If we're requesting a single record we return a single item
        // array with the model name in singular as the json root
        if($id) $stack[$this->getSingularClassName()] = $final;
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
   * Very simply pluralization.
   * 
   * @return string pluralized class name
   */
  protected function getPluralClassName() {

    return $this->sanitizeString($this->classname . 's');
  }

  /**
   * Doesn't do anything at the moment. Just leaves the class name
   * as is (minus sanitizing). It's here to that we can easily do more
   * advanced 'singularization' if needed.
   *
   * @return string singularized class name
   */ 
  protected function getSingularClassName() {

    return $this->sanitizeString($this->classname);
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
