<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router\Http;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\RouteInterface;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Default route for Pi Engine
 *
 * Use cases:
 *
 * - Same structure, key-value and param delimiters:
 *   - Full mode: /module/controller/action/key1/val1/key2/val2
 *   - Full structure only: /module/controller/action
 *   - Module with default structure: /module
 * - Same structure and param delimiters:
 *   - Full mode: /module/controller/action/key1-val1/key2-val2
 *   - Full structure only: /module/controller/action
 * - Different structure delimiter:
 *   - Full mode:
 *      /module-controller-action/key1/val1/key2/val2;
 *      /module-controller-action/key1-val2/key2-val2
 *   - Default structure and parameters:
 *      /module/key1/val1/key2/val2;
 *      /module/key1-val1/key2-val2
 *   - Default structure: /module-controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Standard implements RouteInterface
{
    /**
     * Path prefix
     * @var string
     */
    protected $prefix = '';

    /**
     * Delimiter between structured values of module, controller and action.
     * @var string
     */
    protected $structureDelimiter = '/';

    /**
     * Delimiter between keys and values.
     * @var string
     */
    protected $keyValueDelimiter = '/';

    /**
     * Delimiter before parameters.
     * @var array
     */
    protected $paramDelimiter = '/';

    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'system',
        'controller'    => 'index',
        'action'        => 'index'
    );

    /**
     * List of assembled parameters.
     * @var array
     */
    protected $assembledParams = array();

    /** @var array Specific options */
    protected $options = array();

    /**
     * Create a new wildcard route.
     *
     * @param string|null $prefix
     * @param string $structureDelimiter
     * @param string $keyValueDelimiter
     * @param string $paramDelimiter
     * @param array  $defaults
     *
     * @return \Pi\Mvc\Router\Http\Standard
     */
    public function __construct(
        $prefix = null,
        $structureDelimiter = '/',
        $keyValueDelimiter = '/',
        $paramDelimiter = '/',
        array $defaults = array()
    ) {
        $this->prefix               = (null !== $prefix) ? $prefix : $this->prefix;
        $this->structureDelimiter   = $structureDelimiter;
        $this->keyValueDelimiter    = $keyValueDelimiter;
        $this->paramDelimiter       = $paramDelimiter;
        $this->defaults             = array_merge($this->defaults, $defaults);
    }

    /**
     * Set options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = array())
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Get options
     *
     * @param string $key
     *
     * @return array|mixed
     */
    public function getOptions($key = null)
    {
        if ($key) {
            $result = isset($this->options[$key]) ? $this->options[$key] : null;
        } else {
            $result = $this->options;
        }

        return $result;
    }

    /**
     * factory(): defined by Route interface.
     *
     * @see    Route::factory()
     *
     * @param  array|Traversable $options
     *
     * @throws \InvalidArgumentException
     * @return RouteInterface
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new \InvalidArgumentException(__METHOD__
                . ' expects an array or Traversable set of options');
        }

        if (!isset($options['prefix'])) {
            $options['prefix'] = isset($options['route'])
                ? $options['route'] : null;
        }

        if (!isset($options['structure_delimiter'])) {
            $options['structure_delimiter'] = '/';
        }

        if (!isset($options['key_value_delimiter'])) {
            $options['key_value_delimiter'] = '/';
        }

        if (!isset($options['param_delimiter'])) {
            $options['param_delimiter'] = '/';
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        $route = new static(
            $options['prefix'],
            $options['structure_delimiter'],
            $options['key_value_delimiter'],
            $options['param_delimiter'],
            $options['defaults']
        );
        $route->setOptions($options);

        return $route;
    }

    /**
     * Get cleaned path
     *
     * @param Request $request
     * @param string $pathOffset
     * @return array
     */
    protected function canonizePath(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset !== null) {
            $path = substr($path, $pathOffset);
        }
        $pathLength = strlen($path);

        if ($this->prefix) {
            /*
            $prefix = rtrim($this->prefix, $this->paramDelimiter)
                . $this->paramDelimiter;
            $path = rtrim($path, $this->paramDelimiter)
                . $this->paramDelimiter;
            */
            $prefix = trim($this->prefix, $this->paramDelimiter)
                . $this->paramDelimiter;
            $path = trim($path, $this->paramDelimiter)
                . $this->paramDelimiter;
            $prefixLength = strlen($prefix);
            if ($prefix != substr($path, 0, $prefixLength)) {
                return null;
            }
            $path = substr($path, $prefixLength);
        }
        $path = trim($path, $this->paramDelimiter);

        return array($path, $pathLength);
    }

    /**
     * Parse matched path into params
     *
     * @param array $params
     * @return array
     */
    protected function parseParams(array $params)
    {
        $matches = array();

        if ($this->keyValueDelimiter === $this->paramDelimiter) {
            $count = count($params);

            for ($i = 0; $i < $count; $i += 2) {
                if (isset($params[$i + 1])) {
                    $matches[$this->decode($params[$i])] = $this->decode(
                        $params[$i + 1]
                    );
                }
            }
        } else {
            foreach ($params as $param) {
                $param = explode($this->keyValueDelimiter, $param, 2);
                if (isset($param[1])) {
                    $matches[$this->decode($param[0])] = $this->decode($param[1]);
                }
            }
        }

        //$matches = array_merge($this->defaults, $matches);

        return $matches;
    }

    /**
     * Parse matched path into params
     *
     * @param string $path
     * @return array
     */
    protected function parse($path)
    {
        $matches = array();
        $params  = $path
            ? explode($this->paramDelimiter,
                trim($path, $this->paramDelimiter))
            : array();

        if ($this->paramDelimiter === $this->structureDelimiter) {
            foreach(array('module', 'controller', 'action') as $key) {
                if (!empty($params)) {
                    $matches[$key] = array_shift($params);
                }
            }
        } else {
            $mca = explode($this->structureDelimiter, $params[0]);
            foreach(array('module', 'controller', 'action') as $key) {
                if (!empty($mca)) {
                    $matches[$key] = array_shift($mca);
                }
            }
            array_shift($params);
        }

        $matches = array_merge($matches, $this->parseParams($params));
        $matches = array_merge($this->defaults, $matches);

        return $matches;
    }

    /**
     * match(): defined by Route interface.
     *
     * @see    Route::match()
     * @param  Request $request
     * @param int|null  $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        list($path, $pathLength) = $result;
        $matches = $this->parse($path);
        if (!is_array($matches)) {
            return null;
        }

        return new RouteMatch($matches, $pathLength);
    }

    /**
     * Assemble params
     *
     * @param array $params
     *
     * @return string
     */
    protected function assembleParams(array $params)
    {
        $url = '';
        foreach ($params as $key => $value) {
            if (in_array($key, array('section', 'module', 'controller', 'action'))) {
                continue;
            }
            if (null === $value || '' === $value) {
                continue;
            }
            $url .= $this->paramDelimiter . $this->encode($key)
                . $this->keyValueDelimiter . $this->encode($value);
        }
        $url = ltrim($url, $this->paramDelimiter);

        /* $finalUrl = rtrim($url, '/');

        return $finalUrl; */

        return $url;
    }

    /**
     * Assemble structure
     *
     * @param array $params
     * @param string $url   URL to append
     *
     * @return string
     */
    protected function assembleStructure(array $params, $url = '')
    {
        $mca = array();
        foreach (array('module', 'controller', 'action') as $key) {
            if (!empty($params[$key])) {
                $mca[$key] = $this->encode($params[$key]);
            }
        }
        if ($this->paramDelimiter === $this->structureDelimiter) {
            foreach(array('action', 'controller', 'module') as $key) {
                if (!empty($url) || $mca[$key] !== $this->defaults[$key]) {
                    $url = $this->encode($mca[$key]) . $this->paramDelimiter
                        . $url;
                }
            }
        } else {
            $structure = urlencode($mca['module']);
            if ($mca['controller'] !== $this->defaults['controller']) {
                $structure .= $this->structureDelimiter
                    . $this->encode($mca['controller']);
                if ($mca['action'] !== $this->defaults['action']) {
                    $structure .= $this->structureDelimiter
                        . $this->encode($mca['action']);
                }
            } elseif ($mca['action'] !== $this->defaults['action']) {
                $structure .= $this->structureDelimiter
                    . $this->encode($mca['controller']);
                $structure .= $this->structureDelimiter
                    . $this->encode($mca['action']);
            }
            $url = $structure . ($url ? $this->paramDelimiter . $url : '');
        }

        /* $finalUrl = rtrim($url, '/');

        return $finalUrl; */

        return $url;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return string
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }

        $url = $this->assembleParams($mergedParams);
        $url = $this->assembleStructure($mergedParams, $url);

        $prefix = $this->prefix
            ? $this->paramDelimiter
            . trim($this->prefix, $this->paramDelimiter)
            : '';

        /* $finalUrl = rtrim($prefix . $this->paramDelimiter . $url, '/');

        return $finalUrl; */

        return $prefix . $this->paramDelimiter . $url;
    }

    /**
     * getAssembledParams(): defined by Route interface.
     *
     * @see    Route::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return array();
    }

    /**
     * Encode a path segment.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function encode($value)
    {
        return $value ? rawurlencode($value) : $value;
    }

    /**
     * Decode a path segment.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function decode($value)
    {
        return $value ? rawurldecode($value) : $value;
    }
}