<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-09
 * Time: 上午 10:42
 */

namespace Http\Message;


use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{

    private $stream;
    private $size;
    private $uri;
    private $seekable;
    private $writable;
    private $readable;
    private $metaData;

    /** @var array Hash of readable and writable stream types */
    private static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
        ]
    ];

    /**
     * Stream constructor.
     * @param resource $stream
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)){
            throw new \InvalidArgumentException('Input stream must be a resource');
        }
        $this->stream = $stream;
        $this->metaData = stream_get_meta_data($stream);
        $this->uri = $this->metaData['uri'];
        $this->seekable = boolval($this->metaData['seekable']);
        $this->readable = isset(self::$readWriteHash['read'][$this->metaData['mode']]);
        $this->writable = isset(self::$readWriteHash['write'][$this->metaData['mode']]);
        $stat = fstat($stream);
        $this->size = (int)$stat['size'];
    }

    /**
     * close stream
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->stream)){
            return '';
        }

        try{
            $this->rewind();
            return $this->getContents();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->stream);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if (!isset($this->stream)) {
            return null;
        }
        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        if (!is_null($this->stream)){
            return ftell($this->stream);
        }else{
            throw new \RuntimeException("Can' t read position from nul resource");
        }
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return is_null($this->stream) || feof($this->stream);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return !is_null($this->stream) && $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable){
            throw new \RuntimeException('The resource is not seekable');
        }
        if(fseek($this->stream, $offset, $whence) === -1){
            throw new \RuntimeException(var_export($whence, true));
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if ($this->isSeekable()){
            $this->seek(0);
        }else{
            throw new \RuntimeException('Seek failure');
        }
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return !is_null($this->stream) && $this->writable;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if (is_null($this->stream || $this->isWritable())){
            throw new \RuntimeException("Can't write  resource");
        }else{
            $this->size = null;
            $result = fwrite($this->stream, $string);
            if ($result === false){
                throw new \RuntimeException("Can't write it");
            }
            return $result;
        }
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return !is_null($this->stream) && $this->readable;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if (is_null($this->stream || $this->isReadable())){
            throw new \RuntimeException("Can't read resource");
        }else{
            if ($this->eof()){
                return '';
            }
            $this->size = null;
            $result = fread($this->stream, $length);
            if ($result === false){
                throw new \RuntimeException("Can't read resource");
            }
            return $result;
        }
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (is_null($this->stream)){
            return null;
        }
        if (!isset($this->metaData)){
            $this->metaData = stream_get_meta_data($this->stream);
        }

        if($key === null){
            return $this->metaData;
        }else{
            return isset($this->metaData[$key])? $this->metaData[$key]: null;
        }
    }
}