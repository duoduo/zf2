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
 * @subpackage Zend_PDF_Destination
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\PDF\Destination;
use Zend\PDF\InternalType;
use Zend\PDF;

/**
 * \Zend\PDF\Destination\FitBoundingBoxHorizontally explicit detination
 *
 * Destination array: [page /FitBH top]
 *
 * (PDF 1.1) Display the page designated by page, with the vertical coordinate
 * top positioned at the top edge of the window and the contents of the page
 * magnified just enough to fit the entire width of its bounding box within the
 * window.
 *
 * @uses       \Zend\PDF\Destination\Explicit
 * @uses       \Zend\PDF\InternalType\ArrayObject
 * @uses       \Zend\PDF\InternalType\NameObject
 * @uses       \Zend\PDF\InternalType\NumericObject
 * @uses       \Zend\PDF\Exception
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Destination
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class FitBoundingBoxHorizontally extends Explicit
{
    /**
     * Create destination object
     *
     * @param \Zend\PDF\Page|integer $page  Page object or page number
     * @param float $top   Top edge of displayed page
     * @return \Zend\PDF\Destination\FitBoundingBoxHorizontally
     * @throws \Zend\PDF\Exception
     */
    public static function create($page, $top)
    {
        $destinationArray = new InternalType\ArrayObject();

        if ($page instanceof PDF\Page) {
            $destinationArray->items[] = $page->getPageDictionary();
        } else if (is_integer($page)) {
            $destinationArray->items[] = new InternalType\NumericObject($page);
        } else {
            throw new PDF\Exception('Page entry must be a Zend_PDF_Page object or a page number.');
        }

        $destinationArray->items[] = new InternalType\NameObject('FitBH');
        $destinationArray->items[] = new InternalType\NumericObject($top);

        return new self($destinationArray);
    }

    /**
     * Get top edge of the displayed page
     *
     * @return float
     */
    public function getTopEdge()
    {
        return $this->_destinationArray->items[2]->value;
    }

    /**
     * Set top edge of the displayed page
     *
     * @param float $top
     * @return Zend_PDF_Action_FitBoundingBoxHorizontally
     */
    public function setTopEdge($top)
    {
        $this->_destinationArray->items[2] = new InternalType\NumericObject($top);
        return $this;
    }
}
