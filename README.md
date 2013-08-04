# Silverstripe DataObject (+relationships) to Ember JS convention JSON

## How to use
### Method A.
Pass the getJSON method an array of the properties you want outputed.

    class PostController extends Page_Controller {

      public function index($request) {

        // Send the JSON headers
        $this->response->addHeader("Content-Type", "application/json");

        // Initialize EmberJSON
        $json = new EmberJSON('Post', $request, array('has_many', 'many_many'));

        // Get JSON
        return $json->getJSON(array(
          'id' => 'ID',
          'post' => 'post',
          'publish_date' => 'publishDate'
        ));
      }

    }

The array passed to getJSON will end up looking like this:

    array(
      'id' => $row->ID,
      'post' => $row->post,
      'publish_date' => $row->publishDate
    ):

So essentially, the array key is what the property will be named in the JSON output, while the array value is the DataObject's actual proprty name.

### Method B.
You can also pass the getJSON method a function where you do the array preparations yourself.

    class PostController extends Page_Controller {

      public function index($request) {

        // Send the JSON headers
        $this->response->addHeader("Content-Type", "application/json");

        // Initialize EmberJSON
        $json = new EmberJSON('Post', $request, array('has_many', 'many_many'));

        // Get JSON
        return $json->getJSON(function($row) {
          return array(
            'id' => $row->ID,
            'post' => $row->post,
            'avatar' => $row->avatar()->Filename,
            'publish_date' => $row->publishDate
          )}
        );
      }

    }

## Author
![Ron Burgundy](http://static.tumblr.com/769bb9c07595aebdeb73214592a0fd63/u9mcnar/1SVmhkwfk/tumblr_static_anchorman4.jpg)

## License
You can do whatever you want with it. It IS that simple.
