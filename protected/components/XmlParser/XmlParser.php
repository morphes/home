<?php

class XmlParser
{
    static public $parsers = [
        XmlParserFurnish::PARSER_ID => 'XmlParserFurnish',
        XmlParserDomalina::PARSER_ID => 'XmlParserDomalina',
        XmlParserMebelion::PARSER_ID => 'XmlParserMebelion',
        XmlParserHomeme::PARSER_ID => 'XmlParserHomeme',
        XmlParserLifemebel::PARSER_ID => 'XmlParserLifemebel',
        XmlParserWestwing::PARSER_ID => 'XmlParserWestwing',
        XmlParserPostelDeluxe::PARSER_ID => 'XmlParserPostelDeluxe',
    ];

    public static function build($parserType, $xmlFile)
    {
        if (isset(self::$parsers[$parserType])) {
            return new self::$parsers[$parserType]($xmlFile);
        } else {
            throw new CException('Unknown parser type');
        }
    }
}