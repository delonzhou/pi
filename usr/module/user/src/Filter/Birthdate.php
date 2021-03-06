<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * Birth date display filter
 *
 * Transform birthdate format from YYYY-mm-dd to localized format
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Birthdate extends AbstractFilter
{
    /**
     * Filter birthdate value
     *
     * @param string|int $value
     * @return string
     */
    public function filter($value)
    {
        $value   = $value ?: 0;
        $replace = [];
        if (!is_numeric($value)) {
            list($year, $month, $day) = explode('-', $value);
            if ($year) {
                if ($year < 1970) {
                    $replace = [$day, $year];
                    $year    = 1970;
                    $day     = 28;
                }
                $value = mktime(0, 0, 0, $month, $day, $year);
            } else {
                $value = 0;
            }
        }
        if ($value) {
            $format = Pi::config('birthdate_format', 'user');
            $value  = date($format, $value);
            if ($replace) {
                $value = str_replace(['28', '1970'], $replace, $value);
            }
        } else {
            $value = '';
        }

        return $value;
    }
}
