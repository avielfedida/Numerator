<?php
/* *
 * Numerator - Pagination methods supplier.
 *
 * Author: Aviel Fadida <avielfedida@gmail.com>
 * License: MIT <FULL LICENSE CONTENT AT DOCUMENT BOTTOM>
 * */
namespace Numerator;

class Numerator
{
    /* This framework uses multiple pages separators such as user defined pages separators or edges separators,
     * the key for this separators can be anything as long as it have 'SEP' string inside it, This framework guide the users
     * to test the keys for 'SEP' (Capital letters) inside them, so the user won't have to use stripos or regex with I modifier.
     *
     * The reason I'm using ++$x instead of $x++ is because it tested and found to be the fastest way to increment a number.
     * */

    private $methodName,
        $queryString = [],
        $page,
        $argumentsNames = ['first', 'second', 'third', 'fourth', 'fifth', 'sixth'], // This array is used in cases I want to get name from key.
        $classes = [],
        $instance,
        $error,
        $validDirSep = ['/', '\\'],
        $methodNames =
       ['&' => 'ampersand', '$' => 'dollar'];

    public function __construct($methodName, $incDir = null)
    {
        if($incDir){
            if(is_string($incDir)){

                // I'm checking to see if the first character is an absolute path.
                if(in_array($incDir[0], $this->validDirSep)){
                    // First to be checked is getcwd() function which on failure return false.
                    $documentRoot = getcwd();

                    if(!$documentRoot){
                        // In case getcwd returned false, I try to get the working directory using $_SERVER['DOCUMENT_ROOT'].
                        if(isset($_SERVER['DOCUMENT_ROOT'])){
                            $documentRoot = $_SERVER['DOCUMENT_ROOT'];
                        }else{
                            /* Finally when I didn't succeed to get the working directory this error
                             * came to inform the user that he/she can't use a absolute path in front
                             * of their path for example '/someFolder', the reason is because the Numerator app
                             * could not find the working directory using getcdw() or $_SERVER['DOCUMENT_ROOT'],
                             * the reason I'm not explaining the reason for the error is so the user won't get
                             * confused, so I only suggest alternative way.
                             * */
                            $this->error = 'Please remove the ' . $incDir[0] . ' in front of your path, You can use
                            double dots (..) in front as your path as many as you want.';
                        }
                    }
                }
            }else{
                $this->error = 'Please check you pass a valid string as a path for the included methods directory.';
            }
        }else{
            $incDir = 'methods';
            /* methods is the default directory name if $incDir = null,
             * also this default string determine the default path from Numerator.php
             * to methods folder.
             * */
        }


        if(!$this->error){
            switch($methodName)
            {
                case '&':
                case '$':

                    /* I'm first checking for isset($documentRoot) because it might be undefined,
                     * then I check if not false because getcwd() can return false on failure.
                     * */
                    if(isset($documentRoot) && $documentRoot){
                        // First case is used for absolute paths.
                        $path = $documentRoot . $incDir;
                    }else{
                    /* If there is not absolute path ('/someFolder'),
                     * then I can continue checking for relative/double dotted paths.
                     * */
                        $doubleDotCount = substr_count($incDir, '..');

                        $prependedPath = null;

                        /* $doubleDotCount:
                         * Will contain the number of double dots within the user path,
                         * the reason I need the to count the double dots within the user path is to know how many
                         * folder do I have to return from the Numerator.php directory.
                         *
                         * $prependedPath = null:
                         * As a default I assume that there is no double dots, in case where there is double dots
                         * it will require me to define $prependedPath.
                         *
                         *
                         * For loop explained:
                         * About dirname():
                         * dirname('someFolder/anotherFolder'): will result in 'someFolder'.
                         * or
                         * dirname('someFolder/anotherFolder/Numerator.php'): will result in 'someFolder/anotherFolder'.
                         *
                         * For the first iteration I taking the directory name where Numerator.php
                         * is defined using dirname(__DIR__).
                         *
                         * After I define $prependedPath to the Numerator.php directory I can go back as many folders
                         * I want using dirname($prependedPath).
                         * */


                        for($i = 0; $i < $doubleDotCount; ++$i){
                            if($i == 0){
                                $prependedPath = dirname(__DIR__);
                            }else{
                                $prependedPath = dirname($prependedPath);
                            }
                        }


                        /* In case $prependedPath is not null:
                         * I take $prependedPath(explained above) and add '/', finally I add  preg_replace('/\.\./', '', $incDir).
                         *
                         * preg_replace('/\.\./', '', $incDir) explained:
                         * I need to remove all dotes from the the $incDir for example:
                         * '../../someFolder' will result in '//someFolder', about the double // it will become /
                         * at below preg_replace('/[\/\\\]+/', '/', $path);
                         *
                         * In case $prependedPath stay null:
                         * I take the __DIR__ which return the current directory for Numerator.php file.
                         * Next I add the '/' and finally I add the $incDir.
                         * */

                        $path = ($prependedPath ? $prependedPath . '/' . preg_replace('/\.\./', '', $incDir) : __DIR__ . '/' . $incDir);
                    }



                    /* Why replace?, 2 reasons:
                     *
                     * Reason 1:
                     * The user may mistakenly define the following:
                     * 1. '//someFolder/anotherFolder'
                     * 2. '//\\//\someFolder//\/\\\\\anotherFolder'
                     *
                     * Reason 2:
                     * The user may add slash to the end of the path for example:
                     * 1. 'methods/'
                     *
                     * The problem is that the $path is adding '/' with or without to know if the user may
                     * or may not already added path separator, So for example the user supply 'methods/' and
                     * and as you can see when defining $path I add '/' without checking if there is already
                     * 'methods/' (slash) at the end, So the final result will be 'methods//'.
                     *
                     * The final reason is to prevent unknown responses by the operating systems
                     * I remove all of the double(//)or combination(/\) slashes.
                     * */
                    $path = preg_replace('/[\/\\\]+/', '/', $path);

                    /* The is 2 file_exists checks, The first is testing to see if the path provided by the user
                     * is exists, The second file_exists is to make sure the method file (dollar.php for example) is exists.
                     * */
                    if(file_exists($path)){

                        if(file_exists($path . '/' . $this->methodNames[$methodName] . '.php')){

                            require_once ($path . '/' . $this->methodNames[$methodName] . '.php');

                        }else{
                            $this->error = 'Please check if the file: ' . $this->methodNames[$methodName] . '.php exists inside: ' . $path;
                        }
                    }else{
                        $this->error = 'The path: ' . $path . ' is not exists.';
                    }

                    $this->methodName = $methodName;
                    break;
                default:
                    if(!$this->error) $this->error = "Method not found.";
            }
        }
    }

    public function setNumerator($methodSettings)
    {
        if(!$this->error) $this->instance = new $this->methodNames[$this->methodName]($methodSettings);
    }

    public function getArray($page, $prevNext = null, $edgesSep = null, $pagesSep = null)
    {
        if($this->instance){

            /* As you can see the 3 if statements below is checking in reverse, what I mean is that first
             * $pagesSep than $edgesSep and finally $prevNext, the reason is because:
             * As you know the error system is based on "set $this->error only if it wasn't set before" so in case of
             * an error I could check in each if statement if $this->error is already set but instead I decided to reverse the
             * check, this way the for example if an error occurred on both $pagesSep and $edgesSep the error from
             * $addEdgesPages will override the error from $pagesSep, and that is how I get the behavior I want.
             * */

            if($pagesSep && !is_string($pagesSep)) $this->error =
                'Please provide a string type value as your pages separator.';

            if($edgesSep &&  !is_string($edgesSep)) $this->error =
                'Please provide a string type value as your edges separator.';

            if($prevNext && (!is_array($prevNext) || count($prevNext) != 2 || !is_string($prevNext[0]) || !is_string($prevNext[1])))
                $this->error = 'Please provide a valid array with 2 string elements as your previous/next navigators.';


            /* The empty array ([]) is used so the developer foreach loop won't get null if there is an error,
            * The reason I don't want it to get null is because it will raise an error.
            * */

            if($this->error) return [];

            // The reason I expose the tested page number is so I won't have to retest the value on getHtml method.
            $this->page = intval($page) > 0 ? $page : 1;
            $this->page = $this->page > $this->instance->totalPages ? $this->instance->totalPages : $this->page;

            if($this->instance->error){

                /* I'm checking to see if the error within the instance is not identical to true(!==)
                 * because if its not than it is a custom error by the method itself.
                 * */
                if($this->instance->error !== true){
                    $this->error = $this->instance->error;
                }else{
                    $this->error = 'Settings validation error at '.get_class($this->instance).' class, Please check the settings array you pass to setNumerator().';
                }

                return [];
            }

            return $this->instance->getArray($this->page, $prevNext, $edgesSep, $pagesSep);
        }else{
            $this->error = 'You must set numerator using setNumerator method before using getArray or getHtml methods.';
            return [];
        }
    }

    // Important to remember that when I'm adding more parameters to this method I should add more arguments names on $argumentsNames.
    public function setClasses($containerClass = null, $pagesClass = null, $currentPageClass = null, $prevNextClass = null, $edgesSepClass = null, $pagesSepClass = null)
    {
        foreach(func_get_args() as $key => $value){
            // As you can see the only case that the value will be treated is when the value type is string.
            if(is_string($value)){
                if(preg_match('/-?[_a-z]+[_a-z\d-]*/i', $value)){
                    $this->classes[$key] = $value;
                }else{
                    if(!$this->error) $this->error = 'Please make sure you provide a valid class name as setClasses ' . $this->argumentsNames[$key] . 'argument.';
                }
            }
        }
    }

    // Important to remember that when I'm adding more parameters to this method I should add more arguments names on $argumentsNames.
    public function setQueryString($prependedQueryString  = null, $appendedQueryString  = null)
    {
        foreach(func_get_args() as $key => $value){
            if(is_string($value)){
                if(preg_match('/[\w\?\&\=\;\+\!\'\(\)\*\-\.\~\%]+/', $value)){
                    $this->queryString[$key] = $value;
                }else{
                    if(!$this->error) $this->error = 'Please provide a valid query string as setQueryString ' . $this->argumentsNames[$key] . ' argument.';
                }
            }
        }
    }

    public function getHtml($page, $prevNext = null, $edgesSep = null, $pagesSep = null)
    {
        $getArray = $this->getArray($page, $prevNext, $edgesSep, $pagesSep);

        // In case of an error $getArray will return empty array so getHtml return empty string.
        if(count($getArray) == 0) return '';

        $getHtml = '<div class="' . (isset($this->classes[0]) ? $this->classes[0] : 'n-container') . '">';

        foreach($getArray as $key => $value){

            if($this->page == $key){ // Current page.
                $getHtml .= '<span class="' . (isset($this->classes[2]) ? $this->classes[2] : 'n-current-page') . '">' . $value . '</span>';
                continue;
            }

            if(strpos($key, 'Edge') !== false){ // Edges separators.
                $getHtml .= '<span class="' . (isset($this->classes[4]) ? $this->classes[4] : 'n-edges-separator') . '">' . $value . '</span>';
                continue;
            }

            if(strpos($key, 'SEP') === 0){ // Pages separators.
                $getHtml .= '<span class="' . (isset($this->classes[5]) ? $this->classes[5] : 'n-pages-separator') . '">' . $value . '</span>';
                continue;
            }

            /* All of the above if statements order can be changed at anytime without any problems, the only exception is the
             * below if/else statements, if the above statements won't execute so the continue; will not skip the rest of the code
             * the below if/else statements will execute, so this if/else statements must remain after all of the above if statements.
             * */

            if($value == $this->instance->prevNext[0] || $value == $this->instance->prevNext[1]){
                if(isset($this->classes[3])){
                    $className = $this->classes[3];
                }else{
                    $className = 'n-prev-next';
                }
            }else{
                if(isset($this->classes[1])){
                    $className = $this->classes[1];
                }else{
                    $className = 'n-page';
                }
            }

            if(count($this->queryString) > 0){
                $getHtml .= '<a href="' .
                    (isset($this->queryString[0]) ? $this->queryString[0] . '&page=' : '?page=')
                    . $key .
                    (isset($this->queryString[1]) ? $this->queryString[1] : '') .
                    '" class="' . $className . '">';
            }else{
                $getHtml .= '<a href="?page=' . $key . '" class="'. $className . '">';
            }

            $getHtml .= $value . '</a>';
        }

        return $getHtml . '</div>';
    }

    public function getError()
    {
        return $this->error ? $this->error : 'No errors.';
    }
}

abstract class NumeratorBase
{
    protected $returnedArray, $devLeft, $devRight, $devPer, $devRows;
    public $error, $totalPages, $prevNext;

    /* The following construct is a base construct for the methods,
     * The reason to have a base construct is to allow fixed user behavior, What I mean is that the user will always
     * provide an array with [left, right, perPage, totalRows], And it also spare multiple and different constructors along
     * with the different methods.
     * */
    public function __construct($settings)
    {
        if(is_array($settings) && count($settings) == 4){
            $this->devLeft = intval($settings[0]) < 0 ? 0 : $settings[0];
            $this->devRight = intval($settings[1]) < 0 ? 0 : $settings[1];
            $this->devPer = intval($settings[2]) > 0 ? $settings[2] : 1;
            $this->devRows = intval($settings[3]) > 0 ? $settings[3] : 1;

            /* The (int) type casting is used because after I divide $this->devRows / $this->devPer the / operator will return
             * a float value, even after ceil($this->devRows / $this->devPer) the value type is still float, why is that a problem?
             *
             * 1. Consistency, it is important that all the keys will be integer types.
             * 2. Users checking/debugging the code may be confused because some keys are integers and some are floats.
             * */
            $this->totalPages = (int)ceil($this->devRows / $this->devPer);
        }else{
            $this->error = true;
        }
    }

    protected function getArray($page){

        /* This if statement will evaluates as true only when the totalPages number is bigger than devLeft + devRight.
         *
         * In the above case the totalPages number won't cover the developer expectations(devLeft,devRight).
         * The reason I need to know if the totalPages is bigger than the developer expectations(devLeft,devRight)
         * is so I will be able to add slots from 1 side to another for example:
         *
         * devLeft = 2
         * devRight = 2
         * totalPages = 5
         *
         * I need to know that if the page is 1, it means that I need to add 2 slots to the right so the way to make sure
         * that when I add slots to the right I won't passing the totalPages is with this if statement.
         *
         * As you can see I'm using > sign, The reason is because I need to remember that i
         * also have to remember that the devLeft + devRight is not including the current page number and that is the reason for
         * the > sign, for better understanding if do: ($this->devLeft + $this->devRight + 1) the + 1 is representing the current
         * page number slot, If I was doing so I would have to use the >= sign.
         * */
        if($this->totalPages > $this->devLeft + $this->devRight){
            $this->returnedArray = range($page - $this->devLeft, $page + $this->devRight);
            $left = 0;
            $right = 0;

            foreach($this->returnedArray as $key => $value){

                /*  First if:
                 * $value is the page number.
                 * Remove values with page number < 1 or number > $this->totalPages.
                 * The continue is so the value won't be calculated.
                 *
                 * Second if:
                 * Set left slots that was used.
                 *
                 * Third if:
                 * Set right slots that was used.
                 *
                 * Why the second if have continue and the third don't have?
                 * The reason is that it's last, The is no code after the third if so there is no code to skip.
                 *
                 * Also there is no order to the second and third if statements, The only exception is the
                 * first if that must stay first.
                 *
                 * If you replace the second and third if for example:
                 * if($value > $page){++$right;}
                 * if($value < $page){++$left;continue;}
                 *
                 * Do not forget to move the continue to the new second if:
                 * if($value > $page){$right+1;continue;}
                 * if($value < $page){$left+1;}
                 *
                 * The role for this foreach loop is the unset invalid values, values that smaller than 1
                 * or bigger than the totalPages number.
                 * */
                if($value < 1 || $value > $this->totalPages){ unset($this->returnedArray[$key]); continue;}
                if($value < $page){ ++$left; continue;}
                if($value > $page){ ++$right; }
            }


           /* The first if:
            * The if is asking is the left slots is smaller than the expectation($this->devLeft)?
            * If they are, add to the right slots the left slots that have not bean used.
            *
            * The second if:
            * The if is asking is the right slots is smaller than the expectation($this->devRight)?
            * If they are, add to the left slots the right slots that have not bean used.
            *
            *
            * Only 1 of this if statements will evaluate as true, in case:
            *
            * devLeft = 2
            * devRight = 2
            * totalPages = 3
            * page = 2
            *
            * In the above case both of the below statement should work, the reason they won't
            * work is because they will never be reached at all, if you take a look at the main if
            * statement, this if statement will never be evaluate as true in the above case.
            * */

            if($left < $this->devLeft) $right += $this->devLeft - $left;
            if($right < $this->devRight) $left += $this->devRight - $right;

            /* range():
             * Is used to set new array depending the left and right slots that should be used.
             *
             * array_combine():
             * The array combine is used to set the key to value because in some cases(for example the $ method)
             * where I can't use the value as the link href and the link name(<a href=$key>$key</a>).
             * So this way I can use the keys as href and the values as the link names(<a href=$value>$key</a>).
             * */


            $this->returnedArray = array_combine(range($page - $left, $page + $right),range($page - $left, $page + $right));
        }else{
            /* In this case where the $this->totalPages is less than or equal to the totalPages number + devLeft + devRight I cut
             * from 1 to the $this->totalPages.
             * */
            $this->returnedArray = array_combine(range(1, $this->totalPages), range(1, $this->totalPages));
        }
    }

    protected function addPagesSep($pagesSep)
    {
        if($pagesSep){

            /* First step is to take the last value, the reason I need it is so later in the foreach loop
             * I will know if the last value reached, why do I need this information?
             * 2 reasons:
             *
             * 1. So I won't add extra unnecessary separator after the last value.
             * 2. If you look into $value+1, Lets see what will happen if I add extra separator after the last value:
             *
             * [1 => 1, 2 => 2, 3 => 3]
             *
             * After:
             *
             * [1 => 1, 'SEP (1-2)' => 'sep', 2 => 2, 'SEP (2-3)' => 'sep', 3 => 3, 'SEP (3-4)' => 'sep']
             *
             * As you can see I got out of range of pages the last 'sep' key is 'SEP (3-4)' but I don't have page 4.
             * And even if we had page 4 but its not presented, so there is no logic for having 'sep' with key of 'SEP (3-4)'.
             *
             * The reason for using the $key inside the foreach loop is to allow working with methods like dollar where
             * the representation is different from the link itself.
             *
             * The first use of $key ($tmpArray[$key] = $value) is for the above reason.
             *
             * The second use of $key ('SEP ('.$key.'-'.($key+1).')') is to allow consistent key between methods with or without
             * representation separation.
             *
             * The tmpArray is used to temporary hold the new array with the separators added.
             *
             * Finally I override $this->returnedArray with $tmpArray.
             * */
            $lastElement = end($this->returnedArray);

            $tmpArray = [];

            foreach($this->returnedArray as $key => $value){
                $tmpArray[$key] = $value;
                if($value != $lastElement) $tmpArray['SEP ('.$key.'-'.($key+1).')'] = $pagesSep;
            }
            $this->returnedArray = $tmpArray;
        }
    }

    protected function addEdgedPages($edgesSep, $specialEdges = null)
    {
        if($edgesSep){

            /* Why $specialEdges?
             *
             * There may be some methods that differentiate between representation from the link itself,
             * if there is no difference the method('&') for example won't provide this argument and
             * $specialEdges will set to null, this will cause the default values(within the else statement) to take place.
             *
             * If there is a method('$' for example) that require a specialEdges its the method responsibility to set the edges.
             * */

            if($specialEdges){
                $prependedArray = $specialEdges[0];
                $appendedArray = $specialEdges[1];
            }else{
                $prependedArray = [1 => 1, 2 => 2];
                $appendedArray = [$this->totalPages-1 => $this->totalPages-1, $this->totalPages => $this->totalPages];
            }


            /* This first if statement checks to see if the first element key is bigger than 4
             * the reason is that I want 5 pages gap between the first page and the first element key
             *
             * LeftSideSEP:
             * Is used so the developer won't confuse and use it as a link.
             * */
            if(array_search((array_slice($this->returnedArray, 0, 1)[0]), $this->returnedArray) > 4)
                $this->returnedArray = $prependedArray + ['LeftEdgeSEP' => $edgesSep] + $this->returnedArray;

            /* This second if statement checks to see if the last element key is smaller than the $this->totalPages-4
             * the reason is that I want 5 pages gap between the last element key and the $this->totalPages
             *
             * RightSideSEP:
             * Is used so the developer won't confuse and use it as a link.
             * */
            if(array_search(array_slice($this->returnedArray, -1)[0], $this->returnedArray) < $this->totalPages-4)
                $this->returnedArray += ['RightEdgeSEP' => $edgesSep] + $appendedArray;

            /* The following lines should remove the existing previous/next line and prepend/append them after the
             * edged pages links for example: 1,2,LeftSideSEP,$this->prevNext[0],6,$this->prevNext[1],RightSideSEP,11,12 will
             * become: $this->prevNext[1],1,2,LeftSideSEP,6,RightSideSEP,11,12,$this->prevNext[1]
             *
             * $previousKey/$nextKey gets the existing if exists keys for the values($this->prevNext[0]/$this->prevNext[1]).
             *
             * array_search:
             * Used to get the key by value.
             *
             * array_diff:
             * Will remove the previous/next links.
             *
             * Finally we prepend/append the keys with the values($previousKey => $this->prevNext[0]/$nextKey => $this->prevNext[1])
             * to the returnedArray, the if is using !== and not != even there is no way at all, because in case the key
             * is a string, string can not be equal to false and a there will never will be key with the value of 0, but just
             * to make sure I'm checking for !== false.
             * */
            $previousKey = array_search($this->prevNext[0], $this->returnedArray);
            $nextKey = array_search($this->prevNext[1], $this->returnedArray);

            $this->returnedArray = array_diff($this->returnedArray, [$this->prevNext[0]]);
            $this->returnedArray = array_diff($this->returnedArray, [$this->prevNext[1]]);

            if($previousKey !== false){
                $this->returnedArray = [$previousKey => $this->prevNext[0]] + $this->returnedArray;
            }

            if($nextKey !== false){
                $this->returnedArray += [$nextKey => $this->prevNext[1]];
            }
        }
    }

    protected function addPrevNext($prevNext)
    {
        if($prevNext){

            // The reason I expose $prevNext is so addEdgedPages method and Numerator class will be able to access it.
            $this->prevNext = $prevNext;

            /* The first if statement:
             * Is used to make sure that the first most page is not already presented for example:
             * 1,2,3 page 1 is here so there is no need for previous link.
             *
             * array_slice:
             * Used to get the first value in the array, but what I actually want to get is the key because
             * the key is always the link href, how do I get the key? with array_search.
             *
             * array_search:
             * Used to get the key by value.
             *
             * -1:
             * The -1 after the array_search is so it will represent the previous link.
             *
             * Addition operator(+):
             * The reason for array()+$this->returnedArray is so the new element that represent the previous link will
             * be prepended to the existing array($this->returnedArray).
             * */

            if(array_slice($this->returnedArray, 0, 1)[0] != 1) $this->returnedArray = [array_search((array_slice($this->returnedArray, 0, 1)[0]), $this->returnedArray)-1 => $prevNext[0]] + $this->returnedArray;

            /* The second if statement:
             * Is used to make sure that the last most page is not already presented for example:
             * 1,2,3 page 3 is the last page and it is presented so there is no need for next link.
             *
             * array_slice:
             * Used to get the first value in the array, but what I actually want to get is the key because
             * the key is always the link href, how do I get the key? with array_search.
             *
             * array_search:
             * Used to get the key by value.
             *
             * +1:
             * The +1 after the array_search is so it will represent the next link.
             *
             * Addition operator(+):
             * The reason for +=array() is so the new element that represent the next link will
             * be appended to the existing $this->returnedArray.
             * */
            if(array_search(array_slice($this->returnedArray, -1)[0], $this->returnedArray) != $this->totalPages) $this->returnedArray += array(array_search(array_slice($this->returnedArray, -1)[0], $this->returnedArray)+1 => $prevNext[1]);
        }else{

            /* The below if statements have 2 conditions
             * The first condition is to check if the developer set the devLeft or the devRight to 0
             * If devLeft == 0 than I force(even if the developer don't want to) previous link
             * The reason is that if there will be devLeft == 0 and no previous link we can't navigate
             * to the left, same thing for the devRight.
             * About the second condition, this is the same condition as above fully explained conditions.
             * */
            if($this->devLeft == 0 && array_slice($this->returnedArray, 0, 1)[0] != 1)
                $this->returnedArray = [array_search((array_slice($this->returnedArray, 0, 1)[0]), $this->returnedArray)-1 => $prevNext[0]] + $this->returnedArray;

            if($this->devRight == 0 && array_search(array_slice($this->returnedArray, -1)[0], $this->returnedArray) != $this->totalPages)
                $this->returnedArray += [array_search(array_slice($this->returnedArray, -1)[0], $this->returnedArray)+1 => $prevNext[1]];
        }
    }
}
/*
Copyright (c) 2014 Aviel Fadida

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/