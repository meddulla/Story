<?php

require_once('Story.php');
class Story_Runner
{
    var $rerun_file = '.rerun';

    public function run()
    {
        $env = $this->detect_environment();
        $args = $this->get_arguments($env);

        if (is_file($args['path'])) {
            // its a file
            $features[0] = $args['path'];
        }elseif(is_dir($args['path'])){
            //get all files in path of given extension
            $features = glob($args['path'].'*.'.$args['extension']);
        }


        if (empty($features)) {
            $this->error('ERROR: No description files found. ');
            exit;
        }

        $this->tests_failed = 0;
        $this->tests_passed = 0;
        if ($args['extension'] == 'yaml') {
            // @todo...
        }else{
            $results = $this->_run_php_features($features,$args);
        }
        $this->display_results($results);
    }

    protected function error($msg)
    {
        echo $msg;
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



    protected function _run_php_features($features,$args)
    {
        $story_methods  = get_class_methods('Story');
        $results = array();
        $fails = 0;

        if($args['rerun'] == 1) {
            $torun = $this->load_for_rerun();
            $this->clear_failed_for_rerun();//reset for current run
        }


        foreach($features as $feature) {
            require_once($feature);
            $class = basename($feature);//in case it's a file
            $class = str_replace($args['path'],'',$class);
            $class = str_replace('.'.$args['extension'],'',$class);
            $story = new $class();

            $methods = get_class_methods($class);
            $parent_class = get_parent_class($class);
            if ($parent_class != 'Story') {
                //feature has a custom parent
                //get methods of parent and story and
                //merge them
                $parent_methods = get_class_methods($parent_class);
                $parent_methods = array_merge($story_methods,$parent_methods);
            }else{
                $parent_methods = $story_methods;
            }

            foreach ($methods as $test){
                if (in_array($test,$parent_methods)) {
                    continue;
                }

                if (!empty($args['tag'])) {
                    $method = new ReflectionMethod($class, $test);
                    $tags = $this->get_doc_comment($method->getDocComment(), '@Tags');
                    if (!empty($tags)) {
                        $tags = explode(',',$tags);
                        if (!in_array($args['tag'],$tags)) {
                            //skip test
                            //doesnt belong to tag
                            continue;
                        }
                    }else{
                        continue;
                    }

                } elseif(isset($torun)) {
                    if(!isset($torun[$class])){
                        continue;
                    } elseif(isset($torun[$class]) && !in_array($test, $torun[$class]) ){
                        continue;
                    }
                }
                $story->$test();
                $fails += $story->fails;
                if ($story->fails > 0) {
                    $this->tests_failed ++;
                    $this->save_failed($class, $test);
                }else{
                    $this->tests_passed ++;
                }
                $results[$class][$test] = $story->results();
                $story->reset();
            }
        }
        $this->clear_failed_for_rerun();
        return $results;

    }

    public function load_for_rerun()
    {
        $row = 1;
        $list = array();
        if (($handle = fopen($this->rerun_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    if($c < 1 && !isset($list[$data[$c]])) {
                        $feature = $data[$c];
                        $list[$feature] = array();
                    } else {
                        if($data[$c] !== $feature) {
                            $list[$feature][] = $data[$c];
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        return $list;

    }

    public function save_failed($feature, $scenario)
    {
        if(is_writable($this->rerun_file)){
            file_put_contents($this->rerun_file,$feature.','.$scenario."\n", FILE_APPEND);
        }
    }

    public function clear_failed_for_rerun()
    {
        if($this->tests_failed === 0){
            file_put_contents($this->rerun_file,'');
        }
    }

    public function display_results($results)
    {
        if ($this->detect_environment()== 'cli') {
            if ($this->tests_failed > 0) {
                echo "\033[0;31mTests Failed: ".$this->tests_failed."\033[0m";
                echo "\n";
                foreach($results as $file => $feature) {
                    foreach($feature as $name => $test) {
                        if($test['errors'] > 0) {
                            echo "\033[0;31m- Failed feature\033[0m: ". $file.'->'.$name .' - '. $test['failed_rules']. " \n";
                        }
                    }
                }

            }else{
                echo "\033[0;32mTests Failed: ".$this->tests_failed."\033[0m";
            }
            echo "\n";
            echo "\033[0;32mTests Passed: ".$this->tests_passed."\033[0m";
            echo "\n";
            $total_tests = $this->tests_failed + $this->tests_passed;
            echo "Total Tests: ".$total_tests;
            echo "\n";
        }else{
            if ($this->tests_failed > 0) {
                echo "<div style='color:red'>Tests Failed: ".$this->tests_failed."</div>";
                foreach($results as $file => $feature) {
                    foreach($feature as $name => $test) {
                        if($test['errors'] > 0) {
                            echo '<span style="color:red">&nbsp; - Failed feature</span>: '. $file.'->'.$name .' ('. $test['failed_rules']. ')<br/>';
                        }
                    }
                }

            }else{
                echo "<div style='color:green'>Tests Failed: ".$this->tests_failed."</div>";
            }
            echo "<div style='color:green'>Tests Passed: ".$this->tests_passed."</div>";
            $total_tests = $this->tests_failed + $this->tests_passed;
            echo "Total Tests: ".$total_tests;
        }

    }

    public function get_arguments($env)
    {
        if ($env == 'cli') {
            $arguments = getopt("p:e:t:r");
            $arguments['rerun'] = isset($arguments['r'])? 1 : 0;
            $arguments['tag'] = isset($arguments['t'])? $arguments['t'] : '';
            $arguments['path'] = isset($arguments['p'])? $arguments['p'] : '';
            $arguments['extension'] = isset($arguments['e'])? $arguments['e'] : '';

        }else{
            $arguments['rerun'] = isset($_GET['rerun'])? 1 : 0;
            $arguments['tag'] = isset($_GET['tag'])? $_GET['tag'] : '';
            $arguments['path'] = isset($_GET['path'])? $_GET['path'] : '';
            $arguments['extension'] = isset($_GET['extension'])? $_GET['extension'] : '';
        }
        if (empty($arguments['extension'])) {
            $arguments['extension'] = 'php';
        }
        if (empty($arguments['path'])) {
            $arguments['path'] = 'features/';
        }
        return $arguments;
    }

    public function detect_environment()
    {
        if (php_sapi_name() !='cli') {
            return 'Browser';
        }else{
            return 'cli';
        }
    }
}

//Example Usage
if (!debug_backtrace()) {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    $runner = new Story_Runner();
    $runner->run();
}

