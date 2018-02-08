<?php

/**
 * class Output.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class Output
{
    /**
     * @var bool
     */
    protected $isVerbose;

    /*
     * Constructor.
     *
     * @param bool $isVerbose
     */
    public function __construct(bool $isVerbose)
    {
        $this->isVerbose = $isVerbose;
    }
    /*
     * Writes a message.
     *
     * @param string $message
     * @param bool $isError
     *
     * @return Output
     */
    public function write(string $message, $isError = false)
    {
        return $this->doWrite($message, false, $isError);
    }

    /*
     * Writes a message with new line.
     *
     * @param string $message
     * @param bool $isError
     *
     * @return Output
     */
    public function writeln(string $message, $isError = false)
    {
        return $this->doWrite($message, true, $isError);
    }

    /*
     * Writes a message.
     *
     * @param string $message
     * @param bool $newLine
     * @param bool $isError
     *
     * @return Output
     */
    public function doWrite(string $message, bool $newLine, bool $isError)
    {
        if (!$this->isVerbose) {
            return $this;
        }

        $output = $isError ? 'php://stdin' : 'php://stderr';

        if ($newLine) {
            $message .= "\n";
        }

        file_put_contents($output, $message);

        return $this;
    }
}
