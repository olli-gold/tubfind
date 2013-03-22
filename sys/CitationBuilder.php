<?php
/**
 * Code to build citations for use in VuFind.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */

/**
 * Citation Builder Class
 *
 * This class builds APA and MLA citations.
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
class CitationBuilder
{
    private $_details;

    /**
     * Constructor
     *
     * Load the base data needed to build the citations.  The $details parameter
     * should contain as many of the following keys as possible:
     *
     *  authors         => Array of authors in "Last, First, Title, Dates" format.
     *                     i.e. King, Martin Luther, Jr., 1929-1968.
     *  title           => The primary title of the work.
     *  subtitle        => Subtitle of the work.
     *  edition         => Array of edition statements (i.e. "1st ed.").
     *  pubPlace        => Place of publication.
     *  pubName         => Name of publisher.
     *  pubDate         => Year of publication.
     *
     * Unless noted as an array, each field should be a string.
     *
     * @param array $details An array of details used to build the citations.
     *
     * @access public
     */
    public function __construct($details)
    {
        $this->_details = $details;
    }

    /**
     * Get supported citations
     * 
     * Returns the citations supported by this builder
     * 
     * @return array List of supported citation formats
     * @access public
     */
    public static function getSupportedCitationFormats()
    {
        return array('APA', 'MLA');
    }
    
    /**
     * Retrieve a citation in a particular format
     * 
     * Returns the citation in the format specified
     * 
     * @param string $format Citation format from getSupportedCitationFormats()
     *
     * @return string        Path to a Smarty template to display the citation
     * @access public
     */
    public function getCitation($format)
    {
        // Construct method name for requested format:
        $method = 'get' . $format;

        // Avoid calls to inappropriate/missing methods:
        if ($format != 'Citation' && $format != 'SupportedCitationFormats'
            && method_exists($this, $method)
        ) {
            return $this->$method();
        }

        // Return blank string if no valid method found:
        return '';
    }

    /**
     * Get APA citation.
     *
     * This function assigns all the necessary variables and then returns a template
     * name to display an APA citation.
     *
     * @return string Path to a Smarty template to display the citation.
     * @access public
     */
    public function getAPA()
    {
        global $interface;
        $apa = array(
            'title' => $this->_getAPATitle(),
            'authors' => $this->_getAPAAuthors(),
            'publisher' => $this->_getPublisher(),
            'year' => $this->_getYear(),
            'edition' => $this->_getEdition()
        );
        // Show a period after the title if it does not already have punctuation
        // and is not followed by an edition statement:
        $apa['periodAfterTitle']
            = (!$this->_isPunctuated($apa['title']) && empty($apa['edition']));
        $interface->assign('apaDetails', $apa);
        return 'Citation/apa.tpl';
    }

    /**
     * Get MLA citation.
     *
     * This function assigns all the necessary variables and then returns a template
     * name to display an MLA citation.
     *
     * @return string Path to a Smarty template to display the citation.
     * @access public
     */
    public function getMLA()
    {
        global $interface;
        $mla = array(
            'title' => $this->_getMLATitle(),
            'authors' => $this->_getMLAAuthors(),
            'publisher' => $this->_getPublisher(),
            'year' => $this->_getYear(),
            'edition' => $this->_getEdition()
        );
        $mla['periodAfterTitle'] = !$this->_isPunctuated($mla['title']);
        $interface->assign('mlaDetails', $mla);
        return 'Citation/mla.tpl';
    }

    /**
     * Is the string a valid name suffix?
     *
     * @param string $str The string to check.
     *
     * @return bool       True if it's a name suffix.
     * @access private
     */
    private function _isNameSuffix($str)
    {
        $str = $this->_stripPunctuation($str);

        // Is it a standard suffix?
        $suffixes = array('Jr', 'Sr');
        if (in_array($str, $suffixes)) {
            return true;
        }

        // Is it a roman numeral?  (This check could be smarter, but it's probably
        // good enough as it is).
        if (preg_match('/^[MDCLXVI]+$/', $str)) {
            return true;
        }

        // If we got this far, it's not a suffix.
        return false;
    }

    /**
     * Is the string a date range?
     *
     * @param string $str The string to check.
     *
     * @return bool       True if it's a date range.
     * @access private
     */
    private function _isDateRange($str)
    {
        $str = trim($str);
        return preg_match('/^([0-9]+)-([0-9]*)\.?$/', $str);
    }

    /**
     * Abbreviate a first name.
     *
     * @param string $name The name to abbreviate
     *
     * @return string      The abbreviated name.
     * @access private
     */
    private function _abbreviateName($name)
    {
        $parts = explode(', ', $name);
        $name = $parts[0];

        // Attach initials... but if we encountered a date range, the name
        // ended earlier than expected, and we should stop now.
        if (isset($parts[1]) && !$this->_isDateRange($parts[1])) {
            $fnameParts = explode(' ', $parts[1]);
            for ($i = 0; $i < count($fnameParts); $i++) {
                // Use the multi-byte substring function if available to avoid
                // problems with accented characters:
                if (function_exists('mb_substr')) {
                    $fnameParts[$i] = mb_substr($fnameParts[$i], 0, 1, 'utf8') . '.';
                } else {
                    $fnameParts[$i] = substr($fnameParts[$i], 0, 1) . '.';
                }
            }
            $name .= ', ' . implode(' ', $fnameParts);
            if (isset($parts[2]) && $this->_isNameSuffix($parts[2])) {
                $name = trim($name) . ', ' . $parts[2];
            }
        }

        return trim($name);
    }

    /**
     * Strip the dates off the end of a name.
     *
     * @param string $str Name to clean.
     *
     * @return string     Cleaned name.
     * @access private
     */
    private function _cleanNameDates($str)
    {
        $arr = explode(', ', $str);
        $name = $arr[0];
        if (isset($arr[1]) && !$this->_isDateRange($arr[1])) {
            $name .= ', ' . $arr[1];
            if (isset($arr[2]) && $this->_isNameSuffix($arr[2])) {
                $name .= ', ' . $arr[2];
            }
        }
        return $name;
    }

    /**
     * Does the string end in punctuation that we want to retain?
     *
     * @param string $string String to test.
     *
     * @return boolean       Does string end in punctuation?
     * @access private
     */
    private function _isPunctuated($string)
    {
        $punctuation = array('.', '?', '!');
        return (in_array(substr($string, -1), $punctuation));
    }

    /**
     * Strip unwanted punctuation from the right side of a string.
     *
     * @param string $text Text to clean up.
     *
     * @return string      Cleaned up text.
     * @access private
     */
    private function _stripPunctuation($text)
    {
        $punctuation = array('.', ',', ':', ';', '/');
        $text = trim($text);
        if (in_array(substr($text, -1), $punctuation)) {
            $text = substr($text, 0, -1);
        }
        return trim($text);
    }

    /**
     * Turn a "Last, First" name into a "First Last" name.
     *
     * @param string $str Name to reverse.
     *
     * @return string     Reversed name.
     * @access private
     */
    private function _reverseName($str)
    {
        $arr = explode(', ', $str);

        // If the second chunk is a date range, there is nothing to reverse!
        if (!isset($arr[1]) || $this->_isDateRange($arr[1])) {
            return $arr[0];
        }

        $name = $arr[1] . ' ' . $arr[0];
        if (isset($arr[2]) && $this->_isNameSuffix($arr[2])) {
            $name .= ', ' . $arr[2];
        }
        return $name;
    }

    /**
     * Capitalize all words in a title, except for a few common exceptions.
     *
     * @param string $str Title to capitalize.
     *
     * @return string     Capitalized title.
     * @access private
     */
    private function _capitalizeTitle($str)
    {
        $exceptions = array('a', 'an', 'the', 'against', 'between', 'in', 'of',
            'to', 'and', 'but', 'for', 'nor', 'or', 'so', 'yet', 'to');

        $words = explode(' ', $str);
        $newwords = array();
        $followsColon = false;
        foreach ($words as $word) {
            // Capitalize words unless they are in the exception list...  but even
            // exceptional words get capitalized if they follow a colon.
            if (!in_array($word, $exceptions) || $followsColon) {
                $word = ucfirst($word);
            }
            array_push($newwords, $word);

            $followsColon = substr($word, -1) == ':';
        }

        return ucfirst(join(' ', $newwords));
    }

    /**
     * Get the full title for an APA citation.
     *
     * @return string
     * @access private
     */
    private function _getAPATitle()
    {
        // Create Title
        $title = $this->_stripPunctuation($this->_details['title']);
        if (isset($this->_details['subtitle'])) {
            $subtitle = $this->_stripPunctuation($this->_details['subtitle']);
            // Capitalize subtitle and apply it, assuming it really exists:
            if (!empty($subtitle)) {
                $subtitle
                    = strtoupper(substr($subtitle, 0, 1)) . substr($subtitle, 1);
                $title .= ': ' . $subtitle;
            }
        }

        return $title;
    }

    /**
     * Get an array of authors for an APA citation.
     *
     * @return array
     * @access private
     */
    private function _getAPAAuthors()
    {
        $authorStr = '';
        if (isset($this->_details['authors'])
            && is_array($this->_details['authors'])
        ) {
            $i = 0;
            foreach ($this->_details['authors'] as $author) {
                $author = $this->_abbreviateName($author);
                if (($i + 1 == count($this->_details['authors']))
                    && ($i > 0)
                ) { // Last
                    $authorStr .= '& ' . $this->_stripPunctuation($author) . '.';
                } elseif (count($this->_details['authors']) > 1) {
                    $authorStr .= $author . ', ';
                } else { // First and only
                    $authorStr .= $this->_stripPunctuation($author) . '.';
                }
                $i++;
            }
        }
        return (empty($authorStr) ? false : $authorStr);
    }

    /**
     * Get edition statement for inclusion in a citation.  Shared by APA and
     * MLA functionality.
     *
     * @return string
     * @access private
     */
    private function _getEdition()
    {
        // Find the first edition statement that isn't "1st ed."
        if (isset($this->_details['edition'])
            && is_array($this->_details['edition'])
        ) {
            foreach ($this->_details['edition'] as $edition) {
                // Strip punctuation from the edition to get rid of unwanted
                // junk...  but if there is nothing left after stripping, put
                // back at least one period!
                $edition = $this->_stripPunctuation($edition);
                if (empty($edition)) {
                    continue;
                }
                if (!$this->_isPunctuated($edition)) {
                    $edition .= '.';
                }
                if ($edition !== '1st ed.') {
                    return $edition;
                }
            }
        }

        // No edition statement found:
        return false;
    }

    /**
     * Get the full title for an MLA citation.
     *
     * @return string
     * @access private
     */
    private function _getMLATitle()
    {
        // MLA titles are just like APA titles, only capitalized differently:
        return $this->_capitalizeTitle($this->_getAPATitle());
    }

    /**
     * Get an array of authors for an APA citation.
     *
     * @return array
     * @access private
     */
    private function _getMLAAuthors()
    {
        $authorStr = '';
        if (isset($this->_details['authors'])
            && is_array($this->_details['authors'])
        ) {
            $i = 0;
            if (count($this->_details['authors']) > 4) {
                $author = $this->_details['authors'][0];
                $authorStr = $this->_cleanNameDates($author) . ', et al';
            } else {
                foreach ($this->_details['authors'] as $author) {
                    if (($i+1 == count($this->_details['authors'])) && ($i > 0)) {
                        // Last
                        $authorStr .= ', and ' .
                            $this->_reverseName($this->_stripPunctuation($author));
                    } elseif ($i > 0) {
                        $authorStr .= ', ' .
                            $this->_reverseName($this->_stripPunctuation($author));
                    } else {
                        // First
                        $authorStr .= $this->_cleanNameDates($author);
                    }
                    $i++;
                }
            }
        }
        return (empty($authorStr) ? false : $this->_stripPunctuation($authorStr));
    }

    /**
     * Get publisher information (place: name) for inclusion in a citation.
     * Shared by APA and MLA functionality.
     *
     * @return string
     * @access private
     */
    private function _getPublisher()
    {
        $parts = array();
        if (isset($this->_details['pubPlace'])
            && !empty($this->_details['pubPlace'])
        ) {
            $parts[] = $this->_stripPunctuation($this->_details['pubPlace']);
        }
        if (isset($this->_details['pubName'])
            && !empty($this->_details['pubName'])
        ) {
            $parts[] = $this->_details['pubName'];
        }
        if (empty($parts)) {
            return false;
        }
        return $this->_stripPunctuation(implode(': ', $parts));
    }

    /**
     * Get the year of publication for inclusion in a citation.
     * Shared by APA and MLA functionality.
     *
     * @return string
     * @access private
     */
    private function _getYear()
    {
        if (isset($this->_details['pubDate'])) {
            return preg_replace('/[^0-9]/', '', $this->_details['pubDate']);
        }
        return false;
    }
}
?>