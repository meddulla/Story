<?php
/**
 * Spec.
 *
 * Usage:
 * 1. Inherit from this class
 * 2. $this->value($myvar)->is('string')->equals('test');
 * 3. $this->value($myvar)->is('true');
 * 4. etc ..
 * 5. Get output: $this->output();
 * OR
 * 1. value($myvar)->equals(26);
 * 2. etc ..
 * 3. $spec->output();
 *
 * Types:
 * is('numeric') and should->be('numeric') are equivalent.
 *
 * Operators:
 * equal(45), greater_than(23), less_than(21),
 * not_equal,greater_or_equal, less_or_equal,
 * is_true (or is(true)), is_false (or is(false)).
 *
 * @todo       isset, is_instance_of
 * @todo in message get name of variable passed (is it possible??)
 *      see the inpect func at http://stackoverflow.com/questions/255312/how-to-get-a-variable-name-as-a-string-in-php
 * @todo-maybe OR operator is('true')->or->equals(25);
 *
 *
 *             See https://github.com/phpspec/phpspec/blob/master/src/PHPSpec/Specification.php
*/
class Spec
{
    public function __construct()
    {
        $this->successes = 0;
        $this->fails = 0;
        $this->failures = array();
        $this->rules = array();

        //connectors, syntactic sugar
        $this->should = $this;
        $this->and = $this;
        $this->is = $this;
        $this->be = $this;
    }
    //all calls must start with this
    //method:
    //$this->value($age)->equals(25);
    public function value($val)
    {
        $this->val = $val;
        return $this;
    }
    public function is($what = null)
    {
        if (is_null($what)) {
            return $this;
        } elseif (is_bool($what)) {
            $what = $what ? 'true' : 'false';
        }
        $method = 'is_' . (string)$what;
        if (function_exists($method)) {
            return $this->passed($method($this->val), $method);
        } elseif (method_exists($this, $method)) {
            return $this->$method();
        }
        die('ERROR');
        //throw

    }
    public function is_not($what = null)
    {
        if (is_null($what)) {
            return $this;
        } elseif (is_bool($what)) {
            $what = $what ? 'true' : 'false';
        }
        $method = 'is_' . (string)$what;
        $real_method = 'is_not_' . $what;
        if (function_exists($method)) {
            return $this->passed(!$method($this->val), $real_method);
        } elseif (method_exists($this, $real_method)) {
            return $this->$real_method();
        }
        die('ERROR');
        //throw

    }
    //@alias of is
    //should->be(true)
    public function be($what = null)
    {
        return $this->is($what);
    }
    public function less_than($compare)
    {
        return $this->passed($this->val < $compare ? true : false, __FUNCTION__, $compare);
    }
    public function less_or_equal($compare)
    {
        return $this->passed($this->val <= $compare ? true : false, __FUNCTION__, $compare);
    }
    public function greater_than($compare)
    {
        return $this->passed($this->val > $compare ? true : false, __FUNCTION__, $compare);
    }
    public function greater_or_equal($compare)
    {
        return $this->passed($this->val >= $compare ? true : false, __FUNCTION__, $compare);
    }
    public function is_true()
    {
        return $this->passed($this->val === true ? true : false, __FUNCTION__);
    }
    public function is_false()
    {
        return $this->passed($this->val === false ? true : false, __FUNCTION__);
    }

    /**
     * has function.
     *
     * Checks if the given string
     * (must be a string or will
     * result to fail)
     * is inside value.
     * Case insensitive comparison.
     *
     * @param string $compare
     *
     * @access public
     * @return object
    */
    public function has($compare)
    {
        if (is_string($this->val) && stripos($this->val, $compare) !== false) {
            return $this->passed(true, __FUNCTION__, $compare);
        }
        if (is_array($this->val) && in_array($compare,$this->val)) {
            return $this->passed(true, __FUNCTION__, $compare);
        }
        return $this->passed(false, __FUNCTION__, $compare);
    }

    public function has_definition($compare)
    {
        if (is_array($this->val) && isset($this->val[$compare])) {
            return $this->passed(true, __FUNCTION__, $compare);
        }
        return $this->passed(false, __FUNCTION__, $compare);
    }

    /**
     * contains function.
     *
     * Checks if the given string
     * (must be a string or will
     * result to fail)
     * is inside value.
     * Case insensitive comparison.
     *
     * @param string $compare
     *
     * @access public
     * @return object
     * @see Spec::has
     * @alias of has
    */
    public function contains($compare)
    {
        return $this->has($compare);
    }

    /**
     * has_not function.
     *
     * Checks if the given string
     * (must be a string or will
     * result to fail)
     * is not inside value.
     * Case insensitive comparison.
     *
     * @param string $compare
     *
     * @access public
     * @return object
    */
    public function has_not($compare)
    {
        if (is_string($this->val) && stripos($this->val, $compare) === false) {
            return $this->passed(true, __FUNCTION__, $compare);
        }
        if (is_array($this->val) && !in_array($var,$this->val)) {
            return $this->passed(true, __FUNCTION__, $compare);
        }
        return $this->passed(false, __FUNCTION__, $compare);
    }

    public function has_not_definition($compare)
    {
        if (is_array($this->val) && !isset($this->val[$compare])) {
            return $this->passed(true, __FUNCTION__, $compare);
        }
        return $this->passed(false, __FUNCTION__, $compare);
    }


    /**
     * does_not_contain function.
     *
     * Checks if the given string
     * (must be a string or will
     * result to fail)
     * is NOT inside value.
     * Case insensitive comparison.
     *
     * @param string $compare
     *
     * @access public
     * @return object
     * @see Spec::has_not
     * @alias of has_not
    */
    public function does_not_contain($compare)
    {
        return $this->has_not($compare);
    }
    public function equals($compare)
    {
        $bool = $compare == $this->val ? true : false;
        return $this->passed($bool, __FUNCTION__, $compare);
    }
    public function not_equals($compare)
    {
        $bool = $compare != $this->val ? true : false;
        return $this->passed($bool, __FUNCTION__, $compare);
    }
    public function is_not_equal_to($compare)
    {
        return $this->not_equals($compare);
    }
    public function is_equal_to($compare)
    {
        return $this->equals($compare);
    }
    public function equal($compare)
    {
        return $this->equals($compare);
    }
    public function not_equal($compare)
    {
        return $this->not_equals($compare);
    }
    public function differs($compare)
    {
        return $this->not_equals($compare);
    }

    //alias
    public function is_equals_to($compare)
    {
        return $this->equals($compare);
    }
    public function really_equals($compare)
    {
        $bool = $compare === $this->val ? true : false;
        return $this->passed($bool, __FUNCTION__, $compare);
    }

    //end methods available

    public function passed($bool, $caller_func, $compare = null)
    {
        if ($bool === true) {
            $this->successes++;
        } else {
            $this->fails++;
            //explain the failure
            $this->rules[] = ' ' . str_replace('_', ' ', $caller_func) . ' ' . (!is_null($compare) ? $compare . ' ' : '').PHP_EOL;
            $this->failures[] = $this->val . ' ' . str_replace('_', ' ', $caller_func) . ' ' . (!is_null($compare) ? $compare . ' ' : '').PHP_EOL;
        }
        return $this;
    }

    public function reset()
    {
        $this->successes = 0;
        $this->fails = 0;
        $this->failures = array();
    }

    public function results()
    {
        $data['successes'] = $this->successes;
        $data['fails'] = $this->fails;
        $data['total'] = $this->successes + $this->fails;
        $data['failures'] = $this->failures;
        return $data;
    }
    public function output($format = 'simple')
    {
        $results = $this->results();
        echo '<pre>';
        print_r($results);
        echo '</pre>';
    }
}




//test
if (!debug_backtrace()) {
    //helper
    GLOBAL $spec;
    $spec = new Spec();
    function value($val)
    {
        global $spec;
        return $spec->value($val);
    }


    //print('debug_backtrace works');
    class Example_Spec extends Spec
    {
        public function test_it()
        {
            $name = 'Ana';
            $age = 25;
            $female = true;
            $text = $name . ' went to the house and ate up all she could till she burst
                and flew into the deep ocean to gather herself down again.';
            //strings
            $this->value($name)->is('string')->equals('Ana');
            $this->value($text)->is('string')->has($name)->has_not('Nuno');
            $this->value($text)->has($name)->and->has_not('Nuno');
            $this->value($text)->contains($name)->and->does_not_contain('Nuno');
            //numbers
            $this->value($age)->is('integer')->equals(25);
            $this->value($age)->should->be('integer')->equals(25);
            $this->value($age)->should->be('numeric')->equals(25);
            //booleans
            $this->value($female)->is('true');
            $this->value($female)->is(true);
            $this->value($female)->should->equals(true);
            $this->value($female)->should->be(true);
            //with helper
            //syntactic sugar (but uses globals)
            value($name)->is('string');
            value($age)->should->be('integer')->and->equal(25);
            value($age)->should->not_equal(26);
            value($age)->should->be('numeric')->equal(25);
            value($age)->is('numeric')->and->greater_than(24);
            value($age)->is('numeric')->and->less_or_equal(26);
            value($age)->should->be->greater_than(23);
            value($age)->is->greater_than(23);
            value($age)->equals(25);
            //should fail (3 failures)
            value($age)->is_not('numeric')->and->equal('Ana');
            value($age)->is('string')->and->equal(25);
        }
    }
    $es = new Example_Spec();
    $es->test_it();
    $es->output();
    //2nd example usage
    $spec->output();
}
