<?php
/* *
 * Numerator - Pagination methods supplier.
 *
 * Author: Aviel Fadida <avielfedida@gmail.com>
 * License: MIT <FULL LICENSE CONTENT AT DOCUMENT BOTTOM>
 * */
class dollar extends \Numerator\NumeratorBase
{

    /* The reason for $prevNext = null, $edgesSep = null, $pagesSep = null is very complex one and should be explained widely:
     *
     * As you can see this class extends NumeratorBase, the NumeratorBase version of getArray is:
     *
     * getArray($page, $this->totalPages)
     *
     * All the methods classes version of getArray is:
     *
     * getArray($page, $this->totalPages, $prevNext = null, $edgesSep = null)
     *
     * If I remove the default value(can be anything) assigned to the extra parameters, getArray will become:
     *
     * getArray($page, $this->totalPages, $prevNext, $edgesSep)
     *
     * Now why is that a problem?
     *
     * As for php strict standards both methods classes and NumeratorBase getArray declaration should be compatible.
     * What it means is that they want methods getArray to be:
     *
     * getArray($page, $this->totalPages)
     *
     * It is a big problem because I override NumeratorBase version to allow each method getArray
     * to also include its own behavior, after I call parent::getArray.
     *
     * So when I assign value(can be anything) to methods getArray extra parameters I bypass this strict standard
     * because this strict standard is apply to parameters without default values.
     *
     * You can say that NumeratorBase getArray don't care if you override it as long as you can call
     * the overriding version without the extra parameters (that is what compatible means).
     *
     * value(can be anything):
     * Why is it can be any value?
     *
     * The reason is because the default value is already supplied by Numerator getArray, so there is no way
     * that the arguments supplied to methods getArray will be be undefined, and because of that the extra parameters
     * default values from methods getArray will never applied.
     *
     * An alternative way to solve this strict standard, is leave methods getArray like:
     *
     * getArray($page, $this->totalPages, $prevNext, $edgesSep)
     *
     * But use error_reporting(E_ALL | E_STRICT); or any alternative way to block strict standards errors.
     * */
    public function getArray($page, $prevNext = null, $edgesSep = null, $pagesSep = null)
    {
        parent::getArray($page);
        foreach($this->returnedArray as &$value){
            $value = ($value * $this->devPer) - ($this->devPer -1) . '-' . ($value * $this->devPer);
        }

        // $maxRows and $lastElementFix is used to prevent multiple declarations for this all calculations.
        $maxRows = $this->totalPages * $this->devPer;
        $lastElementFix = $maxRows - $this->devPer + 1 . '-' . $this->devRows;

        // $lastKey is using end to get the last element value and array_search to get the key using this value, $lastKey uses explained below.
        $lastKey = array_search(end($this->returnedArray), $this->returnedArray);

        /* $lastKey is tested to be equal to the $this->totalPages, In that case I know that the last link is presented.
         * Why do I wanna know about the last key?
         *
         * For example:
         * Total pages is 400
         * Total rows is 397
         * Page number is 80
         * Per page is 5
         * left/right is 3,3
         *
         * What I will get from the foreach loop above is
         * [74 => '366-370', 75 => '371-375', 76 => '376-380', 77 => '381-385', 78 => '386-390', 79 => '391-395', 80 => '396-400']
         *
         * Can you see the problem?, Well the problem is '396-400', in other words this link says I present rows from 396
         * to 400, but we don't have row number 400 the total rows is 397 so the reason we want to see if the last key is presented
         * is to know if we have to fix the last element in the array, so after the fix it will show '396-397'.
         * */
        if($lastKey == $this->totalPages){
            $this->returnedArray[$lastKey] = $lastElementFix;
        }


        /* The following rows used to set $specialEdges parameter from addEdgedPages method.
         * The reason for this parameter is explained within the method(addEdgedPages) itself.
         * */

        $prependedArray = [1 => 1 . '-' . $this->devPer,

                           2 =>  $this->devPer + 1 . '-' . $this->devPer * 2];

        $appendedArray = [$this->totalPages -1 => $maxRows - ($this->devPer * 2) + 1 . '-' . ($maxRows - $this->devPer),

                          $this->totalPages => $lastElementFix];

        /* The order I call addPagesSep/addPrevNext/addEdgedPages is very important and cannot be changed.
         *
         * The reason for addPagesSep to be first is because it must work on the numbers only, it can't work
         * on array with previous/next links or with edged pages because it add separator only between pages
         * numbers for example(',' is the separator):
         *
         * « 1 2 ... 37,38,39,40,41,42,43 ... 79 80 »
         *
         * So as you can see it will be much easy to add separators when I don't need to check/remove previous/next or
         * edged pages from the array, because when addPagesSep is first it gets only: 37 38 39 40 41 42 43
         *
         * The reason for addPrevNext to be second is because it take the first/last elements and check to see
         * if it can add previous/next links, the problem is that addEdgedPages is adding links that not reflect
         * the current page(which addPrevNext rely on).
         *
         * There won't be a problem for addEdgedPages to be last because it work
         * with the first/last elements no matter if it is a previous/next link or just a link.
         * */

        $this->addPagesSep($pagesSep);
        $this->addPrevNext($prevNext);
        $this->addEdgedPages($edgesSep, [$prependedArray, $appendedArray]);
        return $this->returnedArray;
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