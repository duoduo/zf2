<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Action
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\PDF\Action;
use Zend\PDF\InternalType;
use Zend\PDF;

/**
 * Abstract PDF action representation class
 *
 * @uses       Countable
 * @uses       RecursiveIterator
 * @uses       SplObjectStorage
 * @uses       \Zend\PDF\Action\GoToAction
 * @uses       \Zend\PDF\Action\GoTo3DView
 * @uses       \Zend\PDF\Action\GoToE
 * @uses       \Zend\PDF\Action\GoToR
 * @uses       \Zend\PDF\Action\Hide
 * @uses       \Zend\PDF\Action\ImportData
 * @uses       \Zend\PDF\Action\JavaScript
 * @uses       \Zend\PDF\Action\Launch
 * @uses       \Zend\PDF\Action\Movie
 * @uses       \Zend\PDF\Action\Named
 * @uses       \Zend\PDF\Action\Rendition
 * @uses       \Zend\PDF\Action\ResetForm
 * @uses       \Zend\PDF\Action\SetOCGState
 * @uses       \Zend\PDF\Action\Sound
 * @uses       \Zend\PDF\Action\SubmitForm
 * @uses       \Zend\PDF\Action\Thread
 * @uses       \Zend\PDF\Action\Trans
 * @uses       \Zend\PDF\Action\Unknown
 * @uses       \Zend\PDF\Action\URI
 * @uses       \Zend\PDF\InternalType\AbstractTypeObject
 * @uses       \Zend\PDF\InternalType\ArrayObject
 * @uses       \Zend\PDF\Exception
 * @uses       \Zend\PDF\InternalStructure\NavigationTarget
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Action
 * @subpackage Zend_PDF_Action
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class AbstractAction extends PDF\InternalStructure\NavigationTarget implements \RecursiveIterator, \Countable
{
    /**
     * Action dictionary
     *
     * @var   \Zend\PDF\InternalType\DictionaryObject
     *      | \Zend\PDF\InternalType\IndirectObject
     *      | \Zend\PDF\InternalType\IndirectObjectReference
     */
    protected $_actionDictionary;


    /**
     * An original list of chained actions
     *
     * @var array  Array of \Zend\PDF\Action\AbstractAction objects
     */
    protected $_originalNextList;

    /**
     * A list of next actions in actions tree (used for actions chaining)
     *
     * @var array  Array of \Zend\PDF\Action\AbstractAction objects
     */
    public $next = array();

    /**
     * Object constructor
     *
     * @param \Zend\PDF\InternalType\DictionaryObject $dictionary
     * @param SplObjectStorage      $processedActions  list of already processed action dictionaries,
     *                                                 used to avoid cyclic references
     * @throws \Zend\PDF\Exception
     */
    public function __construct(InternalType\AbstractTypeObject $dictionary, \SplObjectStorage $processedActions)
    {
        if ($dictionary->getType() != InternalType\AbstractTypeObject::TYPE_DICTIONARY) {
            throw new PDF\Exception('$dictionary mast be a direct or an indirect dictionary object.');
        }

        $this->_actionDictionary = $dictionary;

        if ($dictionary->Next !== null) {
            if ($dictionary->Next instanceof InternalType\DictionaryObject) {
                // Check if dictionary object is not already processed
                if (!$processedActions->contains($dictionary->Next)) {
                    $processedActions->attach($dictionary->Next);
                    $this->next[] = self::load($dictionary->Next, $processedActions);
                }
            } else if ($dictionary->Next instanceof InternalType\ArrayObject) {
                foreach ($dictionary->Next->items as $chainedActionDictionary) {
                    // Check if dictionary object is not already processed
                    if (!$processedActions->contains($chainedActionDictionary)) {
                        $processedActions->attach($chainedActionDictionary);
                        $this->next[] = self::load($chainedActionDictionary, $processedActions);
                    }
                }
            } else {
                throw new PDF\Exception('PDF Action dictionary Next entry must be a dictionary or an array.');
            }
        }

        $this->_originalNextList = $this->next;
    }

    /**
     * Load PDF action object using specified dictionary
     *
     * @internal
     * @param \Zend\PDF\InternalType\AbstractTypeObject $dictionary (It's actually Dictionary or Dictionary Object or Reference to a Dictionary Object)
     * @param SplObjectStorage $processedActions  list of already processed action dictionaries, used to avoid cyclic references
     * @return \Zend\PDF\Action\AbstractAction
     * @throws \Zend\PDF\Exception
     */
    public static function load(InternalType\AbstractTypeObject $dictionary, \SplObjectStorage $processedActions = null)
    {
        if ($processedActions === null) {
            $processedActions = new \SplObjectStorage();
        }

        if ($dictionary->getType() != InternalType\AbstractTypeObject::TYPE_DICTIONARY) {
            throw new PDF\Exception('$dictionary mast be a direct or an indirect dictionary object.');
        }
        if (isset($dictionary->Type)  &&  $dictionary->Type->value != 'Action') {
            throw new PDF\Exception('Action dictionary Type entry must be set to \'Action\'.');
        }

        if ($dictionary->S === null) {
            throw new PDF\Exception('Action dictionary must contain S entry');
        }

        switch ($dictionary->S->value) {
            case 'GoTo':
                return new GoToAction($dictionary, $processedActions);
                brake;

            case 'GoToR':
                return new GoToR($dictionary, $processedActions);
                brake;

            case 'GoToE':
                return new GoToE($dictionary, $processedActions);
                brake;

            case 'Launch':
                return new Launch($dictionary, $processedActions);
                brake;

            case 'Thread':
                return new Thread($dictionary, $processedActions);
                brake;

            case 'URI':
                return new URI($dictionary, $processedActions);
                brake;

            case 'Sound':
                return new Sound($dictionary, $processedActions);
                brake;

            case 'Movie':
                return new Movie($dictionary, $processedActions);
                brake;

            case 'Hide':
                return new Hide($dictionary, $processedActions);
                brake;

            case 'Named':
                return new Named($dictionary, $processedActions);
                brake;

            case 'SubmitForm':
                return new SubmitForm($dictionary, $processedActions);
                brake;

            case 'ResetForm':
                return new ResetForm($dictionary, $processedActions);
                brake;

            case 'ImportData':
                return new ImportData($dictionary, $processedActions);
                brake;

            case 'JavaScript':
                return new JavaScript($dictionary, $processedActions);
                brake;

            case 'SetOCGState':
                return new SetOCGState($dictionary, $processedActions);
                brake;

            case 'Rendition':
                return new Rendition($dictionary, $processedActions);
                brake;

            case 'Trans':
                return new Trans($dictionary, $processedActions);
                brake;

            case 'GoTo3DView':
                return new GoTo3DView($dictionary, $processedActions);
                brake;

            default:
                return new Unknown($dictionary, $processedActions);
                brake;
        }
    }

    /**
     * Get resource
     *
     * @internal
     * @return \Zend\PDF\InternalType\AbstractTypeObject
     */
    public function getResource()
    {
        return $this->_actionDictionary;
    }

    /**
     * Dump Action and its child actions into PDF structures
     *
     * Returns dictionary indirect object or reference
     *
     * @internal
     * @param \Zend\PDF\ObjectFactory\ObjectFactory $factory   Object factory for newly created indirect objects
     * @param SplObjectStorage $processedActions  list of already processed actions
     *                                            (used to prevent infinity loop caused by cyclic references)
     * @return \Zend\PDF\InternalType\IndirectObject|\Zend\PDF\InternalType\IndirectObjectReference
     */
    public function dumpAction(PDF\ObjectFactory\ObjectFactoryInterface $factory, \SplObjectStorage $processedActions = null)
    {
        if ($processedActions === null) {
            $processedActions = new \SplObjectStorage();
        }
        if ($processedActions->contains($this)) {
            throw new PDF\Exception('Action chain cyclyc reference is detected.');
        }
        $processedActions->attach($this);

        $childListUpdated = false;
        if (count($this->_originalNextList) != count($this->next)) {
            // If original and current children arrays have different size then children list was updated
            $childListUpdated = true;
        } else if ( !(array_keys($this->_originalNextList) === array_keys($this->next)) ) {
            // If original and current children arrays have different keys (with a glance to an order) then children list was updated
            $childListUpdated = true;
        } else {
            foreach ($this->next as $key => $childAction) {
                if ($this->_originalNextList[$key] !== $childAction) {
                    $childListUpdated = true;
                    break;
                }
            }
        }

        if ($childListUpdated) {
            $this->_actionDictionary->touch();
            switch (count($this->next)) {
                case 0:
                    $this->_actionDictionary->Next = null;
                    break;

                case 1:
                    $child = reset($this->next);
                    $this->_actionDictionary->Next = $child->dumpAction($factory, $processedActions);
                    break;

                default:
                    $pdfChildArray = new InternalType\ArrayObject();
                    foreach ($this->next as $child) {

                        $pdfChildArray->items[] = $child->dumpAction($factory, $processedActions);
                    }
                    $this->_actionDictionary->Next = $pdfChildArray;
                    break;
            }
        } else {
            foreach ($this->next as $child) {
                $child->dumpAction($factory, $processedActions);
            }
        }

        if ($this->_actionDictionary instanceof InternalType\DictionaryObject) {
            // It's a newly created action. Register it within object factory and return indirect object
            return $factory->newObject($this->_actionDictionary);
        } else {
            // It's a loaded object
            return $this->_actionDictionary;
        }
    }


    ////////////////////////////////////////////////////////////////////////
    //  RecursiveIterator interface methods
    //////////////

    /**
     * Returns current child action.
     *
     * @return \Zend\PDF\Action\AbstractAction
     */
    public function current()
    {
        return current($this->next);
    }

    /**
     * Returns current iterator key
     *
     * @return integer
     */
    public function key()
    {
        return key($this->next);
    }

    /**
     * Go to next child
     */
    public function next()
    {
        return next($this->next);
    }

    /**
     * Rewind children
     */
    public function rewind()
    {
        return reset($this->next);
    }

    /**
     * Check if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return current($this->next) !== false;
    }

    /**
     * Returns the child action.
     *
     * @return \Zend\PDF\Action\AbstractAction|null
     */
    public function getChildren()
    {
        return current($this->next);
    }

    /**
     * Implements RecursiveIterator interface.
     *
     * @return bool  whether container has any pages
     */
    public function hasChildren()
    {
        return count($this->next) > 0;
    }


    ////////////////////////////////////////////////////////////////////////
    //  Countable interface methods
    //////////////

    /**
     * count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->childOutlines);
    }
}
