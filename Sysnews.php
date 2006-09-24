<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Sysnews and associate classes.
 *
 * This file contains the Sysnews and SysnewsItem classes. Sysnews provides an
 * interface which performs roughly the same tasks as the sysnews unix tool. It
 * reads a directory which contains files. Each file represents a news item.
 * Various ways to represent news are provided by the Sysnews class. Each
 * individual news item is represented by an instance of SysnewsItem.
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   System
 * @package    Sysnews
 * @author     Ferry Boender <f.boender@electricmonk.nl>
 * @copyright  2006 Ferry Boender
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://www.electricmonk.nl/PEAR/package/Sysnews
 */

/**
 * The controlling class.
 *
 * This class provides the basic controlling mechanism for access sysnews style
 * news articles.
 *
 * @category   System
 * @package    Sysnews
 * @author     Ferry Boender <f.boender@electricmonk.nl>
 * @copyright  2006 Ferry Boender
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://www.electricmonk.nl/PEAR/package/Sysnews
 */
class Sysnews {
    
    /**
     * The news items present in the given path.
     *
     * @var array
     */
    private $items = array();

    public function __construct($path) 
    {
        $this->load($path);
        $this->sort(SORT_ASC);
    }

    protected function load($path) 
    {
        $news = array();
        if (!$d = @opendir($path)) {
            throw new Exception("Can't open '$path' for reading");
        }
        while ($f = readdir($d)) {
            if (strpos($f, '.') !== 0) {
                $this->items[] = new SysnewsItem($path, $f);
            }
        }
    }

    public function sort($direction) 
    {
        if ($direction == SORT_ASC) {
            $cmpRet = "-1 : 1";
        } else {
            $cmpRet = "1 : -1";
        }
        $cmp = create_function('$a,$b', '
            if ($a->date == $b->date) { 
                return (0); 
            }; 
            return($a->date > $b->date ? '.$cmpRet.');
        ');
        usort($this->items, $cmp);
    }

    public function getItems() 
    {
        return $this->items;
    }

    public function getLastItems($count) 
    {
        $this->sort(SORT_ASC);
        $retItems = array();
        if ($count >= count($this->items)) {
            $retItems = &$this->items;
        } else {
            for ($i = 0; $i != $count; $i++) {
                $retItems[] = &$this->items[$i];
            }
        }
        return $retItems;
    }
}

class SysnewsItem {
    public $date = null;
    public $title = null;
    public $contents = null;

    public function __construct($path, $title) 
    {
        $this->load($path, $title);
    }

    protected function load($path, $title) 
    {
        /* Date */
        if (!$mtime = @filemtime($path.'/'.$title)) {
            throw new Exception("Couldn't get mtime for '$f'");
        }
        $this->date = $mtime;
    
        /* Title */
        $this->title = $title;

        /* Contents */
        if (!$contents = file_get_contents($path.'/'.$title)) {
            throw new Exception("Couldn't read news for '$f'");
        }
        $this->contents = $contents;
    }
}

?>
