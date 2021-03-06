<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Index Controller
 */
class IndexController extends ActionController
{
    /**
     * {@inheritDoc}
     */
    public function indexAction()
    {
        $this->redirect('', array('controller' => 'script'));
    }
}
