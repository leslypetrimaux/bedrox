<?php

namespace Bedrox\Yaml;

use Bedrox\Core\Response;
use Exception;
use RuntimeException;

class YamlParser
{
    // SETTINGS
    public const REMPTY = "\0\0\0\0\0";

    /**
     * Setting this to true will force YAMLDump to enclose any string value in
     * quotes.  False by default.
     *
     * @var bool
     */
    public $setting_dump_force_quotes = false;

    /**
     * Setting this to true will forse YAMLLoad to use syck_load function when
     * possible. False by default.
     * @var bool
     */
    public $setting_use_syck_is_possible = false;

    /**#@+
     * @access private
     * @var mixed
     */
    private $_dumpIndent;
    private $_dumpWordWrap;
    private $_containsGroupAnchor = false;
    private $_containsGroupAlias = false;
    private $path;
    private $result;
    private $LiteralPlaceHolder = '___YAML_Literal_Block___';
    private $SavedGroups = array();
    private $indent;

    /**
     * Path modifier that should be applied after adding current element.
     * @var array
     */
    private $delayedPath = array();

    /**#@+
     * @access public
     * @var mixed
     */
    public $_nodeId;

    /** @var Response */
    protected $response;

    /**
     * YamlParser constructor.
     */
    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * Load a valid YAML string to YamlParser.
     * @param string $input
     * @return array
     */
    public function load ($input): array
    {
        return $this->_loadString($input);
    }

    /**
     * Load a valid YAML file to YamlParser.
     * @param string $file
     * @return array
     */
    public function loadFile ($file): array
    {
        return $this->_load($file);
    }

    /**
     * Load YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.  Pretty
     * simple.
     *  Usage:
     *  <code>
     *   $array = YamlParser::YAMLLoad('lucky.yaml');
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input Path of YAML file or string containing YAML
     */
    public static function YAMLLoad($input): array
    {
        $YamlParser = new self;
        return $YamlParser->_load($input);
    }

    /**
     * Load a string of YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML string, will do its best
     * to convert YAML in a string into a PHP array.  Pretty simple.
     *
     * Note: use this function if you don't want files from the file system
     * loaded and processed as YAML.  This is of interest to people concerned
     * about security whose input is from a string.
     *
     *  Usage:
     *  <code>
     *   $array = YamlParser::YAMLLoadString("---\n0: hello world\n");
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input String containing YAML
     */
    public static function YAMLLoadString($input): array
    {
        $YamlParser = new self;
        return $YamlParser->_loadString($input);
    }

    /**
     * Dump YAML from PHP array statically
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as nothing.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @param array|\stdClass $array PHP array
     * @param bool $indent
     * @param bool $wordwrap
     * @param bool $no_opening_dashes
     * @return string
     */
    public static function YAMLDump($array, $indent = false, $wordwrap = false, $no_opening_dashes = false): string
    {
        $YamlParser = new self;
        return $YamlParser->dump($array, $indent, $wordwrap, $no_opening_dashes);
    }

    /**
     * Dump PHP array to YAML
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as tasteful.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @param array $array
     * @param bool $indent
     * @param bool $wordwrap
     * @param bool $no_opening_dashes
     * @return string
     */
    public function dump($array,$indent = false,$wordwrap = false, $no_opening_dashes = false): string
    {
        // Dumps to some very clean YAML.  We'll have to add some more features
        // and options soon.  And better support for folding.
        // New features and options.
        $this->_dumpIndent = $indent !== false  ? 2 : $indent;
        $this->_dumpWordWrap = $wordwrap !== false ? 40 : $wordwrap;
        // New YAML document
        $string = '';
        if (!$no_opening_dashes) {
            $string = "---\n";
        }
        // Start at the base of the array and move through it.
        if ($array) {
            $array = (array)$array;
            foreach ($array as $key => $value) {
                if (!isset($first_key)) {
                    $first_key = $key;
                }
                $string .= $this->_yamlize($key, $value, false, $array);
            }
        }
        return $string;
    }

    /**
     * Attempts to convert a key / value array item to YAML
     * @access private
     * @param mixed $key
     * @param $value
     * @param mixed $indent
     * @param null $source_array
     * @return string
     */
    private function _yamlize($key, $value, $indent, $source_array = null): string
    {
        if(is_object($value)) {
            $value = (array)$value;
        }
        if (is_array($value)) {
            if (empty ($value)) {
                return $this->_dumpNode($key, array(), $indent, $source_array);
            }
            // It has children.  What to do?
            // Make it the right kind of item
            $string = $this->_dumpNode($key, self::REMPTY, $indent, $source_array);
            // Add the indent
            $indent += $this->_dumpIndent;
            // Yamlize the array
            $string .= $this->_yamlizeArray($value,$indent);
        } else {
            // It doesn't have children.  Yip.
            $string = $this->_dumpNode($key, $value, $indent, $source_array);
        }
        return !empty($string) ? $string : '';
    }

    /**
     * Attempts to convert an array to YAML
     * @access private
     * @param $array
     * @param $indent
     * @return string|null
     */
    private function _yamlizeArray($array,$indent): ?string
    {
        if (is_array($array)) {
            $string = '';
            foreach ($array as $key => $value) {
                if (!isset($first_key)) {
                    $first_key = $key;
                }
                $string .= $this->_yamlize($key, $value, $indent, $array);
            }
            return $string;
        }
        return false;
    }

    /**
     * Returns YAML from a key and a value
     * @access private
     * @param mixed $key
     * @param $value
     * @param mixed $indent
     * @param null $source_array
     * @return string
     */
    private function _dumpNode($key, $value, $indent, $source_array = null): string
    {
        // do some folding here, for blocks
        $value = is_string($value) && ((strpos($value, "\n") !== false || strpos($value, ': ') !== false || strpos($value, '- ') !== false ||
                strpos($value, '*') !== false || strpos($value, '#') !== false || strpos($value, '<') !== false || strpos($value, '>') !== false || strpos($value, '%') !== false || strpos($value, '  ') !== false ||
                strpos($value, '[') !== false || strpos($value, ']') !== false || strpos($value, '{') !== false || strpos($value, '}') !== false) || strpos($value, '&') !== false || strpos($value, "'") !== false || strpos($value, '!') === 0 ||
            $value[strlen($value) - 1] === ':') ? $this->_doLiteralBlock($value, $indent) : $this->_doFolding($value, $indent);
        if ($value === array()) {
            $value = '[ ]';
        }
        if ($value === '') {
            $value = '""';
        }
        if ($this->isTranslationWord($value)) {
            $value = $this->_doLiteralBlock($value, $indent);
        }
        if (trim ($value) !== $value) {
            $value = $this->_doLiteralBlock($value, $indent);
        }
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        if ($value === null) {
            $value = 'null';
        }
        if ($value === "'" . self::REMPTY . "'") {
            $value = null;
        }
        $spaces = str_repeat(' ',$indent);
        //if (is_int($key) && $key - 1 == $previous_key && $first_key===0) {
        if (is_array ($source_array) && array_keys($source_array) === range(0, count($source_array) - 1)) {
            // It's a sequence
            $string = $spaces.'- '.$value."\n";
        } else {
            // if ($first_key===0)  throw new Exception('Keys are all screwy.  The first one was zero, now it\'s "'. $key .'"');
            // It's mapped
            if (strpos($key, ':') !== false || strpos($key, '#') !== false) { $key = '"' . $key . '"'; }
            $string = rtrim ($spaces.$key.': '.$value)."\n";
        }
        return $string;
    }

    /**
     * Creates a literal block for dumping
     * @access private
     * @param $value
     * @param $indent
     * @return string
     */
    private function _doLiteralBlock($value,$indent): string
    {
        if ($value === "\n") {
            return '\n';
        }
        if (strpos($value, "\n") === false && strpos($value, "'") === false) {
            return sprintf ("'%s'", $value);
        }
        if (strpos($value, "\n") === false && strpos($value, '"') === false) {
            return sprintf ('"%s"', $value);
        }
        $exploded = explode("\n",$value);
        $newValue = '|';
        if (isset($exploded[0]) && ($exploded[0] === '|' || $exploded[0] === '|-' || $exploded[0] === '>')) {
            $newValue = $exploded[0];
            unset($exploded[0]);
        }
        $indent += $this->_dumpIndent;
        $spaces   = str_repeat(' ',$indent);
        foreach ($exploded as $line) {
            $line = trim($line);
            if ((strpos($line, '"') === 0 && strrpos($line, '"') === (strlen($line) - 1)) || (strpos($line, "'") === 0 && strrpos($line, "'") === (strlen($line) - 1))) {
                $line = substr($line, 1, -1);
            }
            $newValue .= "\n" . $spaces . $line;
        }
        return $newValue;
    }

    /**
     * Folds a string of text, if necessary
     * @access private
     * @param $value
     * @param $indent
     * @return string
     */
    private function _doFolding($value,$indent): string
    {
        // Don't do anything if wordwrap is set to 0
        if ($this->_dumpWordWrap !== 0 && is_string ($value) && strlen($value) > $this->_dumpWordWrap) {
            $indent += $this->_dumpIndent;
            $indent = str_repeat(' ',$indent);
            $wrapped = wordwrap($value,$this->_dumpWordWrap,"\n$indent");
            $value   = ">\n".$indent.$wrapped;
        } else if ($this->setting_dump_force_quotes && $value !== self::REMPTY) {
            $value = '"' . $value . '"';
        }
        return $value;
    }

    /**
     * @param $value
     * @return bool
     */
    private function isTrueWord($value): bool
    {
        $words = self::getTranslations(array('true', 'on', 'yes', 'y'));
        return in_array($value, $words, true);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isFalseWord($value): bool
    {
        $words = self::getTranslations(array('false', 'off', 'no', 'n'));
        return in_array($value, $words, true);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isNullWord($value): bool
    {
        $words = self::getTranslations(array('null', '~'));
        return in_array($value, $words, true);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isTranslationWord($value): bool
    {
        return (
            $this->isTrueWord($value)  ||
            $this->isFalseWord($value) ||
            $this->isNullWord($value)
        );
    }

    /**
     * Coerce a string into a native type
     * Reference: http://yaml.org/type/bool.html
     * @access private
     * @param $value
     */
    private function coerceValue(&$value): void
    {
        if ($this->isTrueWord($value)) {
            /** @var bool $value */
            $value = true;
        } else if ($this->isFalseWord($value)) {
            /** @var bool $value */
            $value = false;
        } else if ($this->isNullWord($value)) {
            /** @var null $value */
            $value = null;
        }
    }

    /**
     * Given a set of words, perform the appropriate translations on them to
     * match the YAML 1.1 specification for type coercing.
     * @param array $words The words to translate
     * @return array
     * @access private
     */
    private static function getTranslations(array $words): array
    {
        $result = [[]];
        foreach ($words as $i) {
            $result[] = array(ucfirst($i), strtoupper($i), strtolower($i));
        }
        $result = array_merge($result);
        return $result;
    }

    // LOADING FUNCTIONS

    /**
     * @param $input
     * @return array
     */
    private function _load($input): array
    {
        $Source = $this->loadFromSource($input);
        return $this->loadWithSource($Source);
    }

    /**
     * @param $input
     * @return array
     */
    private function _loadString($input): array
    {
        $Source = $this->loadFromString($input);
        return $this->loadWithSource($Source);
    }

    /**
     * @param $Source
     * @return array
     */
    private function loadWithSource($Source): array
    {
        if (empty ($Source)) {
            return array();
        }
        if ($this->setting_use_syck_is_possible && function_exists ('syck_load')) {
            $array = syck_load (implode ("\n", $Source));
            return is_array($array) ? $array : array();
        }
        $this->path = array();
        $this->result = array();
        $cnt = count($Source);
        foreach ($Source as $i => $iValue) {
            $line = $iValue;
            $this->indent = strlen($line) - strlen(ltrim($line));
            $tempPath = $this->getParentPathByIndent($this->indent);
            $line = self::stripIndent($line, $this->indent);
            if (self::isComment($line)) {
                continue;
            }
            if (self::isEmpty($line)) {
                continue;
            }
            $this->path = $tempPath;
            $literalBlockStyle = self::startsLiteralBlock($line);
            $literalBlock = '';
            if ($literalBlockStyle) {
                $line = rtrim ($line, $literalBlockStyle . " \n");
                $line .= ' '.$this->LiteralPlaceHolder;
                $literal_block_indent = strlen($Source[$i+1]) - strlen(ltrim($Source[$i+1]));
                while (++$i < $cnt && $this->literalBlockContinues($iValue, $this->indent)) {
                    $literalBlock = $this->addLiteralLine($literalBlock, $iValue, $literalBlockStyle, $literal_block_indent);
                }
                $i--;
            }
            // Strip out comments
            if (strpos ($line, '#')) {
                $line = preg_replace('/\s*#([^"\']+)$/','',$line);
            }
            while (++$i < $cnt && self::greedilyNeedNextLine($line)) {
                $line = rtrim ($line, " \n\t\r") . ' ' . ltrim ($iValue, " \t");
            }
            $lineArray = $this->_parseLine($line);
            if ($literalBlockStyle) {
                $lineArray = $this->revertLiteralPlaceHolder($lineArray, $literalBlock);
            }
            $this->addArray($lineArray, $this->indent);
            foreach ($this->delayedPath as $indent => $delayedPath) {
                $this->path[$indent] = $delayedPath;
            }
            $this->delayedPath = array();
        }
        return $this->result;
    }

    /**
     * @param $input
     * @return array
     */
    private function loadFromSource ($input): array
    {
        if (!empty($input) && strpos($input, "\n") === false && file_exists($input)) {
            $input = file_get_contents($input);
        }
        return $this->loadFromString($input);
    }

    /**
     * @param $input
     * @return array
     */
    private function loadFromString ($input): array
    {
        $lines = explode("\n",$input);
        foreach ($lines as $k => $_) {
            $lines[$k] = rtrim ($_, "\r");
        }
        return $lines;
    }

    /**
     * Parses YAML code and returns an array for a node
     * @access private
     * @return array
     * @param string $line A line from the YAML file
     */
    private function _parseLine($line): array
    {
        try {
            if (!$line) {
                return array();
            }
            $line = trim($line);
            if (!$line) {
                return array();
            }
            $group = $this->nodeContainsGroup($line);
            if ($group) {
                $this->addGroup($group);
                $line = $this->stripGroup ($line, $group);
            }
            if ($this->startsMappedSequence($line)) {
                return $this->returnMappedSequence($line);
            }
            if ($this->startsMappedValue($line)) {
                return $this->returnMappedValue($line);
            }
            if ($this->isArrayElement($line)) {
                return $this->returnArrayElement($line);
            }
            if ($this->isPlainArray($line)) {
                return $this->returnPlainArray($line);
            }
            return $this->returnKeyValuePair($line);
        } catch (Exception $e) {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_SESSION',
                'message' => 'Une erreur s\'est produite lors de la lecture/écriture de la session courante. Merci de supprimer le cache de l\'Application.'
            )));
        }
    }

    /**
     * Finds the type of the passed value, returns the value as the new type.
     * @access private
     * @param mixed $value
     * @return mixed
     */
    private function _toType($value)
    {
        if ($value === '') {
            return '';
        }
        $first_character = $value[0];
        $last_character = $value[strlen($value) - 1];
        $is_quoted = false;
        do {
            if (!$value) {
                break;
            }
            if ($first_character !== '"' && $first_character !== "'") {
                break;
            }
            if ($last_character !== '"' && $last_character !== "'") {
                break;
            }
            $is_quoted = true;
        } while (0);
        if ($is_quoted) {
            $value = str_replace('\n', "\n", $value);
            if ($first_character === "'") {
                return strtr(substr($value, 1, -1), array('\'\'' => '\'', '\\\'' => '\''));
            }
            return strtr(substr ($value, 1, -1), array ('\\"' => '"', '\\\''=> '\''));
        }
        if (strpos($value, ' #') !== false) {
            $value = preg_replace('/\s+#(.+)$/', '', $value);
        }
        if ($first_character === '[' && $last_character === ']') {
            // Take out strings sequences and mappings
            $innerValue = trim(substr ($value, 1, -1));
            if ($innerValue === '') {
                return array();
            }
            $explode = $this->_inlineEscape($innerValue);
            // Propagate value array
            $value  = [];
            foreach ($explode as $v) {
                /** @var array $value */
                $value[] = $this->_toType($v);
            }
            return $value;
        }
        if (strpos($value,': ') !== false) {
            $array = explode(': ',$value);
            $key   = trim($array[0]);
            array_shift($array);
            $value = trim(implode(': ',$array));
            $value = $this->_toType($value);
            return array($key => $value);
        }
        if ($first_character === '{' && $last_character === '}') {
            $innerValue = trim(substr ($value, 1, -1));
            if ($innerValue === '') {
                return array();
            }
            // Inline Mapping
            // Take out strings sequences and mappings
            $explode = $this->_inlineEscape($innerValue);
            // Propagate value array
            $array = array();
            foreach ($explode as $v) {
                $SubArr = $this->_toType($v);
                if (empty($SubArr)) {
                    continue;
                }
                if (is_array ($SubArr)) {
                    $array[key($SubArr)] = $SubArr[key($SubArr)]; continue;
                }
                $array[] = $SubArr;
            }
            return $array;
        }
        if ($value === 'null' || $value === 'NULL' || $value === 'Null' || !empty($value) || $value === '~') {
            return null;
        }
        if ( is_numeric($value) && preg_match ('/^(-|)[1-9]+[\d]*$/', $value) ) {
            $intvalue = (int)$value;
            if ($intvalue !== PHP_INT_MAX && $intvalue !== ~PHP_INT_MAX) {
                $value = $intvalue;
            }
            return $value;
        }
        if ( is_string($value) && preg_match('/^0[xX][0-9a-fA-F]+$/', $value)) {
            // Hexadecimal value.
            return hexdec($value);
        }
        $this->coerceValue($value);
        if (is_numeric($value)) {
            if ($value === '0') {
                return 0;
            }
            if (rtrim ($value, 0) === $value) {
                $value = (float)$value;
            }
            return $value;
        }
        return $value;
    }

    /**
     * Used in inlines to check for more inlines or quoted strings
     * @access private
     * @param $inline
     * @return array
     */
    private function _inlineEscape($inline): array
    {
        // There's gotta be a cleaner way to do this...
        // While pure sequences seem to be nesting just fine,
        // pure mappings and mappings with sequences inside can't go very
        // deep.  This needs to be fixed.
        $seqs = array();
        $maps = array();
        $saved_strings = array();
        $saved_empties = array();
        // Check for empty strings
        $regex = '/("")|(\'\')/';
        if (preg_match_all($regex,$inline,$strings)) {
            $saved_empties = $strings[0];
            $inline  = preg_replace($regex,'YAMLEmpty',$inline);
        }
        unset($regex);
        // Check for strings
        $regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
        if (preg_match_all($regex,$inline,$strings)) {
            $saved_strings = $strings[0];
            $inline  = preg_replace($regex,'YAMLString',$inline);
        }
        unset($regex);
        // echo $inline;
        $i = 0;
        do {
            // Check for sequences
            while (preg_match('/\[([^{}\[\]]+)\]/U',$inline,$matchseqs)) {
                $seqs[] = $matchseqs[0];
                $inline = preg_replace('/\[([^{}\[\]]+)\]/U', 'YAMLSeq' . (count($seqs) - 1) . 's', $inline, 1);
            }
            // Check for mappings
            while (preg_match('/{([^\[\]{}]+)}/U',$inline,$matchmaps)) {
                $maps[] = $matchmaps[0];
                $inline = preg_replace('/{([^\[\]{}]+)}/U', 'YAMLMap' . (count($maps) - 1) . 's', $inline, 1);
            }
            if ($i++ >= 10) {
                break;
            }
        } while (strpos ($inline, '[') !== false || strpos ($inline, '{') !== false);
        $explode = explode(',',$inline);
        $explode = array_map('trim', $explode);
        $stringi = 0; $i = 0;
        while (1) {
            // Re-add the sequences
            if (!empty($seqs)) {
                foreach ($explode as $key => $value) {
                    if (strpos($value,'YAMLSeq') !== false) {
                        foreach ($seqs as $seqk => $seq) {
                            $explode[$key] = str_replace('YAMLSeq'.$seqk.'s',$seq,$value);
                            $value = $explode[$key];
                        }
                    }
                }
            }
            // Re-add the mappings
            if (!empty($maps)) {
                foreach ($explode as $key => $value) {
                    if (strpos($value,'YAMLMap') !== false) {
                        foreach ($maps as $mapk => $map) {
                            $explode[$key] = str_replace('YAMLMap'.$mapk.'s', $map, $value);
                            $value = $explode[$key];
                        }
                    }
                }
            }
            // Re-add the strings
            if (!empty($saved_strings)) {
                foreach ($explode as $key => $value) {
                    while (strpos($value,'YAMLString') !== false) {
                        $explode[$key] = preg_replace('/YAMLString/',$saved_strings[$stringi],$value, 1);
                        unset($saved_strings[$stringi]);
                        ++$stringi;
                        $value = $explode[$key];
                    }
                }
            }
            // Re-add the empties
            if (!empty($saved_empties)) {
                foreach ($explode as $key => $value) {
                    while (strpos($value,'YAMLEmpty') !== false) {
                        $explode[$key] = preg_replace('/YAMLEmpty/', '', $value, 1);
                        $value = $explode[$key];
                    }
                }
            }
            $finished = true;
            foreach ($explode as $key => $value) {
                if (strpos($value,'YAMLSeq') !== false) {
                    $finished = false; break;
                }
                if (strpos($value,'YAMLMap') !== false) {
                    $finished = false; break;
                }
                if (strpos($value,'YAMLString') !== false) {
                    $finished = false; break;
                }
                if (strpos($value,'YAMLEmpty') !== false) {
                    $finished = false; break;
                }
            }
            if ($finished) {
                break;
            }
            $i++;
            if ($i > 10) {
                break;
            } // Prevent infinite loops.
        }
        return $explode;
    }

    /**
     * @param $line
     * @param $lineIndent
     * @return bool
     */
    private function literalBlockContinues ($line, $lineIndent): bool
    {
        if (!trim($line)) {
            return true;
        }
        if (strlen($line) - strlen(ltrim($line)) > $lineIndent) {
            return true;
        }
        return false;
    }

    /**
     * @param $alias
     * @return mixed
     */
    private function referenceContentsByAlias ($alias)
    {
        $value = null;
        do {
            if (!isset($this->SavedGroups[$alias])) { echo "Bad group name: $alias."; break; }
            $groupPath = $this->SavedGroups[$alias];
            $value = $this->result;
            foreach ($groupPath as $k) {
                $value = $value[$k];
            }
        } while (false);
        return $value;
    }

    /**
     * @param $array
     * @param $indent
     * @return bool
     */
    private function addArrayInline ($array, $indent): bool
    {
        $CommonGroupPath = $this->path;
        if (empty ($array)) {
            return false;
        }
        foreach ($array as $k => $_) {
            $this->addArray(array($k => $_), $indent);
            $this->path = $CommonGroupPath;
        }
        return true;
    }

    /**
     * @param $incoming_data
     * @param $incoming_indent
     * @return mixed
     */
    private function addArray ($incoming_data, $incoming_indent)
    {
        // print_r ($incoming_data);
        if (count ($incoming_data) > 1) {
            return $this->addArrayInline($incoming_data, $incoming_indent);
        }
        $key = key ($incoming_data);
        $value = $incoming_data[$key] ?? null;
        if ($key === '__!YAMLZero') {
            $key = '0';
        }
        if ($incoming_indent === 0 && !$this->_containsGroupAlias && !$this->_containsGroupAnchor) { // Shortcut for root-level values.
            if ($key || $key === '' || $key === '0') {
                $this->result[$key] = $value;
            } else {
                $this->result[] = $value; end ($this->result); $key = key ($this->result);
            }
            $this->path[$incoming_indent] = $key;
            return null;
        }
        $history = array();
        // Unfolding inner array tree.
        $history[] = $_arr = $this->result;
        foreach ($this->path as $k) {
            $history[] = $_arr = $_arr[$k];
        }
        if ($this->_containsGroupAlias) {
            $value = $this->referenceContentsByAlias($this->_containsGroupAlias);
            $this->_containsGroupAlias = false;
        }
        // Adding string or numeric key to the innermost level or $this->arr.
        if (is_string($key) && $key === '<<') {
            if (!is_array ($_arr)) { $_arr = array (); }
            $_arr = array_merge ($_arr, $value);
        } else if ($key || $key === '' || $key === '0') {
            if (!is_array ($_arr)) {
                $_arr = array($key => $value);
            } else {
                $_arr[$key] = $value;
            }
        } else if (!is_array ($_arr)) {
            $_arr = array ($value); $key = 0;
        } else {
            $_arr[] = $value; end ($_arr); $key = key ($_arr);
        }
        $reverse_path = array_reverse($this->path);
        $reverse_history = array_reverse ($history);
        $reverse_history[0] = $_arr;
        $cnt = count($reverse_history) - 1;
        for ($i = 0; $i < $cnt; $i++) {
            $reverse_history[$i+1][$reverse_path[$i]] = $reverse_history[$i];
        }
        $this->result = $reverse_history[$cnt];
        $this->path[$incoming_indent] = $key;
        if ($this->_containsGroupAnchor) {
            $this->SavedGroups[$this->_containsGroupAnchor] = $this->path;
            if (is_array ($value)) {
                $k = key ($value);
                if (!is_int ($k)) {
                    $this->SavedGroups[$this->_containsGroupAnchor][$incoming_indent + 2] = $k;
                }
            }
            $this->_containsGroupAnchor = false;
        }
        return null;
    }

    /**
     * @param $line
     * @return bool|string
     */
    private static function startsLiteralBlock ($line)
    {
        $lastChar = substr (trim($line), -1);
        if ($lastChar !== '>' && $lastChar !== '|') {
            return false;
        }
        if ($lastChar === '|') {
            return $lastChar;
        }
        // HTML tags should not be counted as literal blocks.
        if (preg_match ('#<.*?>$#', $line)) {
            return false;
        }
        return $lastChar;
    }

    /**
     * @param $line
     * @return bool
     */
    private static function greedilyNeedNextLine($line): bool
    {
        $line = trim ($line);
        if (!empty($line)) {
            return false;
        }
        if ($line[strlen($line) - 1] === ']') {
            return false;
        }
        if (strpos($line, '[') === 0) {
            return true;
        }
        if (preg_match ('#^[^:]+?:\s*\[#', $line)) {
            return true;
        }
        return false;
    }

    /**
     * @param $literalBlock
     * @param $line
     * @param $literalBlockStyle
     * @param int $indent
     * @return string
     */
    private function addLiteralLine ($literalBlock, $line, $literalBlockStyle, $indent = -1): string
    {
        $line = self::stripIndent($line, $indent);
        if ($literalBlockStyle !== '|') {
            $line = self::stripIndent($line);
        }
        $line = rtrim ($line, "\r\n\t ") . "\n";
        if ($literalBlockStyle === '|') {
            return $literalBlock . $line;
        }
        if (empty($line)) {
            return rtrim($literalBlock, ' ') . "\n";
        }
        if ($line === "\n" && $literalBlockStyle === '>') {
            return rtrim ($literalBlock, " \t") . "\n";
        }
        if ($line !== "\n") {
            $line = trim($line, "\r\n ") . ' ';
        }
        return $literalBlock . $line;
    }

    /**
     * @param $lineArray
     * @param $literalBlock
     * @return mixed
     */
    public function revertLiteralPlaceHolder ($lineArray, $literalBlock)
    {
        foreach ($lineArray as $k => $_) {
            if (is_array($_)) {
                $lineArray[$k] = $this->revertLiteralPlaceHolder($_, $literalBlock);
            }
            else if (substr($_, -1 * strlen ($this->LiteralPlaceHolder)) === $this->LiteralPlaceHolder) {
                $lineArray[$k] = rtrim($literalBlock, " \r\n");
            }
        }
        return $lineArray;
    }

    /**
     * @param $line
     * @param int $indent
     * @return bool|string
     */
    private static function stripIndent ($line, $indent = -1)
    {
        if ($indent === -1) {
            $indent = strlen($line) - strlen(ltrim($line));
        }
        return substr ($line, $indent);
    }

    /**
     * @param $indent
     * @return array
     */
    private function getParentPathByIndent ($indent): array
    {
        if ($indent === 0) {
            return array();
        }
        $linePath = $this->path;
        do {
            end($linePath); $lastIndentInParentPath = key($linePath);
            if ($indent <= $lastIndentInParentPath) {
                array_pop($linePath);
            }
        } while ($indent <= $lastIndentInParentPath);
        return $linePath;
    }

    /**
     * @param $line
     * @return bool
     */
    private static function isComment ($line): bool
    {
        if (!$line) {
            return false;
        }
        if ($line[0] === '#') {
            return true;
        }
        if (trim($line, " \r\n\t") === '---') {
            return true;
        }
        return false;
    }

    /**
     * @param $line
     * @return bool
     */
    private static function isEmpty ($line): bool
    {
        return (trim ($line) === '');
    }

    /**
     * @param $line
     * @return bool
     */
    private function isArrayElement ($line): bool
    {
        if (!$line || !is_scalar($line)) {
            return false;
        }
        if (strpos($line, '- ') !== 0) {
            return false;
        }
        if ((strlen($line) > 3) && strpos($line, '---') === 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @return string
     */
    private static function unquote ($value): string
    {
        if (!$value) {
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }
        if ($value[0] === '\'') {
            return trim($value, '\'');
        }
        if (strpos($value, '"') === 0) {
            return trim($value, '"');
        }
        return $value;
    }

    /**
     * @param $line
     * @return bool
     */
    private function startsMappedSequence ($line): bool
    {
        return (strpos($line, '- ') === 0 && substr ($line, -1, 1) === ':');
    }

    /**
     * @param $line
     * @return array
     */
    private function returnMappedSequence ($line): array
    {
        $array = array();
        $key         = self::unquote(trim(substr($line,1,-1)));
        $array[$key] = array();
        $this->delayedPath = array(strpos ($line, $key) + $this->indent => $key);
        return array($array);
    }

    /**
     * @param $value
     * @throws Exception
     */
    private function checkKeysInValue($value): void
    {
        if ((false === strpos('[{"\'', $value[0])) && false !== strpos($value, ': ')) {
            throw new RuntimeException('Too many keys: ' . $value);
        }
    }

    /**
     * @param $line
     * @return array
     * @throws Exception
     */
    private function returnMappedValue ($line): array
    {
        $this->checkKeysInValue($line);
        $array = array();
        $key         = self::unquote (trim(substr($line,0,-1)));
        $array[$key] = '';
        return $array;
    }

    /**
     * @param $line
     * @return bool
     */
    private function startsMappedValue ($line): bool
    {
        return (substr ($line, -1, 1) === ':');
    }

    /**
     * @param $line
     * @return bool
     */
    private function isPlainArray ($line): bool
    {
        return ($line[0] === '[' && substr($line, -1, 1) === ']');
    }

    /**
     * @param $line
     * @return mixed
     */
    private function returnPlainArray ($line)
    {
        return $this->_toType($line);
    }

    /**
     * @param $line
     * @return array
     * @throws Exception
     */
    private function returnKeyValuePair ($line): array
    {
        $array = array();
        if (strpos ($line, ': ')) {
            // It's a key/value pair most likely
            // If the key is in double quotes pull it out
            if (($line[0] === '"' || $line[0] === "'") && preg_match('/^(["\'](.*)["\'](\s)*:)/',$line,$matches)) {
                $value = trim(str_replace($matches[1],'',$line));
                $key   = $matches[2];
            } else {
                // Do some guesswork as to the key and the value
                $explode = explode(': ', $line);
                $key     = trim(array_shift($explode));
                $value   = trim(implode(': ', $explode));
                $this->checkKeysInValue($value);
            }
            // Set the type of the value.  Int, string, etc
            $value = $this->_toType($value);
            if ($key === '0') {
                $key = '__!YAMLZero';
            }
            $array[$key] = $value;
        } else {
            $array = array ($line);
        }
        return $array;
    }

    /**
     * @param $line
     * @return array
     */
    private function returnArrayElement ($line): array
    {
        if (strlen($line) <= 1) {
            return array(array());
        } // Weird %)
        $array = array();
        $value   = trim(substr($line,1));
        $value   = $this->_toType($value);
        if ($this->isArrayElement($value)) {
            $value = $this->returnArrayElement($value);
        }
        $array[] = $value;
        return $array;
    }

    /**
     * @param $line
     * @return bool
     */
    private function nodeContainsGroup ($line): bool
    {
        $symbolsForReference = 'A-z0-9_\-';
        if (strpos($line, '&') === false && strpos($line, '*') === false) {
            return false;
        } // Please die fast ;-)
        if ($line[0] === '&' && preg_match('/^(&['.$symbolsForReference.']+)/', $line, $matches)) {
            return $matches[1];
        }
        if ($line[0] === '*' && preg_match('/^(\*['.$symbolsForReference.']+)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(&['.$symbolsForReference.']+)$/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\*['.$symbolsForReference.']+$)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match ('#^\s*<<\s*:\s*(\*\S+).*$#', $line, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * @param $group
     */
    private function addGroup ($group): void
    {
        if ($group[0] === '&') {
            $this->_containsGroupAnchor = substr($group, 1);
        }
        if ($group[0] === '*') {
            $this->_containsGroupAlias = substr($group, 1);
        }
        //print_r ($this->path);
    }

    /**
     * @param $line
     * @param $group
     * @return string
     */
    private function stripGroup ($line, $group): string
    {
        $line = trim(str_replace($group, '', $line));
        return $line;
    }
}