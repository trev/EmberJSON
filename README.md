# Silverstripe DataObject (+relationships) to Ember Data RESTAdapter JSON

This currently only deals with retrieving 1 or multiple records and its associations as an array of IDs.
It will output JSON expected by the DS.RESTAdapter. Check out [Ember Data Model Maker](http://andycrum.github.io/ember-data-model-maker/) to get a clear idea of what JSON this presenter will output.

## How to use
### Method A.
Pass the getJSON method an array of the properties you want outputed.

    class Post extends DataObject {
      public static $has_many = array(
        'Comments' => 'Comment'
      );
    }

    class PostController extends Page_Controller {

      public function index($request) {

        // Send the JSON headers
        $this->response->addHeader("Content-Type", "application/json");

        // Initialize EmberJSON
        $json = new EmberJSON('Post', $request, array('has_many'));

        // Get JSON
        return $json->getJSON(array(
          'id' => 'ID',
          'post' => 'post',
          'publishDate' => 'publishDate'
        ));
      }

    }

The array passed to getJSON will end up looking like this:

    array(
      'id' => $row->ID,
      'post' => $row->post,
      'publishDate' => $row->publishDate,
      'comments' => [ /* All associated comments */ ]
    ):

In short, the array key is what the property will be named in the JSON output, while the array value is the DataObject's actual proprty name.
Also note that the association key is automatically added because we told EmberJSON to include the `has_many` associations.


### Method B.
You can also pass the getJSON method a function where you do the array preparations yourself.

    class PostController extends Page_Controller {

      public function index($request) {

        // Send the JSON headers
        $this->response->addHeader("Content-Type", "application/json");

        // Initialize EmberJSON
        $json = new EmberJSON('Post', $request, array('has_many'));

        // Get JSON
        return $json->getJSON(function($row) {
          return array(
            'id' => $row->ID,
            'post' => $row->post,
            'avatar' => $row->avatar()->Filename,
            'publishDate' => $row->publishDate
          )}, ['comments']
        );
      }

    }

Notice that in this example we also pass a key to exclude from the JSON payload: `comments`. You could also not pass `array('has_many')` when creating a new EmberJSON instance and you'd get the same result but it would also not included ANY `has_many` relationships.

## Author
![Ron Burgundy](http://static.tumblr.com/769bb9c07595aebdeb73214592a0fd63/u9mcnar/1SVmhkwfk/tumblr_static_anchorman4.jpg)

## License
The MIT License (MIT)

Copyright (c) 2015 Trevor Wistaff

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
