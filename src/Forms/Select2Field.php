<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Select2\Forms
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-select2
 */

namespace SilverWare\Select2\Forms;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;

/**
 * An extension of the dropdown field class for a Select2 field.
 *
 * @package SilverWare\Select2\Forms
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-select2
 */
class Select2Field extends DropdownField
{
    /**
     * An array which defines the default configuration for instances.
     *
     * @var array
     * @config
     */
    private static $default_config = [
        'width' => '100%'
    ];
    
    /**
     * An array which holds the configuration for an instance.
     *
     * @var array
     */
    protected $config;
    
    /**
     * Constructs the object upon instantiation.
     *
     * @param string $name
     * @param string $title
     * @param array|ArrayAccess $source
     * @param mixed $value
     */
    public function __construct($name, $title = null, $source = [], $value = null)
    {
        // Construct Parent:
        
        parent::__construct($name, $title, $source, $value);
        
        // Define Default Config:
        
        $this->setConfig(self::config()->default_config);
        
        // Disable Chosen:
        
        $this->addExtraClass('no-chosen');
    }
    
    /**
     * Answers the field type for the template.
     *
     * @return string
     */
    public function Type()
    {
        return sprintf('select2field %s', parent::Type());
    }
    
    /**
     * Defines either the named config value, or the config array.
     *
     * @param string|array $arg1
     * @param mixed $arg2
     *
     * @return $this
     */
    public function setConfig($arg1, $arg2 = null)
    {
        if (is_array($arg1)) {
            $this->config = $arg1;
        } else {
            $this->config[$arg1] = $arg2;
        }
        
        return $this;
    }
    
    /**
     * Answers either the named config value, or the config array.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getConfig($name = null)
    {
        if (!is_null($name)) {
            return isset($this->config[$name]) ? $this->config[$name] : null;
        }
        
        return $this->config;
    }
    
    /**
     * Answers an array of HTML attributes for the field.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = array_merge(
            parent::getAttributes(),
            $this->getDataAttributes()
        );
        
        if (!isset($attributes['data-placeholder'])) {
            $attributes['data-placeholder'] = $this->getEmptyString();
        }
        
        return $attributes;
    }
    
    /**
     * Answers an array of data attributes for the field.
     *
     * @return array
     */
    public function getDataAttributes()
    {
        $attributes = [];
        
        foreach ($this->getFieldConfig() as $key => $value) {
            $attributes[sprintf('data-%s', $key)] = $this->getDataValue($value);
        }
        
        return $attributes;
    }
    
    /**
     * Converts the given data value to a string suitable for a data attribute.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function getDataValue($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            return Convert::array2json($value);
        } else {
            return Convert::raw2att($value);
        }
    }
    
    /**
     * Answers the field config for the receiver.
     *
     * @return array
     */
    protected function getFieldConfig()
    {
        $config = $this->getConfig();
        
        if ($this->getHasEmptyDefault()) {
            $config['allow-clear'] = true;
        }
        
        return $config;
    }
}
