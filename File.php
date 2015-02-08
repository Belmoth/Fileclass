<?php
namespace Core;
/**
 * Class for work with File
 *
 * @author Sasha Bichkov
 */

  class File 
  {
    private $file;

    /**
     *  Define file name
     *
     * @param string $file
     */

    function __construct($file) 
    {
      if (file_exists($file)) {
        if (!is_dir( $this->file)) {
          $this->file = $file;
        } else {
          throw new Exception('This is not a file');
        }
      } else {
        throw new Exception('File not found');
      }
    }

    /**
     * Return file extension
     *
     * @return string
     */

    public function getExtension()
    {
      $arr = explode('.', $this->file);
      $len = count( $arr );

      $ext = $arr[ $len - 1 ];

      return $ext;
    }

    /**
     * Return file path
     *
     * @return string
     */

    public function getPath()
    {
      return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->file;
    }

    /**
     * Return file name
     *
     * @return string
     */

    public function getName()
    {
      $name = explode('.', $this->file)[ 0 ];
      return $name;
    }

    /**
     * Return file size
     *
     * @return int
     */

    public function getSize( $in )
    {
      $in = strtolower( $in );

      switch ( $in ) {
        case 'kb': 
          return $this->bytesInKB();
          break;

        case 'mb':
          return $this->bytesInMB();
          break;

        case 'gb': 
          return $this->bytesInGB();
          break;

        default: $this->bytes();
      }
    }

    /**
     * Move file from dir
     *
     * @param string $path
     */

    public function move( $path ) 
    {
      createDir( $dir );

      $new_file = $path . DIRECTORY_SEPARATOR . $this->getName();

      $this->copyTo( $new_file );
      $this->destroy();

      $this->file = $new_file;
    }

    /**
     * Copy file in directory
     * We set new path /dir/dir/new_name
     *
     * @param string $new
     * @return bool
     */

    public function copyTo( $new )
    {
      $dir = explode('/', $new);

      unset($dir[ count($dir) -1 ]);

      if (count( $dir ) !== 0) 
        $this->createDir( $dir );
      
      $from = $this->file;
      $to   = $this->newPath( $new );

      return @copy($from, $to);
    }

    /**
     * Rename file
     *
     * @param string $new
     * @return bool
     */

    public function rename( $new )
    {
      $from = $this->file;
      $to   = $this->newPath( $new );

      if (!@rename($from, $to)) {
        throw new Exception("We can't rename the file");
      }

      return true;
    }

    /**
     * Destroy file
     *
     * @return bool
     */
    public function destroy()
    {
      if (@unlink($this->file)) {
        $file->name = NULL;
        return true;
      } else {
        throw new Exception("Can't delete the file");
      }
    }

    /**
     * is it image?
     *
     * @return bool
     */
    public function isImage()
    {
      if (preg_match('/png|gif|jpg/', $this->file)) {
        return true;
      }

      return false;
    }

    /**
     * is it text file?
     *
     * @return bool
     */
    public function isText()
    {
      if (preg_match('/txt|doc|rtf/', $this->file)) {
        return true;
      }

      return false;
    }

    /**
     * is it archive?
     *
     * @return bool
     */
    public function isArchive()
    {
      if (preg_match('/rar|zip/', $this->file)) {
        return true;
      }

      return false;
    }

    /**
     * Return file date
     *
     * For example
     * January 31 2015 09:31:09.
     *
     * @return string
     */
    public function getDate()
    {
      return date("F d Y H:i:s.", filemtime($this->file));
    }

    /**
     * Return file owner perms
     *
     * @return string
     */
    public function getOwnerPerms()
    {
      $perms = fileperms($this->file);

      $info  = '';
      $info .= (($perms & 0x0100) ? 'r' : '-');
      $info .= (($perms & 0x0080) ? 'w' : '-');
      $info .= (($perms & 0x0040) ?
                  (($perms & 0x0800) ? 's' : 'x' ) :
                  (($perms & 0x0800) ? 'S' : '-'));

      return $info;
    }

    /**
     * Return file group perms
     * Result is 'rwx'
     *
     * @return string
     */
    public function getGroupPerms()
    {
      $perms = fileperms($this->file);

      $info  = '';
      $info .= (($perms & 0x0020) ? 'r' : '-');
      $info .= (($perms & 0x0010) ? 'w' : '-');
      $info .= (($perms & 0x0008) ?
                  (($perms & 0x0400) ? 's' : 'x' ) :
                  (($perms & 0x0400) ? 'S' : '-'));

      return $info;
    }

    /**
     * Return file user perms
     *
     * @return string
     */
    public function getUserPerms()
    {
      $perms = fileperms($this->file);

      $info  = '';
      $info .= (($perms & 0x0004) ? 'r' : '-');
      $info .= (($perms & 0x0002) ? 'w' : '-');
      $info .= (($perms & 0x0001) ?
                  (($perms & 0x0200) ? 't' : 'x' ) :
                  (($perms & 0x0200) ? 'T' : '-'));
      
      return $info;
    }

    /**
     * Return file perms as
     * 0777 or 'rwx|rwx|rwx'
     *
     * @return mixed
     */
    public function getPerms( $hex = true )
    {
      $perms = '';

      if ( true === $hex ) {
        $perms = substr(sprintf('%o', fileperms($this->file)), -4);
      } else {
        $perms = $perms
          . $this->getOwnerPerms() . '|'
          . $this->getGroupPerms() . '|'
          . $this->getUserPerms();
      }
  
      return $perms;
    }

    /**
     * Is the file writable?
     *
     * @return bool
     */
    
    public function isWritable()
    {
      return is_writable( $this->file );
    }

    /**
     * Is the file executable?
     *
     * @return bool
     */

    public function isExec()
    {
      return is_executable( $this->file );
    }

    /**
     * Is the file readable?
     *
     * @return bool
     */

    public function isReadable()
    {
      return is_readable( $this->file );
    }

    /**
     * Return file size in kilobytes
     *
     * @return int
     */

    private function bytesInKB()
    {
      return filesize( $this->file ) / 1024;
    }
    
    /**
     * Return file size in megabytes
     *
     * @return int
     */

    private function bytesInMB()
    {
      return $this->bytesInKB() / 1024;
    }

    /**
     * Return file size in gigabytes
     *
     * @return int
     */

    private function bytesInGB()
    {
      return $this->bytesInMB() / 1024;
    }

    /**
     * Return file size in bytes
     *
     * @return int
     */

    private function bytes()
    {
      return filesize( $this->file );
    }

    /**
     * Create directory with subdirectories
     *
     * @param string $path
     */

    private function createDir( $path )
    {
      $len = count( $path );
      $npath = '';

      $i = 0;
      while ( $i < $len ) {
        $npath .= $path[ $i ] . DIRECTORY_SEPARATOR;
        
        if (!file_exists($npath)) {
          @mkdir($npath);
        }

        $i++;
      }
    }

    /**
     * Return new path
     *
     * @param string $name
     * @return string
     */

    private function newPath( $name ) 
    {
      $ext = $this->getExtension();
      $new = $name . '.' . $ext;

      return $new;
    }
  }