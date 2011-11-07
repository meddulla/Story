<?php

/**
 * Simple BDD for PHP
 *
 * Usage: see at the end of file
 *
 * PHP version 5.2 and 5.3
 *
 * @package    SimpleBDD
 * @author     Sofia Cardita <sofiacardita@gmail.com>
 */

require_once('Spec.php');


/**
 * Story Class
 *
 * @package    Story
 * @author     Sofia Cardita <sofiacardita@gmail.com>
 * @copyright  2010 Sofia Cardita
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link       pencilcode.com
 *
 * Todo
 * Run in console
 * Do not die on error eg base_path not defined
 * Output printer
 * Read steps from yaml files like cucumber
 * Integration with headless browser like
 * webrat/mink would be great. Another option
 * is simple test.
 */
class Story
{
    /* current value being evaluated */
    public $value = '';

    /* nr of failures */
    public $fails = 0;
    public $rules = '';
    public $failures = '';

    public function __construct()
    {
        $this->spec = new Spec();
        $this->set_map();
    }

    public function setBasePath($path)
    {
        $this->steps->base_path = $path;
    }

    public function set_map()
    {
        require_once 'steps/Steps.php';
        $this->steps = new Steps();
        //now get patterns from doc blocks in steps
        $steps_map = $this->get_patterns_from_docblocks($this->steps);
        $this->steps_map = $steps_map;

        //now get patterns for this instance
        $this->map = array();
        $steps_map = $this->get_patterns_from_docblocks($this);
        $this->map = $steps_map;

    }

    public function add_step($step)
    {
        $this->map[] = $step;
    }


    public function get_patterns_from_docblocks($class, $map = array())
    {
        $class_methods = get_class_methods($class);
        foreach ($class_methods as $method_name) {
            $method = new ReflectionMethod($class, $method_name);
            $pattern = $this->get_doc_comment($method->getDocComment(), '@StepMatches');
            if ($pattern) {
                $step['pattern'] = $pattern;
                $step['step']    = $method_name;
                $map[]           = $step;
            }
        }

        return $map;
    }


    protected function get_doc_comment($str, $tag = '')
    {
        if (empty($tag)) {
            return $str;
        }

        $matches = array();
        preg_match("/".$tag.":(.*)(\\r\\n|\\r|\\n)/U", $str, $matches);

        if (isset($matches[1])) {
            return trim($matches[1]);
        }

        return '';
    }



    public function get_map()
    {
        return $this->map;
    }

    public function get_step($matcher)
    {
        //is it in steps.php?
        if (isset($this->steps_map)) {
            foreach ($this->steps_map as $step){
                $match = preg_match($step['pattern'],$matcher,$matches);
                if($match){
                    return $this->steps->$step['step']($matches, $this) ;
                }

            }
        }

        //is it in Spec or child class?
        $arguments = explode(' ',$matcher);
        //remove the I (1st argument)
        array_shift($arguments);

        //by default, value is the last word
        $value = array_pop($arguments);


        //maybe method is in a child class?
        $method = implode('_',$arguments);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        $method = str_replace(array('should','not see', 'not_see' ,'see'),array('','has_not', 'has_not','has'),$method);
        if ($method[0] == '_') {
            $method = substr($method,1);
        }
        if(method_exists($this->spec, $method)){
            $this->spec->value($this->value)->$method($value);
            return $this;
        }

        foreach ($this->map as $step){
            $match = preg_match($step['pattern'],$matcher,$matches);

            if($match){
                return $this->$step['step']($matches) ;
            }
        }

        //no match
        echo PHP_EOL.'no step defined. Go define it: '.$matcher.PHP_EOL;
    }

    function Given($matcher)
    {
        $this->score();
        return $this->get_step($matcher);
    }

    public function Then($matcher)
    {
        $this->get_step($matcher);
        $this->score();
        return $this;
    }

    public function Also($matcher)
    {
        $this->get_step($matcher);
        $this->score();
        return $this;
    }

    //alias for Also
    public function Andd($matcher)
    {
        return $this->Also($matcher);
    }


    //alias for Also
    public function Likewise($matcher)
    {
        return $this->Also($matcher);
    }


    public function score()
    {
        $this->fails = $this->fails + $this->spec->fails;
        $this->rules = implode(' '.PHP_EOL,$this->spec->rules);
        $this->failures = implode(' '.PHP_EOL,$this->spec->failures);

        //reset spec
        $this->spec->fails = 0;
        $this->spec->rules = array();
        $this->spec->failures = array();
    }

    public function results()
    {
        $results['errors'] = $this->fails;
        $results['failed_rules'] = $this->rules;
        $results['error_messages'] = $this->failures;

        return $results;
    }

    public function reset()
    {
        $this->fails = 0;
        $this->failures = '';
    }



}


//USAGE
if (!debug_backtrace()) {

    ini_set('display_errors',1);
    error_reporting(E_ALL);

    //1. define your own steps
    //by creating a class that
    //extends Story
    class MY_Story extends Story
    {
        //set base path of website
        //if you're planning to
        //test webpages by curling them
        public $base_path = 'http://localhost:8888/idh/pt/';

        public function __construct()
        {
            parent::__construct();
            $this->steps->base_path = $this->base_path;
        }


        /**
         * step_insert_fixture function.
         *
         * @StepMatches: /^I have (.+) (.+)$/
         *
         * @param array $matches
         *
         * @access public
         * @return string
         */
        public function step_insert_fixture($matches)
        {
            $value = $matches[1];
            $class = $matches[2];

            $list[] = array('title' => 'Helld', 'id'=>1);
            $list[] = array('title' => 'Bye','id' => 2);

            $this->value = $list;
            return $this;
        }

    }



    //2. run them
    $story = new MY_Story();
    $story->Given('I am on the articles')
        ->Then('I should see Home')
        ->Also('I should not see error');
    print_r($story->results());

    $story = new MY_Story();
    $story->Given('I have 2 articles')
        ->Then('They should have a definition for title')
        ->Also('Item 0 title should contain Hello')
        ->Also('Item 1 title should contain Bye');
    print_r($story->results());

    //multiple Given steps
    $story = new MY_Story();
    $story->Given('I am on the articles')
        ->Then('I should see Home')
        ->Also('I should not see error')
        ->Given('I am on the pdfs')
        ->Then('I should not see error')
        ->Also('I should see teste');
    print_r($story->results());


    $story = new MY_Story();
    $story->Given('I have a file at /Applications/MAMP/htdocs/Bdd/roadmap.tasks')
        ->Then('I should see git');
    print_r($story->results());

}
