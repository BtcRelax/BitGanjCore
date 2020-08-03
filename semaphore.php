<?php
/**
 * Semaphore class
 *
 * Intended to lock a process till its end.
 * Use it under GPL.
 * Visit our home page at http://www.tortuga.pp.ru
 *
 * @author   Eugene Panin <varenich@yahoo.com>
 * @author   Mike Kopylov <cryatec@mail.ru>
 * @package  Semaphore
 * @access   public
 */
class Semaphore
{

  //-- Properties -----------------------------------------------------
    //-- Please access properties only by related methods ---------------

    /**
     * Path to lock file
     *
     * @var path string
     * @access private
     */
    public $path;

    /**
     * Process identity
     *
     * @var name string
     * @access private
     */
    public $name;

    /**
     * Lock file name
     *
     * @var name string
     * @access private
     */
    public $fname;

    /**
     *  Process timeout
     *
     * @var timeout integer
     * @access private
     */
    public $timeout;

    //------------------- Methods ----------------------------------------

    /**
     * Constructor creates object
     *
     * @param $name String Process identity
     * @param $path String Lock file place, default to /tmp
     * @access public
     */
    public function Semaphore($name, $path='/tmp')
    {
        $this->timeout = 5;
        $this->path = $path;
        $this->fname = md5($name) . ".sem.tmp";
    }


    /**
     * Is the process locked or not and if not then lock it
     *
     * @return boolean
     * @access public
     * @return void
     */
    public function lock()
    {
        $lf = $this->fname;
        $curDir = getcwd();
        chdir($this->path);
        if (file_exists($lf)) {
            $delta = time() - filectime($lf) - $this->timeout;
            if ($delta > 0) {
                $fp = fopen($lf, "w");
                fwrite($fp, "1");
                fclose($fp);
                chdir($curDir);
                return true;
            } else {
                chdir($curDir);
                return false;
            }
        } else {
            $fp = fopen($lf, "w");
            fwrite($fp, "1");
            fclose($fp);
            chdir($curDir);
            return true;
        }
    }

    /**
     * Unlocks process
     *
     * @access  public
     * @return void
     */
    public function unlock()
    {
        $lf = $this->fname;
        $curDir = getcwd();
        chdir($this->path);
        if (file_exists($lf)) {
            unlink($lf);
            chdir($curDir);
        } else {
            chdir($curDir);
            die("ERROR: Attempt to unlink nonexistent file!");
        }
    }

    /**
     * Sets lock timeout
     *
     * @param integer Timeout in seconds
     * @access  public
     * @return void
     */
    public function setTimeout($val)
    {
        $this->timeout = $val;
    }

    /**
     * Returns lock timeout
     *
     * @return integer Timeout in seconds
     * @access  public
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Sets process identity
     *
     * @param string Identity
     * @access  public
     * @return void
     */
    public function setName($val)
    {
        $this->name = $val;
    }

    /**
     * Returns process identity
     *
     * @return string Identity
     * @access  public
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets lock file place
     *
     * @param string
     * @access  public
     * @return void
     */
    public function setPath($val)
    {
        $this->path = $val;
    }

    /**
     * Returns lock file place
     *
     * @return string Path to lock file directory
     * @access  public
     */

    public function getPath()
    {
        return $this->path;
    }
}
