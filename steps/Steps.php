<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'deps/phpQuery/');

require_once('deps/phpQuery/Zend/Http/Client.php');

class Steps
{
    var $base_path;
    /**
     * step_i_am_on_the_page function.
     *
     * Curls page by url set in Child Class
     * of Story (base_path).
     *
     * Usage: Given('I am on the articles')
     * would fetch articles page relative to
     * the base_path, eg. example.com/articles.
     *
     * @StepMatches: /^I am on(?: the)* (.+)$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_i_am_on_the_page($matches,$story)
    {
        $page = $matches[1];
        $url  = (strpos($page,'http:')!==FALSE ? '' : $this->base_path) . $page;

        if (!isset($this->client)) {
            $this->client = new Zend_Http_Client();

            // To turn cookie stickiness on, set a Cookie Jar
            $this->client->setCookieJar();
        }
        $this->client->setConfig(array('useragent' => 'TestToast'));

        $this->client->setUri($url);
        $response = $this->client->request('GET');

        $story->value = $response->getBody();
        return $story;


    }

    /**
     * step_i_am_on_the_page_as_agent function.
     *
     * Curls page as the given agent.
     *
     * Usage: Given('I, as agent "", am on the articles')
     *
     * @StepMatches: /^I, as agent "(.+)", am on(?: the)* (.+)$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_i_am_on_the_page_as_agent($matches,$story)
    {
        $page = $matches[2];
        $url  = (strpos($page,'http:')!==FALSE ? '':$this->base_path) . $page;
        $user_agent = $matches[1];
        set_include_path(get_include_path() . PATH_SEPARATOR . 'deps/phpQuery/');
        if (!isset($this->client)) {
            $this->client = new Zend_Http_Client();

            // To turn cookie stickiness on, set a Cookie Jar
            $this->client->setCookieJar();
        }
        $this->client->setConfig(array('useragent' => $user_agent));

        $this->client->setUri($url);
        $response = $this->client->request('GET');

        $story->value = $response->getBody();


        return $story;
    }

    /**
     * step_item_array function.
     *
     * @StepMatches: /^Variable "(.+)" as json$/
     *
     * Usage: Given('Variable "'.json_encode($myarray).'" as json')
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_item_array($matches,$story)
    {
        $story->value = json_decode($matches[1],true);
        return $story;
    }


    /**
     * step_item function.
     *
     * @StepMatches: /^Variable (.+)$/
     *
     * Usage: Given('Variable john has a big house')
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_item($matches,$story)
    {
        $story->value = $matches[1];
        return $story;
    }


    /**
     * step_isset_in_list function.
     *
     * Usage: Given('A step that sets an assoc array in value')
     *          Then('They should have a definition for title')
     *
     * @StepMatches: /^They should have a definition for (.+)$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_isset_in_list($matches,$story)
    {
        $key = $matches[1];
        foreach ($story->value as $val){
            $story->spec->value($val)->has_definition($matches[1]);
        }
        return $story;
    }

    /**
     * step_item_property_contains function.
     *
     * Usage: Given('A step that sets an assoc array in value')
     *          Then('Item 1 title should contain John')

     * @StepMatches: /^Item (.*) (.*) should contain (.+)$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_item_property_contains($matches,$story)
    {
        $nr    = $matches[1];
        $prop  = $matches[2];
        $value = $matches[3];
        $story->spec->value($story->value[$nr][$prop])->contains($value);
        return $story;
    }

    /**
     * step_load_file function.
     *
     * @StepMatches: /^I have a file at (.+)$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_load_file($matches, $story)
    {
        $file = $matches[1];
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $story->value = $content;
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,"File does not exist: ".$file);
        }
        return $story;
    }

    /**
     * step_file_should_exist function.
     *
     * @StepMatches: /^file "([^"]*)" should exist$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function step_file_should_exist($matches, $story)
    {
        if (file_exists($story->value)) {
            $story->spec->passed(TRUE,__FUNCTION__,$story->value);
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,$story->value);
        }

        return $story;
    }


    /**
     * should_see_n_times_in_selector function.
     *
     * @StepMatches: /^I should see "([^\"]*)" "(.+)" times in "(.+)" selector$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function should_see_n_times_in_selector($matches,$story)
    {
        $str = $matches[1];
        $nr  = $matches[2];
        $selector = $matches[3];

        require_once('deps/phpQuery.php');
        phpQuery::newDocument($story->value);
        $container= pq($selector);
        $html = $container->html();
        $n_times = substr_count($html, $str);

        if ((int)$n_times == (int)$nr) {
            $story->spec->passed(TRUE,__FUNCTION__,$html);
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,$html);
        }

        return $story;
    }

    /**
     * should_see_in_selector function.
     *
     * @StepMatches: /^I should see "([^\"]*)" in "(.+)" selector$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function should_see_in_selector($matches,$story)
    {
        $str = $matches[1];
        $selector = $matches[2];

        require_once('deps/phpQuery.php');
        phpQuery::newDocument($story->value);
        $container= pq($selector);
        $html = $container->html();

        if (strpos($html, $str) !== FALSE) {
            $story->spec->passed(TRUE,__FUNCTION__,$html);
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,$html);
        }

        return $story;
    }

    /**
     * should_not_see_in_selector function.
     *
     * @StepMatches: /^I should not see "([^\"]*)" in "(.+)" selector$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function should_not_see_in_selector($matches,$story)
    {
        $str = $matches[1];
        $selector = $matches[2];

        require_once('deps/phpQuery.php');
        phpQuery::newDocument($story->value);
        $container= pq($selector);
        $html = $container->html();

        if (strpos($html, $str) === FALSE) {
            $story->spec->passed(TRUE,__FUNCTION__,$html);
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,$html);
        }

        return $story;
    }




    /**
     * should_see_n_times function.
     *
     * @StepMatches: /^I should see "([^\"]*)" "(.+)" times$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function should_see_n_times($matches,$story)
    {
        $str = $matches[1];
        $nr  = $matches[2];

        $n_times = substr_count($story->value, $str);

        if ((int)$n_times == (int)$nr) {
            $story->spec->passed(TRUE,__FUNCTION__,$story->value);
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,$story->value);
        }

        return $story;
    }




    /**
     * should_see function.
     *
     * Check for multiple words in
     * string enclosed by "". By
     * default Story sets the value
     * to be the single last word
     * (ie multiple words not allowed).
     * so we need this method.
     *
     * @StepMatches: /^I should see "([^\"]*)"$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function should_see($matches, $story)
    {
        $story->spec->value($story->value)->has($matches[1]);
        return $story;
    }

    /**
     * should_not_see function.
     *
     * Check for multiple words in
     * string enclosed by "". By
     * default Story sets the value
     * to be the single last word
     * (ie multiple words not allowed).
     * so we need this method.
     *
     * @StepMatches: /^I should not see "([^\"]*)"$/
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function should_not_see($matches, $story)
    {
        $story->spec->value($story->value)->has_not($matches[1]);
        return $story;
    }


    /**
     * post_item_to_page function.
     *
     * @StepMatches: /^I (post|get) to (.+) as json: "(.+)"$/
     *
     * @param array  $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function post_item_to_page($matches, $story)
    {
        if (!isset($this->base_path)) {
            echo('To call i am on the page you must set the base path first. Do this $this->path = "http://www.example.com"');
        }
        $method = strtoupper($matches[1]);

        $url  = (strpos($page,'http:')!==FALSE ? '':$this->base_path) . $matches['2'];
        $item  = json_decode($matches[3],true);
        $res = $this->rest_helper($url, $item,$method,'text');
        $story->value = $res;
        return $story;

    }

    /**
     * ajax_post_item_to_page function.
     *
     * @StepMatches: /^I ajax (post|get) to (.+) as json: "(.+)"$/
     *
     * @param array  $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function ajax_post_item_to_page($matches, $story)
    {
        if (!isset($this->base_path)) {
            echo('To call i am on the page you must set the base path first. Do this $this->path = "http://www.example.com"');
        }
        $method = strtoupper($matches[1]);
        $url  = (strpos($page,'http:')!==FALSE ? '':$this->base_path) . $matches['2'];
        $item  = json_decode($matches[3],true);
        $res = $this->rest_helper($url, $item,$method,'text',true);
        $story->value = $res;
        return $story;
    }

    /**
     * post_to_as_user_pass function.
     *
     * @StepMatches: /^I post to "(.+)" as user "(.+)" and pass "(.+)"$/
     *
     * @param array  $matches
     * @param object $story
     *
     * @access public
     * @return object
     */
    public function post_to_as_user_pass($matches, $story)
    {
        $url  = $matches[1];
        $user = $matches[2];
        $pass = $matches[3];


        $client = new Zend_Http_Client();

        $client->setConfig(array(
            'useragent' => 'TestToast')
            );

        // To turn cookie stickiness on, set a Cookie Jar
        $client->setCookieJar();

        // First request: log in and start a session
        $url  = (strpos($page,'http:')!==FALSE ? '':$this->base_path) . $url;
        $client->setUri($url);
        $client->setParameterPost('user', $user);
        $client->setParameterPost('password', $pass);
        $client->request('POST');

        //set client
        $this->client = $client;
        return $story;

        // The Cookie Jar automatically stores the cookies set
        // in the response, like a session ID cookie.

        // Now we can send our next request - the stored cookies
        // will be automatically sent.
        //$client->setUri('http://localhost:8888/economico/admin/welcome');
        //$response = $client->request('GET');
        //echo $response->getBody();

    }


    /**
     * rest_helper  function.
     *
     * By wezfurlong. See
     * http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
     *
     * @param string $url
     * @param array  $params
     * @param string $verb   (default GET)
     * @param string $format (default json)
     * @param boolean $ajax (default false)
     * @param string $header (default:  "Content-Type: multipart/form-data\r\n")
     *
     * @access public
     * @return mixed (string or class)
     */
    public function rest_helper($url, $params = null, $verb = 'GET', $format = 'json', $ajax = false, $header = null )
    {
      if (is_null($header)) {
          $header =  "Content-Type: application/x-www-form-urlencoded\r\n";
      }

      $cparams = array(
        'http' => array(
          'method' => $verb,
          'ignore_errors' => true,
          'header' => $header
        )
      );
      if ($params !== null) {
        $params = http_build_query($params);
        if ($verb == 'POST') {
            $cparams['http']['content'] = $params;
        } else {
          $url .= '?' . $params;
        }
      }

      if ($ajax) {
          $cparams['http']['header'] = $cparams['http']['header'] ." HTTP_X_REQUESTED_WITH: xmlhttprequest\r\n";
      }

      $context = stream_context_create($cparams);
      $fp = fopen($url, 'rb', false, $context);
      if (!$fp) {
        $res = false;
      } else {
        // If you're trying to troubleshoot problems, try uncommenting the
        // next two lines; it will show you the HTTP response headers across
        // all the redirects:
        // $meta = stream_get_meta_data($fp);
        // var_dump($meta['wrapper_data']);
        $res = stream_get_contents($fp);
      }

      if ($res === false) {
        throw new Exception("$verb $url failed: $php_errormsg");
      }

      switch ($format) {
        case 'json':
          $r = json_decode($res);
          if ($r === null) {
            throw new Exception("failed to decode $res as json");
          }
          return $r;

        case 'xml':
          $r = simplexml_load_string($res);
          if ($r === null) {
            throw new Exception("failed to decode $res as xml");
          }
          return $r;
      }
      return $res;
    }


}
