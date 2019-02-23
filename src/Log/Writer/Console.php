<?php

namespace Parable\Log\Writer;

class Console implements WriterInterface
{
    /** @var  */
    protected $file;

    /**
     * Write the message to STDOUT.
     *
     * @param string $message
     *
     * @return $this
     */
    public function write($message)
    {
        echo $message . PHP_EOL;
        return $this;
    }
}
