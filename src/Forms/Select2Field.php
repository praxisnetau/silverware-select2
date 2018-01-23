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
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\Relation;

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
     * Defines whether the field can handle multiple options.
     *
     * @var boolean
     */
    protected $multiple = false;
    
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
     * Renders the field for the template.
     *
     * @param array $properties
     *
     * @return DBHTMLText
     */
    public function Field($properties = [])
    {
        // Merge Options:
        
        $properties = array_merge($properties, [
            'Options' => $this->getOptions()
        ]);
        
        // Render Field:
        
        return FormField::Field($properties);
    }
    
    /**
     * Answers an array list containing the options for the field.
     *
     * @return ArrayList
     */
    public function getOptions()
    {
        // Create Options List:
        
        $options = ArrayList::create();
        
        // Iterate Source Items:
        
        foreach ($this->getSourceEmpty() as $value => $title) {
            $options->push($this->getFieldOption($value, $title));
        }
        
        // Handle Tags:
        
        if ($this->usesTags()) {
            
            // Obtain Source Values:
            
            $values = $this->getSourceValues();
            
            // Iterate Value Array:
            
            foreach ($this->getValueArray() as $value) {
                
                // Handle Tag Values:
                
                if (!in_array($value, $values)) {
                    $options->push($this->getFieldOption($value, $value));
                }
                
            }
            
        }
        
        // Apply Extensions:
        
        $this->extend('updateOptions', $options);
        
        // Answer Options List:
        
        return $options;
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
     * Defines the value of the multiple attribute.
     *
     * @param boolean $multiple
     *
     * @return $this
     */
    public function setMultiple($multiple)
    {
        $this->multiple = (boolean) $multiple;
        
        return $this;
    }
    
    /**
     * Answers the value of the multiple attribute.
     *
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->multiple;
    }
    
    /**
     * Answers the multiple name of the field.
     *
     * @return string
     */
    public function getMultipleName()
    {
        return sprintf('%s[]', $this->getName());
    }
    
    /**
     * Answers true if the field handles multiple tags.
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->getMultiple();
    }
    
    /**
     * Answers true if the field is configured to use tags.
     *
     * @return boolean
     */
    public function usesTags()
    {
        $config = $this->getFieldConfig();
        
        return (isset($config['tags']) && $config['tags']);
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
        
        if ($this->isMultiple()) {
            $attributes['multiple'] = true;
            $attributes['name'] = $this->getMultipleName();
        }
        
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
     * Defines the value of the field.
     *
     * @param mixed $value
     * @param array|DataObject $data
     *
     * @return $this
     */
    public function setValue($value, $data = null)
    {
        if ($data instanceof DataObject) {
            $this->loadFrom($data);
            return $this;
        }
        
        return parent::setValue($value);
    }
    
    /**
     * Answers true if the current value of this field matches the given option value.
     *
     * @param mixed $dataValue
     * @param mixed $userValue
     *
     * @return boolean
     */
    public function isSelectedValue($dataValue, $userValue)
    {
        if (!$this->isMultiple() || !is_array($userValue)) {
            return parent::isSelectedValue($dataValue, $userValue);
        }
        
        return in_array($dataValue, $userValue);
    }
    
    /**
     * Answers the value(s) of this field as an array.
     *
     * @return array
     */
    public function getValueArray()
    {
        return $this->getListValues($this->Value());
    }
    
    /**
     * Loads the value of the field from the given data object.
     *
     * @param DataObjectInterface $record
     *
     * @return void
     */
    public function loadFrom(DataObjectInterface $record)
    {
        // Obtain Field Name:
        
        $fieldName = $this->getName();
        
        // Bail Early (if needed):
        
        if (empty($fieldName) || empty($record)) {
            return;
        }
        
        // Determine Value Mode:
        
        if (!$this->isMultiple()) {
            
            // Load Singular Value:
            
            parent::setValue($record->$fieldName);
            
        } else {
            
            // Load Multiple Value:
            
            $relation = $this->getNamedRelation($record);
            
            if ($relation instanceof Relation) {
                $this->loadFromRelation($relation);
            } elseif ($record->hasField($fieldName)) {
                parent::setValue($this->stringDecode($record->$fieldName));
            }
            
        }
    }
    
    /**
     * Saves the value of the field into the given data object.
     *
     * @param DataObjectInterface $record
     *
     * @return void
     */
    public function saveInto(DataObjectInterface $record)
    {
        // Obtain Field Name:
        
        $fieldName = $this->getName();
        
        // Bail Early (if needed):
        
        if (empty($fieldName) || empty($record)) {
            return;
        }
        
        // Determine Value Mode:
        
        if (!$this->isMultiple()) {
            
            // Save Singular Value:
            
            parent::saveInto($record);
            
        } else {
            
            // Save Multiple Value:
            
            $relation = $this->getNamedRelation($record);
            
            if ($relation instanceof Relation) {
                $this->saveIntoRelation($relation);
            } elseif ($record->hasField($fieldName)) {
                $record->$fieldName = $this->stringEncode($this->getValueArray());
            }
            
        }
    }
    
    /**
     * Loads the value of the field from the given relation.
     *
     * @param Relation $relation
     *
     * @return void
     */
    public function loadFromRelation(Relation $relation)
    {
        parent::setValue(array_values($relation->getIDList()));
    }
    
    /**
     * Saves the value of the field into the given relation.
     *
     * @param Relation $relation
     *
     * @return void
     */
    public function saveIntoRelation(Relation $relation)
    {
        $relation->setByIDList($this->getValueArray());
    }
    
    /**
     * Performs validation on the receiver.
     *
     * @param Validator $validator
     *
     * @return boolean
     */
    public function validate($validator)
    {
        // Baily Early (if tags are used):
        
        if ($this->usesTags()) {
            return true;
        }
        
        // Call Parent Method (if not multiple):
        
        if (!$this->isMultiple()) {
            return parent::validate($validator);
        }
        
        // Obtain User Values:
        
        $values = $this->getValueArray();
        
        // Detect Invalid Values:
        
        $invalid = array_filter(
            $values,
            function ($userValue) {
                foreach ($this->getValidValues() as $formValue) {
                    if ($this->isSelectedValue($formValue, $userValue)) {
                        return false;
                    }
                }
                return true;
            }
        );
        
        // Answer Success (if none invalid):
        
        if (empty($invalid)) {
            return true;
        }
        
        // Define Validation Error:
        
        $validator->validationError(
            $this->getName(),
            _t(
                __CLASS__ . '.INVALIDOPTIONS',
                'Please select values within the list provided. Invalid option(s) {values} given.',
                [
                    'values' => implode(', ', $invalid)
                ]
            ),
            'validation'
        );
        
        // Answer Failure (invalid values detected):
        
        return false;
    }
    
    /**
     * Converts the given array of values into a string.
     *
     * @param array $values
     *
     * @return string
     */
    public function stringEncode($values)
    {
        return $values ? Convert::array2json(array_values($values)) : null;
    }
    
    /**
     * Decodes the given string of values into an array.
     *
     * @param string $values
     *
     * @return array
     */
    public function stringDecode($values)
    {
        return $values ? Convert::json2array($values) : [];
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
    
    /**
     * Answers the relation with the field name from the given data object.
     *
     * @param DataObjectInterface $record
     *
     * @return Relation
     */
    protected function getNamedRelation(DataObjectInterface $record)
    {
        return $record->hasMethod($this->Name) ? $record->{$this->Name}() : null;
    }
}
