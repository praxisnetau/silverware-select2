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

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\Map;
use SilverStripe\ORM\Relation;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ViewableData;
use ArrayAccess;

/**
 * An extension of the Select2 field class for a Select2 Ajax field.
 *
 * @package SilverWare\Select2\Forms
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-select2
 */
class Select2AjaxField extends Select2Field
{
    /**
     * Defines the allowed actions for this field.
     *
     * @var array
     * @config
     */
    private static $allowed_actions = [
        'search'
    ];
    
    /**
     * An array which defines the default Ajax configuration for instances.
     *
     * @var array
     * @config
     */
    private static $default_ajax_config = [
        'cache' => true,
        'delay' => 250
    ];
    
    /**
     * An array which defines the default configuration for instances.
     *
     * @var array
     * @config
     */
    private static $default_config = [
        'minimum-input-length' => 2
    ];
    
    /**
     * An array which holds the Ajax configuration for an instance.
     *
     * @var array
     */
    protected $ajaxConfig;
    
    /**
     * Defines whether Ajax is enabled or disabled for the field.
     *
     * @var boolean
     */
    protected $ajaxEnabled = true;
    
    /**
     * The data class to search via Ajax.
     *
     * @var string
     */
    protected $dataClass = SiteTree::class;
    
    /**
     * The ID field for the data class.
     *
     * @var string
     */
    protected $idField = 'ID';
    
    /**
     * The text field to display for the data class.
     *
     * @var string
     */
    protected $textField = 'Title';
    
    /**
     * The fields to search on the data class.
     *
     * @var array
     */
    protected $searchFields = [
        'Title'
    ];
    
    /**
     * The fields to sort the result list by.
     *
     * @var array|string
     */
    protected $sortBy = [
        'Title' => 'ASC'
    ];
    
    /**
     * The maximum number of records to answer.
     *
     * @var integer
     */
    protected $limit = 256;
    
    /**
     * An array of filters which specify records to be excluded from the search.
     *
     * @var array
     */
    protected $exclude = [];
    
    /**
     * Defines the string format to use for a result.
     *
     * @var string
     */
    protected $formatResult;
    
    /**
     * Defines the string format to use for a selection.
     *
     * @var string
     */
    protected $formatSelection;
    
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
        
        // Define Default Ajax Config:
        
        $this->setAjaxConfig(self::config()->default_ajax_config);
        
        // Define Empty String:
        
        $this->setEmptyString(_t(__CLASS__ . '.SEARCH', 'Search'));
    }
    
    /**
     * Answers the field type for the template.
     *
     * @return string
     */
    public function Type()
    {
        return sprintf('select2ajaxfield %s', parent::Type());
    }
    
    /**
     * Defines the source for the receiver.
     *
     * @param array|ArrayAccess
     *
     * @return $this
     */
    public function setSource($source)
    {
        if ($source instanceof DataList) {
            $this->setDataClass($source->dataClass());
        }
        
        return parent::setSource($source);
    }
    
    /**
     * Defines either the named Ajax config value, or the Ajax config array.
     *
     * @param string|array $arg1
     * @param mixed $arg2
     *
     * @return $this
     */
    public function setAjaxConfig($arg1, $arg2 = null)
    {
        if (is_array($arg1)) {
            $this->ajaxConfig = $arg1;
        } else {
            $this->ajaxConfig[$arg1] = $arg2;
        }
        
        return $this;
    }
    
    /**
     * Answers either the named Ajax config value, or the Ajax config array.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAjaxConfig($name = null)
    {
        if (!is_null($name)) {
            return isset($this->ajaxConfig[$name]) ? $this->ajaxConfig[$name] : null;
        }
        
        return $this->ajaxConfig;
    }
    
    /**
     * Defines the value of the ajaxEnabled attribute.
     *
     * @param boolean $ajaxEnabled
     *
     * @return $this
     */
    public function setAjaxEnabled($ajaxEnabled)
    {
        $this->ajaxEnabled = (boolean) $ajaxEnabled;
        
        return $this;
    }
    
    /**
     * Answers the value of the ajaxEnabled attribute.
     *
     * @return boolean
     */
    public function getAjaxEnabled()
    {
        return $this->ajaxEnabled;
    }
    
    /**
     * Defines the value of the dataClass attribute.
     *
     * @param string $dataClass
     *
     * @return $this
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = (string) $dataClass;
        
        return $this;
    }
    
    /**
     * Answers the value of the dataClass attribute.
     *
     * @return string
     */
    public function getDataClass()
    {
        return $this->dataClass;
    }
    
    /**
     * Defines the value of the idField attribute.
     *
     * @param string $idField
     *
     * @return $this
     */
    public function setIDField($idField)
    {
        $this->idField = (string) $idField;
        
        return $this;
    }
    
    /**
     * Answers the value of the idField attribute.
     *
     * @return string
     */
    public function getIDField()
    {
        return $this->idField;
    }
    
    /**
     * Defines the value of the textField attribute.
     *
     * @param string $textField
     *
     * @return $this
     */
    public function setTextField($textField)
    {
        $this->textField = (string) $textField;
        
        return $this;
    }
    
    /**
     * Answers the value of the textField attribute.
     *
     * @return string
     */
    public function getTextField()
    {
        return $this->textField;
    }
    
    /**
     * Defines the value of the searchFields attribute.
     *
     * @param array $searchFields
     *
     * @return $this
     */
    public function setSearchFields($searchFields)
    {
        $this->searchFields = (array) $searchFields;
        
        return $this;
    }
    
    /**
     * Answers the value of the searchFields attribute.
     *
     * @return array
     */
    public function getSearchFields()
    {
        return $this->searchFields;
    }
    
    /**
     * Defines the value of the sortBy attribute.
     *
     * @param array|string $sortBy
     *
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        
        return $this;
    }
    
    /**
     * Answers the value of the sortBy attribute.
     *
     * @return array
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }
    
    /**
     * Defines the value of the limit attribute.
     *
     * @param integer $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = (integer) $limit;
        
        return $this;
    }
    
    /**
     * Answers the value of the limit attribute.
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Defines the value of the exclude attribute.
     *
     * @param array $exclude
     *
     * @return $this
     */
    public function setExclude($exclude)
    {
        $this->exclude = (array) $exclude;
        
        return $this;
    }
    
    /**
     * Answers the value of the exclude attribute.
     *
     * @return array
     */
    public function getExclude()
    {
        return $this->exclude;
    }
    
    /**
     * Defines the value of the formatResult attribute.
     *
     * @param string $formatResult
     *
     * @return $this
     */
    public function setFormatResult($formatResult)
    {
        $this->formatResult = (string) $formatResult;
        
        return $this;
    }
    
    /**
     * Answers the value of the formatResult attribute.
     *
     * @return string
     */
    public function getFormatResult()
    {
        return $this->formatResult;
    }
    
    /**
     * Defines the value of the formatSelection attribute.
     *
     * @param string $formatSelection
     *
     * @return $this
     */
    public function setFormatSelection($formatSelection)
    {
        $this->formatSelection = (string) $formatSelection;
        
        return $this;
    }
    
    /**
     * Answers the value of the formatSelection attribute.
     *
     * @return string
     */
    public function getFormatSelection()
    {
        return $this->formatSelection;
    }
    
    /**
     * Updates the text field, search fields and sort order to the specified field name.
     *
     * @param string $field
     * @param string $order
     *
     * @return $this
     */
    public function setDescriptor($field, $order = 'ASC')
    {
        // Define Attributes:
        
        $this->setTextField($field);
        $this->setSearchFields([$field]);
        $this->setSortBy([$field => $order]);
        
        // Answer Self:
        
        return $this;
    }
    
    /**
     * Answers an array of data attributes for the field.
     *
     * @return array
     */
    public function getDataAttributes()
    {
        $attributes = parent::getDataAttributes();
        
        if ($this->isAjaxEnabled()) {
            
            foreach ($this->getFieldAjaxConfig() as $key => $value) {
                $attributes[sprintf('data-ajax--%s', $key)] = $this->getDataValue($value);
            }
            
        }
        
        return $attributes;
    }
    
    /**
     * Answers true if Ajax is enabled for the field.
     *
     * @return boolean
     */
    public function isAjaxEnabled()
    {
        return $this->ajaxEnabled;
    }
    
    /**
     * Answers an HTTP response containing JSON results matching the given search parameters.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPResponse
     */
    public function search(HTTPRequest $request)
    {
        // Detect Ajax:
        
        if (!$request->isAjax()) {
            return;
        }
        
        // Initialise:
        
        $data = ['results' => []];
        
        // Create Data List:
        
        $list = $this->getList();
        
        // Filter Data List:
        
        if ($term = $request->getVar('term')) {
            $list = $list->filterAny($this->getSearchFilters($term))->exclude($this->getExclude());
        }
        
        // Sort Data List:
        
        if ($sort = $this->getSortBy()) {
            $list = $list->sort($sort);
        }
        
        // Limit Data List:
        
        if ($limit = $this->getLimit()) {
            $list = $list->limit($limit);
        }
        
        // Define Results:
        
        foreach ($list as $record) {
            $data['results'][] = $this->getResultData($record);
        }
        
        // Answer JSON Response:
        
        return $this->respond($data);
    }
    
    /**
     * Answers an array of search filters for the given term.
     *
     * @param string $term
     *
     * @return array
     */
    public function getSearchFilters($term)
    {
        $filters = [];
        
        foreach ($this->getSearchFields() as $field) {
            $filters[$this->getSearchFilterName($field)] = $term;
        }
        
        return $filters;
    }
    
    /**
     * Answers the name of the search filter for the specified field.
     *
     * @param string $field
     *
     * @return string
     */
    public function getSearchFilterName($field)
    {
        return (strpos($field, ':') !== false) ? $field : sprintf('%s:PartialMatch', $field);
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
        parent::setValue($relation->column($this->getIDField()));
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
        $ids = [];
        
        if ($values = $this->getValueArray()) {
            $ids = $this->getList()->filter($this->getIDField(), $values)->getIDList();
        }
        
        $relation->setByIDList($ids);
    }
    
    /**
     * Answers true if the given data value and user value match (i.e. the value is selected).
     *
     * @param mixed $dataValue
     * @param mixed $userValue
     *
     * @return boolean
     */
    public function isSelectedValue($dataValue, $userValue)
    {
        if (is_array($userValue) && in_array($dataValue, $userValue)) {
            return true;
        }
        
        return parent::isSelectedValue($dataValue, $userValue);
    }
    
    /**
     * Answers the source array for the field options, including the empty string, if present.
     *
     * @return array
     */
    public function getSourceEmpty()
    {
        if (!$this->isAjaxEnabled()) {
            return parent::getSourceEmpty();
        } elseif ($this->getHasEmptyDefault()) {
            return ['' => $this->getEmptyString()];
        }
        
        return [];
    }
    
    /**
     * Answers the record identified by the given value.
     *
     * @param mixed $id
     *
     * @return ViewableData
     */
    protected function getValueRecord($id)
    {
        return $this->getList()->find($this->getIDField(), $id);
    }
    
    /**
     * Answers an HTTP response object with the given array of JSON data.
     *
     * @param array $data
     *
     * @return HTTPResponse
     */
    protected function respond($data = [])
    {
        return HTTPResponse::create(Convert::array2json($data))->addHeader('Content-Type', 'application/json');
    }
    
    /**
     * Answers the underlying list for the field.
     *
     * @return SS_List
     */
    protected function getList()
    {
        return DataList::create($this->dataClass);
    }
    
    /**
     * Answers a result data array for the given record object.
     *
     * @param ViewableData $record
     * @param boolean $selected
     *
     * @return array
     */
    protected function getResultData(ViewableData $record, $selected = false)
    {
        return [
            'id' => $record->{$this->getIDField()},
            'text' => $record->{$this->getTextField()},
            'formattedResult' => $this->getFormattedResult($record),
            'formattedSelection' => $this->getFormattedSelection($record),
            'selected' => $selected
        ];
    }
    
    /**
     * Answers a formatted result string for the given record object.
     *
     * @param ViewableData $record
     *
     * @return string
     */
    protected function getFormattedResult(ViewableData $record)
    {
        if ($format = $this->getFormatResult()) {
            return SSViewer::fromString($format)->process($record);
        }
    }
    
    /**
     * Answers a formatted selection string for the given record object.
     *
     * @param ViewableData $record
     *
     * @return string
     */
    protected function getFormattedSelection(ViewableData $record)
    {
        if ($format = $this->getFormatSelection()) {
            return SSViewer::fromString($format)->process($record);
        }
    }
    
    /**
     * Converts the given data source into an array.
     *
     * @param array|ArrayAccess $source
     *
     * @return array
     */
    protected function getListMap($source)
    {
        // Extract Map from ID / Text Fields:
        
        if ($source instanceof SS_List) {
            $source = $source->map($this->getIDField(), $this->getTextField());
        }
        
        // Convert Map to Array:
        
        if ($source instanceof Map) {
            $source = $source->toArray();
        }
        
        // Determine Invalid Types:
        
        if (!is_array($source) && !($source instanceof ArrayAccess)) {
            user_error('$source passed in as invalid type', E_USER_ERROR);
        }
        
        // Answer Data Source:
        
        return $source;
    }
    
    /**
     * Answers the field config for the receiver.
     *
     * @return array
     */
    protected function getFieldConfig()
    {
        $config = parent::getFieldConfig();
        
        if ($values = $this->getValueArray()) {
            
            $data = [];
            
            foreach ($values as $value) {
                
                if ($record = $this->getValueRecord($value)) {
                    $data[] = $this->getResultData($record, true);
                }
                
            }
            
            $config['data'] = $data;
            
        }
        
        return $config;
    }
    
    /**
     * Answers the field Ajax config for the receiver.
     *
     * @return array
     */
    protected function getFieldAjaxConfig()
    {
        $config = $this->getAjaxConfig();
        
        if (!isset($config['url'])) {
            $config['url'] = $this->Link('search');
        }
        
        return $config;
    }
}
