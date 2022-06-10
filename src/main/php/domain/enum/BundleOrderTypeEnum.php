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
 * Contains bundle 'order by' options.
 */
class BundleOrderTypeEnum extends Enumeration
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const PRICE = 'price';
    public const COURSES = 'courses';
    public const SALES = 'sales';
}