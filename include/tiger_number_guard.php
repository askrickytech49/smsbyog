<?php

/**
 * Validate a TigerSMS number against the requested country where the
 * country has an unambiguous international calling code.
 */
function tiger_number_matches_country(string $country_name, string $number): ?bool
{
    $name = strtolower(trim(strip_tags($country_name)));
    $name = preg_replace('/\s+/', ' ', $name);
    $number = preg_replace('/\D+/', '', $number);
    if ($number === '') return false;

    $aliases = [
        'united kingdom' => 'united kingdom',
        'uk' => 'united kingdom',
        'united states' => 'united states',
        'united states vip' => 'united states',
        'united states virt' => 'united states',
        'usa' => 'united states',
        'canada' => 'canada',
        'cote divoire' => 'ivory coast',
        'cote d ivoire' => 'ivory coast',
        'cape verde' => 'cape verde',
        'bermuda' => 'bermuda',
        'bhutan' => 'bhutan',
    ];
    $name = $aliases[$name] ?? $name;

    // Shared numbering plans (USA/Canada, Caribbean, etc.) are deliberately
    // omitted because a prefix alone cannot identify the exact country.
    $prefixes = [
        'afghanistan' => '93', 'albania' => '355', 'algeria' => '213',
        'angola' => '244', 'argentina' => '54', 'armenia' => '374',
        'australia' => '61', 'austria' => '43', 'azerbaijan' => '994',
        'bahamas' => '1242', 'bahrain' => '973', 'bangladesh' => '880',
        'barbados' => '1246', 'belarus' => '375', 'belgium' => '32', 'belize' => '501',
        'benin' => '229', 'bermuda' => '1441', 'bhutan' => '975',
        'bolivia' => '591', 'bosnia and herzegovina' => '387',
        'botswana' => '267', 'brazil' => '55', 'brunei' => '673',
        'bulgaria' => '359', 'burkina faso' => '226', 'burundi' => '257',
        'cambodia' => '855', 'cameroon' => '237', 'cape verde' => '238',
        'chile' => '56', 'china' => '86', 'colombia' => '57', 'congo' => '242',
        'croatia' => '385', 'cyprus' => '357', 'czech republic' => '420',
        'denmark' => '45', 'djibouti' => '253',
        'ecuador' => '593', 'egypt' => '20', 'estonia' => '372',
        'ethiopia' => '251', 'finland' => '358', 'france' => '33',
        'georgia' => '995', 'germany' => '49', 'ghana' => '233',
        'greece' => '30', 'guatemala' => '502', 'guinea' => '224',
        'guyana' => '592', 'haiti' => '509', 'honduras' => '504',
        'hong kong' => '852', 'hungary' => '36', 'iceland' => '354',
        'india' => '91', 'indonesia' => '62', 'iraq' => '964',
        'ireland' => '353', 'israel' => '972', 'italy' => '39',
        'ivory coast' => '225', 'japan' => '81',
        'jordan' => '962', 'kazakhstan' => '7', 'kenya' => '254',
        'kuwait' => '965', 'kyrgyzstan' => '996', 'laos' => '856',
        'latvia' => '371', 'lebanon' => '961', 'lesotho' => '266',
        'liberia' => '231', 'libya' => '218', 'lithuania' => '370',
        'luxembourg' => '352', 'macau' => '853', 'madagascar' => '261',
        'malawi' => '265', 'malaysia' => '60', 'maldives' => '960',
        'mali' => '223', 'malta' => '356', 'mauritania' => '222',
        'mauritius' => '230', 'mexico' => '52', 'moldova' => '373',
        'mongolia' => '976', 'morocco' => '212', 'mozambique' => '258',
        'myanmar' => '95', 'namibia' => '264', 'nepal' => '977',
        'netherlands' => '31', 'new zealand' => '64', 'nicaragua' => '505',
        'niger' => '227', 'nigeria' => '234', 'north macedonia' => '389',
        'norway' => '47', 'oman' => '968', 'pakistan' => '92',
        'palestine' => '970', 'panama' => '507', 'paraguay' => '595',
        'peru' => '51', 'philippines' => '63', 'poland' => '48',
        'portugal' => '351', 'qatar' => '974', 'romania' => '40',
        'russia' => '7', 'rwanda' => '250', 'saudi arabia' => '966',
        'senegal' => '221', 'serbia' => '381', 'singapore' => '65',
        'slovakia' => '421', 'slovenia' => '386', 'somalia' => '252',
        'south africa' => '27', 'south korea' => '82', 'spain' => '34',
        'sri lanka' => '94', 'sudan' => '249', 'suriname' => '597',
        'sweden' => '46', 'switzerland' => '41', 'taiwan' => '886',
        'tajikistan' => '992', 'tanzania' => '255', 'thailand' => '66',
        'tunisia' => '216', 'turkey' => '90', 'turkmenistan' => '993',
        'uganda' => '256', 'ukraine' => '380', 'united arab emirates' => '971',
        'united kingdom' => '44', 'uruguay' => '598', 'uzbekistan' => '998',
        'venezuela' => '58', 'vietnam' => '84', 'yemen' => '967',
        'zambia' => '260', 'zimbabwe' => '263',
    ];

    if (!isset($prefixes[$name])) return null;
    return str_starts_with($number, $prefixes[$name]);
}