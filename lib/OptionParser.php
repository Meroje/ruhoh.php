<?php

/**
 * Parse cli opts
 *
 * @package default
 * @author Jerome Foray
 **/
class OptionParser
{

    /**
     * banner
     *
     * @var banner string
     **/
    public $banner = '';

    /**
     * Store options and messages
     *
     * @var options array
     **/
    private $options = array();

    /**
     * Store parsed options
     *
     * @var params array
     **/
    private $params = array();

    /**
     * Register a handler for option
     *
     * @return $this
     **/
    public function on($option, $longopt, $message, $callback = null)
    {
        $option_store = array(
        'option' => '',
        'longopt' => '',
        'message' => '',
        'callback' => function($arg) {
            return null;
        }
        );

        if (substr($option, 0, 1) == '-' && substr($option, 0, 2) != '--') {
            $option_store['option'] = $option;
        } elseif (substr($option, 0, 2) == '--') {
            $option_store['longopt'] = $option;
        }

        if (!empty($message) and substr($longopt, 0, 2) == '--') {
            $option_store['longopt'] = $longopt;
        } else {
            $callback = $message;
            $message  = $longopt;
        }

        $option_store['message']  = ($message)  ?: $option_store['message'];
        $option_store['callback'] = ($callback) ?: $option_store['callback'];

        $this->options[] = $option_store;

        return $this;
    }

    /**
    * Parse options and populate data
    *
    * @return void
    * @todo handle [no-] in longopts
    **/
    public function parse()
    {
        $options = '';
        $longopts = array();
        array_walk(
            $this->options,
            function($input, $key) use(&$options, &$longopts) {
                $options .= substr($input['option'], 1);
                if (!empty($input['longopt'])) {
                    $longopt = explode(' ', substr($input['longopt'], 2));
                    $longopts[] = $longopt[0];
                }
            }
        );
        $this->params = getopt($options, $longopts);
    }

    /**
     * Compile help message
     *
     * @return string
     **/
    public function help()
    {
        $out  = '';
        $out .= $this->banner . "\n";
        foreach ($this->options as $o) {
            $o['option'] = str_replace(':', '', $o['option']);
            $o['option'] = !empty($o['option']) ? $o['option'] . ", " : '    ';
            $o['longopt'] = str_replace(':', '', $o['longopt']);
            $o['longopt'] = !empty($o['longopt']) ? $o['longopt'] : '                             ';
            $spacer = 29 - strlen($o['longopt']);
            $spacer = ($spacer <= 0) ? 1 : $spacer++;

            $out .= '    ';
            $out .= $o['option'];
            $out .= $o['longopt'];
            $out .= str_repeat(' ', $spacer);
            $out .= $o['message'];
            $out .= "\n";
        }
        return $out;
    }
}

