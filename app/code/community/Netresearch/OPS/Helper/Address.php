<?php
/**
 * Netresearch_OPS_Helper_Address
 *
 * @package
 * @copyright 2016 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license   OSL 3.0
 */

abstract class Netresearch_OPS_Helper_Address
{

    const OPTION_A_ADDITION_1 = 'A_Addition_to_address_1';
    const OPTION_A_STREET_NAME = 'A_Street_name_1';
    const OPTION_A_HOUSE_NUMBER = 'A_House_number_1';
    const OPTION_A_ADDITION_2 = 'A_Addition_to_address_2';
    const OPTION_B_ADDITION_1 = 'B_Addition_to_address_1';
    const OPTION_B_STREET_NAME = 'B_Street_name';
    const OPTION_B_HOUSE_NUMBER = 'B_House_number';
    const OPTION_B_ADDITION_2 = 'B_Addition_to_address_2';

    const STREET_NAME = 'street_name';
    const STREET_NUMBER = 'street_number';
    const SUPPLEMENT = 'supplement';

    /**
     * split street into street name, number and additional street information
     *
     * @param string[] $streetArray
     *
     * @return array
     */
    public static function splitStreet($streetArray)
    {
        $fullAddress = implode(", ", $streetArray);

        $result = array(
            self::STREET_NAME => array_shift($streetArray),
            self::STREET_NUMBER => '',
            self::SUPPLEMENT => array(array_shift($streetArray))
        );

        if (preg_match(self::getStreetSplitter(), $fullAddress, $matches)) {
            // Pattern A
            if (isset($matches[self::OPTION_A_STREET_NAME]) && !empty($matches[self::OPTION_A_STREET_NAME])) {
                $result[self::STREET_NAME] = trim($matches[self::OPTION_A_STREET_NAME]);

                if (isset($matches[self::OPTION_A_HOUSE_NUMBER]) && !empty($matches[self::OPTION_A_HOUSE_NUMBER])) {
                    $result[self::STREET_NUMBER] = trim($matches[self::OPTION_A_HOUSE_NUMBER]);
                }

                if (isset($matches[self::OPTION_A_ADDITION_1])) {
                    $result[self::SUPPLEMENT][] = trim($matches[self::OPTION_A_ADDITION_1]);
                }

                if (isset($matches[self::OPTION_A_ADDITION_2])) {
                    $result[self::SUPPLEMENT][] = trim($matches[self::OPTION_A_ADDITION_2]);
                }

                // Pattern B
            } elseif (isset($matches[self::OPTION_B_STREET_NAME]) && !empty($matches[self::OPTION_B_STREET_NAME])) {
                $result[self::STREET_NAME] = trim($matches[self::OPTION_B_STREET_NAME]);

                if (isset($matches[self::OPTION_B_HOUSE_NUMBER]) && !empty($matches[self::OPTION_B_HOUSE_NUMBER])) {
                    $result[self::STREET_NUMBER] = trim($matches[self::OPTION_B_HOUSE_NUMBER]);
                }

                if (isset($matches[self::OPTION_B_ADDITION_1])) {
                    $result[self::SUPPLEMENT][] = trim($matches[self::OPTION_B_ADDITION_1]);
                }

                if (isset($matches[self::OPTION_B_ADDITION_2])) {
                    $result[self::SUPPLEMENT][] = trim($matches[self::OPTION_B_ADDITION_2]);
                }
            }
        }

        $result[self::SUPPLEMENT] = implode(' ', array_filter(array_unique($result[self::SUPPLEMENT])));

        return $result;
    }

    /**
     * Regex to analyze addresses and split them into the groups Street Name, House Number and Additional Information
     * Pattern A is addition number street addition
     * Pattern B is addition street number addition
     *
     * @return string
     */
    private static function getStreetSplitter()
    {
        return "/\\A\\s*
 (?:
   #########################################################################
   # Option A: [<Addition to address 1>] <House number> <Street name>      #
   # [<Addition to address 2>]                                             #
   #########################################################################
   (?:
     (?P<A_Addition_to_address_1>.*?)
     ,\\s*
   )?
   # Addition to address 1
   (?:No\\.\\s*)?
   (?P<A_House_number_1>
     \\pN+[a-zA-Z]?
     (?:\\s*[-\\/\\pP]\\s*\\pN+[a-zA-Z]?)*
   )
   # House number
   \\s*,?\\s*
   (?P<A_Street_name_1>
     (?:[a-zA-Z]\\s*|\\pN\\pL{2,}\\s\\pL)
     \\S[^,#]*?
     (?<!\\s)
   )
   # Street name
   \\s*
   (?:
     (?:
       [,\\/]|
       (?=\\#)
     )
     \\s*
     (?!\\s*No\\.)
     (?P<A_Addition_to_address_2>
       (?!\\s)
       .*?
     )
   )?
   # Addition to address 2
   |
   #########################################################################
   # Option B: [<Addition to address 1>] <Street name> <House number>      #
   # [<Addition to address 2>]                                             #
   #########################################################################
   (?:
     (?P<B_Addition_to_address_1>.*?)
     ,\\s*
     (?=.*[,\\/])
   )?
   # Addition to address 1
   (?!\\s*No\\.)
   (?P<B_Street_name>
     \\S\\s*\\S
     (?:
       [^,#]
       (?!\\b\\pN+\\s)
     )*?
     (?<!\\s)
   )
   # Street name
   \\s*[\\/,]?\\s*
   (?:\\sNo\\.)?
   \\s+
   (?P<B_House_number>
     \\pN+\\s*-?[a-zA-Z]?
     (?:
       \\s*[-\\/\\pP]?\\s*\\pN+
       (?:\\s*[\\-a-zA-Z])?
     )*|
     [IVXLCDM]+
     (?!.*\\b\\pN+\\b)
   )
   (?<!\\s)
   # House number
   \\s*
   (?:
     (?:
       [,\\/]|
       (?=\\#)|
       \\s
     )
     \\s*
     (?!\\s*No\\.)
     \\s*
     (?P<B_Addition_to_address_2>
       (?!\\s)
       .*?
     )
   )?
   # Addition to address 2
 )
 \\s*\\Z/x";
    }
}
