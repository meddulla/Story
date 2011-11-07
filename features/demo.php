<?php
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
     * @Tags: str
     *
     * @access public
     * @return string
     */
    public function scenario_home_should_not_have_john()
    {
        $this->Given('Variable Home is where the heart is')
        ->Then('I should not see John')
        ->Also('I should see Home')
        ->Also('I should not see error');
    }


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

    /**
     * scenario_header_Nytimes_Politics function.
     *
     * @Tags: wip
     *
     * @access public
     * @return string
     */
    public function scenario_header_Nytimes_Politics()
    {
        $this->setBasePath('http://www.nytimes.com/');
        $this->Given('I am on the politics')
        ->Then('I should see Politics')
        ->Also('I should see "Politics" in "h2" selector');
    }


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
        ->Also('I should see search')
        ->Also('I should not see Bing')

        //Now we test this html snippet present in the Google homepage
        //<div id="fll">
        //  <a href="/intl/pt-PT/about.html">Tudo sobre o Google</a>
        //</div>
        //using this regex in the step:
        // /^I should see "([^\"]*)" in "(.+)" selector$/
        ->Also('I should see "about.html" in "#fll" selector');

        //the value under test is always accessible in
        //$this->value. In this case, it's the
        //html of the page fetched
        //so you could echo $this->value;

    }


}

