<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi\Application\Api\AbstractApi;

class PermFront extends AbstractApi
{
    protected $module = 'demo';

    public function getResources()
    {
        $resources = array();
        for ($i = 1; $i <= 10; $i++) {
            $name = $this->module . '-resource-' . $i;
            $title = ucwords($this->module . ' resource front ' . $i);
            $resources[$name] = $title;
        }

        //vd($resources);
        return $resources;
    }
}
