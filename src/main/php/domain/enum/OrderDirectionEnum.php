<?php
/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

declare (strict_types=1);

namespace domain\enum;


use util\Enumeration;


/**
 * Contains order by directions.
 */
class OrderDirectionEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const ASCENDING = 'asc';
    public const DESCENDING = 'desc';
}