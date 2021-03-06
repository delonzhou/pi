<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Custom route config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'article' => array(
        'section'  => 'front',
        'priority' => 120,

        'type'     => 'Custom\Article\Route\Article',
        'options'  => array(
            'structure_delimiter'   => '/',
            'param_delimiter'       => '/',
            'key_value_delimiter'   => '-',
            'route'                 => '/article',
            'defaults'        => array(
                'module'     => 'article',
                'controller' => 'index',
                'action'     => 'index',
            ),
        ),
    ),
);
