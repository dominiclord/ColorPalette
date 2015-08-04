<?php
/**
* Generate a randomized color palette
* @package Utils
* @see http://stackoverflow.com/a/43235
* @see http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
*/
class ColorPalette
{
    private $base_r;
    private $base_g;
    private $base_b;

    private $catalogue = [];
    private $modifier;
    private $options;

    public $profiler = [
        'difference'        => '',
        'color_checks'      => 0,
        'proximity_checks'  => 0,
        'proximity_value'   => 0
    ];

    /**
    * Stock the base color and options
    * @param   mixed   $color     Base color can be a string (hex) or an array (rgb)
    * @param   array   $options   General options for the generator (optional)
    */
    public function __construct($color, $options = [])
    {
        // Default options
        $this->options = [
            'avoid_proximity'      => true,
            'proximity_history'    => 2,
            'proximity_max_checks' => 20,
            'proximity_tolerance'  => 10,
            'return_format'        => 'hex',
            'variance'             => 50
        ];

        if (is_array($options) && !empty($options)) {
            $this->options = array_replace_recursive($this->options, $options);
        }

        // Check what type of color we're working with
        // Some checks on each array index for ints would be good too
        if (is_array($color) && !empty($color) && count($color) === 3) {
            $rgb = $color;

        // We need better checks for hex
        } elseif (is_string($color) && !empty($color)) {
            $rgb = $this->hex2rgb($color);

        // Default color will be grey, but we should really return an error
        } else {
            $rgb = [127,127,127];
        }

        $this->base_r = $rgb[0];
        $this->base_g = $rgb[1];
        $this->base_b = $rgb[2];
    }

    /**
    * Convert a hexadecimal color to RGB
    * @param    string   $hex Hex color code
    * @return   array
    */
    public function hex2rgb($hex) {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }

        return [$r, $g, $b];
    }

    /**
    * Convert an RGB color to hexadecimal
    * @param    array   $rgb   An array with rgb data
    * @return   string
    */
    public function rgb2hex($rgb)
    {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex;
    }

    /**
    * Proximity needs to be checked, refers to catalogue for validation.
    * A variance of 50 is good for this feature.
    * @param    array     $rgb RGB values
    * @return   booleam
    */
    private function check_proximity($rgb)
    {
        $proximity = false;

        // We only check the most recently generated colors as a catalogue could get quite large
        $colors_to_check = array_slice($this->catalogue, ((int)$this->options['proximity_history'] * -1));

        $r = $rgb[0];
        $g = $rgb[1];
        $b = $rgb[2];

        foreach ($colors_to_check as $color) {

            $this->profiler['color_checks']++;

            // We can validate with $r on its own since all channels were modified with the same variance
            $difference = abs($r - $color[0]);
            $this->profiler['difference'] .= $difference . ',';

            // Colors will be much closer the lower you go
            if ($difference < $this->options['proximity_tolerance'] ) {
                $proximity = true;
            }
        }

        $this->profiler['difference'] .= '--';

        $this->profiler['proximity_value'] = $proximity ? 'true' : 'false';

        return $proximity;
    }

    /**
    * Generate a random variance for each color channel
    * @return   mixed
    */
    private function generate_colors()
    {
        $variance = rand(0, $this->options['variance']);

        // Switch operator on the variance
        if ($this->options['avoid_proximity']) {
            $this->modifier++;
            $variance = $variance * ( ( $this->modifier % 2 ) ? 1 : -1 );
        }

        $r = max(0, min(255, $this->base_r + $variance));
        $g = max(0, min(255, $this->base_g + $variance));
        $b = max(0, min(255, $this->base_b + $variance));

        return [$r, $g, $b];
    }

    /**
    * Render a new color according to a degree of variance and proximity avoidance
    * @return   mixed
    */
    public function render()
    {
        if ($this->options['avoid_proximity']) {

            // Reset modifier
            $this->modifier = 0;

            $counter    = 0;
            $max_checks = $this->options['proximity_max_checks'];

            $this->profiler['proximity_checks'] = 0;
            $this->profiler['color_checks'] = 0;
            $this->profiler['difference'] = '';

            while ($max_checks > $counter) {

                $rgb = $this->generate_colors();

                $this->profiler['proximity_checks']++;

                if (!$this->check_proximity($rgb)) {
                    break;
                }

                $counter ++;
            }

        } else {
            $rgb = $this->generate_colors();
        }


        // After proximity testing, we can now save our color in the catalogue
        $this->catalogue[] = $rgb;

        return ($this->options['return_format'] === 'hex') ? $this->rgb2hex($rgb) : $rgb;
    }
}