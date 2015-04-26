<?php
/* *
 * Numerator - Pagination methods supplier.
 *
 * Author: Aviel Fadida <avielfedida@gmail.com>
 * License: MIT <FULL LICENSE CONTENT AT DOCUMENT BOTTOM>
 * */

class ampersand extends \Numerator\NumeratorBase
{

    /* The reason for $prevNext = null, $edgesSep = null, $pagesSep = null is very complex one and should be explained widely:
     *
     * As you can see this class extends NumeratorBase, the NumeratorBase version of getArray is:
     *
     * getArray($page)
     *
     * All the methods classes version of getArray is:
     *
     * getArray($page, $prevNext = null, $edgesSep = null)
     *
     * If I remove the default value(can be anything) assigned to the extra parameters, getArray will become:
     *
     * getArray($page, $prevNext, $edgesSep)
     *
     * Now why is that a problem?
     *
     * As for php strict standards both methods classes and NumeratorBase getArray declaration should be compatible.
     * What it means is that they want methods getArray to be:
     *
     * getArray($page)
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
     * getArray($page, $prevNext, $edgesSep)
     *
     * But use error_reporting(E_ALL | E_STRICT); or any alternative way to block strict standards errors.
     * */
    public function getArray($page, $prevNext = null, $edgesSep = null, $pagesSep = null)
    {
        parent::getArray($page);
        /* The order I call addPagesSep/addPrevNext/addEdgedPages is very important and cannot be changed.
         *
         * The reason for addPagesSep to be first is because it must work on the numbers only, it can't work
         * on array with previous/next links or with edged pages because it add separator only between pages
         * numbers for example(',' is the separator):
         *
         * « 1 2 ... 37,38,39,40,41,42,43 ... 79 80 »
         *
         * So as you can see it will be much easy to add separators when I don't need to check/remove previous/next or
         * edged pages from the array, Because when addPagesSep is first it gets only: 37 38 39 40 41 42 43
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
        $this->addEdgedPages($edgesSep);
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