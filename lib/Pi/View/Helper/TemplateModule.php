<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Pi\View\Resolver\ModuleTemplate as ModuleTemplateResolver;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for module template inclusion
 *
 * Usage inside a phtml template
 *
 * ```
 *  include $this->templateModule('admin/public-index.phtml');
 *  include $this->templateModule('admin/public-index.phtml', <module-name>);
 * ```
 *
 * @see Pi\View\Resolver\ModuleTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TemplateModule extends AbstractHelper
{
    /**
     * Get full path to a module template
     *
     * @param   string  $template
     * @param   string|null $module
     * @return  string|false
     */
    public function __invoke($template, $module = null)
    {
        $template = $module ? $module . ':' . $template : $template;

        $resolver = new ModuleTemplateResolver;
        $template = $resolver->resolve($template, $this->view);

        return $template;
    }
}
