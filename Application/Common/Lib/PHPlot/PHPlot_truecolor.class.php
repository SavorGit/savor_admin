<?php
namespace Common\Lib\PHPlot;
use \Common\Lib\PHPlot\PHPlot;
class PHPlot_truecolor extends PHPlot
{
    /**
     * Constructor: Sets up GD truecolor image resource, and initializes plot style controls
     *
     * @param int $width  Image width in pixels
     * @param int $height  Image height in pixels
     * @param string $output_file  Path for output file. Omit, or NULL, or '' to mean no output file
     * @param string $input_file   Path to a file to be used as background. Omit, NULL, or '' for none
     */
    function __construct($width=600, $height=400, $output_file=NULL, $input_file=NULL)
    {
        $this->initialize('imagecreatetruecolor', $width, $height, $output_file, $input_file);
    }
}