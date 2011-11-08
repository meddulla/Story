##  INTRODUCTION

Story is a simple BDD tool for PHP 5.2 and above. 

##  GET STARTED 

Download the zip/tar file and extract it. You'll have the following directory structure:

    Story/
        deps/ - external dependencies, eg phpQuery
        steps/ - where steps are defined
           Steps.php
        Spec.php
        Story.php
        StoryRunner.php
        features/ - where your features/tests are stored

Now create a feature in the folder features, eg. demo.php

    require_once 'Story.php';
    class demo extends Story
    {
        public function __construct()
        {
            parent::__construct();
        }


        /**
         * scenario_home_should_not_have_john function.
         *
         * @Tags: wip
         *
         * @access public
         * @return string
         */
        public function scenario_home_should_not_have_john()
        {
            $this->Given('Variable Home is where the heart is')
            ->Then('I should not see John')
            ->Also('I should not see error');
        }

    }


Now run it in browser: http://localhost/mysite/Story/StoryRunner.php?path=features/demo.php
or in console: $ php StoryRunner.php -p features/demo.php

You should see

    Tests Failed: 0
    Tests Passed: 1
    Total Tests: 1


That's a really simple test asking if the string "Home is where the heart is" contains Home, and does not contain John or error - if these 3 conditions are true the test passes.

But you can also work with arrays:


    /**
     * scenario_books_contain_correct_authors function.
     *
     * @Tags: arrays
     *
     * @access public
     * @return string
     */
    public function scenario_books_contain_correct_authors()
    {
        //we set it here for simplicity but 
        //you could of course fetch it from the db
        $books[] = array('title' => 'Romeu and Juliet','author' => 'William Shakespeare');
        $books[] = array('title' => 'Code, the hidden language', 'author' => 'Charles Petzold');
        $books[] = array('title' => 'Artificial Intelligence', 'author' => 'Peter Norvig');
        $books[] = array('title' => 'Man without Qualities', 'author' => 'Robert Musil');
        
        //uncomment below and the test will fail
        //because it has no definition for title
        //$books[] = array('author' => 'self.reddit');

        $this->Given('Variable "'.json_encode($books).'" as json')
        ->Then('They should have a definition for title')
        ->Then('Item 0 author should contain Shakespeare')
        ->Given('Variable "'.json_encode($books[1]['author']).'"')
        ->Then('I should see "Charles Petzold"');
    }


You can also work with webpages and css selectors. 
To demo this create a test that fetches Google's webpage and checks for some text:


    /**
     * scenario_Google function.
     *
     * @Tags: wip
     *
     * @access public
     * @return string
     */
    public function scenario_Google()
    {
        $this->Given('I am on the http://www.google.com')
        ->Then('I should see Google')
        ->Likewise('I should see search') //Also, Likewise and Andd are all equivalent
        ->Also('I should not see Bing')

        //Testing this html snippet present in the Google homepage
        //<div id="fll">
        //  <a href="/intl/pt-PT/about.html">Tudo sobre o Google</a>
        //</div>
        ->Also('I should see "about.html" in "#fll" selector');

    }
 

Here we're checking not only for presence text but also for presence of text inside a css selector (basically the same as JQuery's $("#fll")->html()).

The value under test is always in the $this->value variable, so you could echo the html of the page by doing echo $this->value.

You could also set your base path and all paths would be relative to that. For example:

 
    /**
    * test_header_Nytimes_Politics function.
    *
    * @Tags: wip
    *
    * @access public
    * @return string
    */
    public function scenario_header_Nytimes_Politics()
    {
        $this->setBasePath( 'http://www.nytimes.com/');
        $this->Given('I am on the politics')
        ->Then('I should see Politics')
        ->Also('I should see "Politics" in "h2" selector');
    }
 
    
The use of the base_path in constructing the url to be fetched depends on the presence or absence of http 
in the web page address. 
So even after setting the base_path you could fetch other sites, as long as you put the absolute url, 
http://www.google.com, not www.google.com.

##  RUNNING TESTS 

####Run from the console 

Run all scenarios in scenarios with Tag wip (in the docblock, it's the @Tags: wip line)

    $ php StoryRunner.php -t wip

Run all features in given Path or directory

    $ php StoryRunner.php -p mytests/

Run a single feature in given Path

    $ php StoryRunner.php -p mytests/demo.php

Rerun only failed scenarios in last run

    $ php StoryRunner.php -r 

#### Run from the browser 

Run all tests in features: 

    http://localhost/mysite/Story/StoryRunner.php

Run all scenarios in features of a given tag: 

    http://localhost/mysite/Story/StoryRunner.php?tag=wip

Run all features in given path: 

    http://localhost/mysite/Story/StoryRunner.php?path=mytests/

Run only last failed scenarios: 

    http://localhost/mysite/Story/StoryRunner.php?rerun=1/

Run a single feature: 

    http://localhost/mysite/Story/StoryRunner.php?path=features/demo.php


Failed scenarios are stored in csv format in the Story/.rerun file. 
Then when in rerun mode, Story reads that file and only runs the scenarios in that file.



## Available Steps 

List of already available steps (those in
Steps.php and Spec):

    @StepMatches: /^I am on(?: the)* (.+)$/
        Fetches given webpage, eg. 
            $this->Given('I am on the http://www.google.com')
        If you set the base path, you can use relative urls
            $this->base_path = 'http://mysite.com/';
            $this->Given('I am on the articles')
            would fetch http:/mysite.com/articles

    @StepMatches: /^I, as agent "(.+)", am on(?: the)* (.+)$/
    @StepMatches: /^Variable "(.+)" as json$/
    @StepMatches: /^Variable (.+)$/
    @StepMatches: /^They should have a definition for (.+)$/
    @StepMatches: /^Item (.*) (.*) should contain (.+)$/
    @StepMatches: /^I have a file at (.+)$/
    @StepMatches: /^file "([^"]*)" should exist$/
    @StepMatches: /^I should see "([^\"]*)" "(.+)" times in "(.+)" selector$/
    @StepMatches: /^I should see "([^\"]*)" in "(.+)" selector$/
    @StepMatches: /^I should not see "([^\"]*)" in "(.+)" selector$/
    @StepMatches: /^I should see "([^\"]*)" "(.+)" times$/
    @StepMatches: /^I should see "([^\"]*)"$/
    @StepMatches: /^I should not see "([^\"]*)"$/
    @StepMatches: /^I (post|get) to (.+) as json: "(.+)"$/
    @StepMatches: /^I ajax (post|get) to (.+) as json: "(.+)"$/
    @StepMatches: /^I post to "(.+)" as user "(.+)" and pass "(.+)"$/



## Define your steps 

You can add your steps in 2 places: steps/Steps.php or in a new file that
extends Story. See below.

Option 1: 
In steps/Steps.php add your step and in the docblock define its matcher.

Eg.


    /**
     * step_txt_should_exist function.
     *
     * @StepMatches: /^txt "([^"]*)" should exist$/
     * @Tags: wip,demo
     *
     * Usage: Then('txt "README" should exist')
     *
     * @param array $matches
     * @param object $story
     *
     * @access public
     * @return string
     */
    public function step_txt_should_exist($matches, $story)
    {
        if (file_exists($story->value.'.txt')) {
            $story->spec->passed(TRUE,__FUNCTION__,$story->value);
        }else{
            $story->spec->passed(FALSE,__FUNCTION__,$story->value);
        }

        return $story;
    }

Notice the @StepMatches regex in the docblock? 
That's what makes it match to $this->Then('txt "README" should exist').

Each step receives the matches and an instance of the story
object which it should return. Here you can put all your commonly
used steps.

Now use your step in demo.php

    /**
    * scenario_txt function.
    *
    * @Tags: wip
    *
    * @access public
    * @return string
    */
    public function scenario_txt()
    {
        $this->Given('I am on the politics')// example
        ->Then('txt "politics" should exist');
    }


Option 2: The second place you can add steps is the child class of Story,
for example:

    class App_Story extends Story
    {
        /**
         * step_check_db_value function.
         *
         * @StepMatches: /^I should have a (.+) with (.+) (.+)$/
         *
         * Usage: Then('I should have a article with author arthur')
         *
         * @param array $matches
         *
         * @access public
         * @return object
         */
        public function step_check_db_value($matches)
        {
            $table = $matches['1'];//article table in db
            $field = $matches['2'];//author
            $value = $matches['3'];//arthur

            $ci = &get_instance();
            $sql = 'SELECT * FROM '.$table.' WHERE '.$field. '=?';
            //PSEUDO db code, change to match your framework
            $result = $db->query($sql,array($value))->result_array();

            if (isset($result[0])) {
                //signal a step as passed
                $this->spec->value(true)->equals(true);
            }else{
                //does not exist in db: signal the step as failed
                //send the sentence itself so it helps in
                //identiying the failure, eg 
                //I should have a article with title arthur
                $this->spec->value($matches[0])->is('true');
            }

            return $this;
        }

    }
    
    
Notice how you don't need the $story object and simply return $this.

In your feature file, eg. features/demo.php you simply extend from Story_App and not from Story.

So this:
 
    require_once 'Story.php';
    class demo extends Story
 

becomes this:

 
    require_once 'Story.php';
    require_once 'Story_App.php';
    class demo extends Story_App
 
So finishing up, you end up with the code below: 
    
    require_once 'Story.php';
    require_once 'Story_App.php';

    class demo extends Story_App 
    {
        /**
         * John has post function.
         *
         * @Tags: posts
         *
         * @access public
         * @return string
         */
        public function john_has_post()
        {
            $this->Given('I am on the page articles') 
            ->Then('I should see arthur')
            ->Also('I should have a article with author arthur')
            ->Also('I should not see error');
        }

    }
    
 
    

## Use Story Without StoryRunner

#### Example Code Igniter Integration 

You can also use Story without using StoryRunner. 
Here is a very simple example using the Toast unit test library in Code Igniter. This
way you can simply mix and match the various testing methods at your will.

First, put the Story folder in libraries, so you end up with 

application/libraries/Story/

Create a test in controllers/test/demo_tests.php. Put the code below in it.
Notice the _pre and _post methods - these send the test results to toast. 

We could probably integrate StoryRunner itself in CI but this serves the purpose
of showing how easily you can use Story right now in your projects, whether you use
Code Igniter, Symfony, CakePHP or any other framework.

 
    require_once(APPPATH . '/libraries/Story.php');
    class demo_tests extends Toast
    {
        function __construct()
        {
            parent::Toast(__FILE__);
            // Load any models, libraries etc. you need here
        }

        public function _pre()
        {
            //reset story
            $this->story = null;
            $this->story = new Story_App();
            $this->story->setBasePath('http://www.mysite.com/');//with trailing slash

        }

        function _post()
        {
            //send story errors to toast
            if($this->story->spec->fails > 0){
                $this->asserts = FALSE;
                //collect failure messages
                $this->_fail(implode(', ',$this->story->spec->failures));
            }
        }
        
        //bdd style test
        public function test_posts_webpage_does_display_errors()
        {
           $this->story->Given('I am on the posts/')
                        ->Then('I should not see "PHP Error"');
        }
        
        //better readability of unit test
        //by using Spec library in Story
        public function test_header_en_has_english_items()
        {
            $this->lang->set('en');
            $html_header = $this->load->view('default/frontend/header','',true);
            $this->story->spec->value($html_header)
                ->contains('Videos')
                ->contains('Welcome')
                ->does_not_contain('error');
        }

        //typical toast style
        public function test_get_menus_highlights()
        {
            $menus = $this->amodel->getMenus();
            $this->_assert_contains('home',$menus);
            $this->_assert_contains('Contacts',$menus);
            $this->_assert_not_contains('error',$menus);
        }



    }
