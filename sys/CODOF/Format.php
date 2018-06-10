<?php

namespace CODOF;

use \JBBCode\Parser as BBCodeParser;
use \JBBCode\CodeDefinitionBuilder as BBCodeBuilder;

class htmlValidator implements \JBBCode\InputValidator {

    /**
     *
     * @param string $input
     * @return string
     */
    public function validate($input) {


        return strip_tags($input);
    }

}

class Format {

    public static function message($mesg) {

        //convert relative path back to absolute url
        $mesg = str_replace("CODOF_RURI_" . UID . "_", NRURI, $mesg);
        return str_replace("CODOF_DURI_" . UID . "_", DURI, $mesg);
    }

    /**
     *
     * @param string $mesg
     * @return string
     */
    public static function excerpt($mesg, $tid, $sef) {

        $maxNoCharacters = 400; //Util::get_opt('max_excerpt_chars');

        $html = mb_convert_encoding($mesg, 'HTML-ENTITIES', "UTF-8");

        $doc = new \DOMDocument();
        @$doc->loadHTML($html);

        $tags = $doc->getElementsByTagName('img');

        $img = '';
        foreach ($tags as $tag) {


            //smiley is not that important, so any other image than smiley accept it.
            if (strpos($tag->getAttribute('src'), SMILEY_PATH) === FALSE) {

                $img = $tag->C14N();
                break;
            }
        }

        $ps = $doc->getElementsByTagName('p');

        foreach ($ps as $p) {

            $p->appendChild($doc->createElement("br"));
        }

        $txt = $doc->saveHTML();
        $txt = strip_tags($txt, '<br>');

        if (strlen($txt) > $maxNoCharacters) {

            /**
             * ALGO for <br> safe substring
             *
             * Consider string 1234<br> with $limit=6
             * Normally, it would produce incorrect html like "1234<b"
             *
             * STEPS
             * 1. Go back 4 chars from max chars
             *  -> $index = 2
             *
             * 2. Search for <br> with offset 2
             *  -> $nearest_br = 4
             *
             * 3. Since $nearest_br < $limit
             *  -> There is possibility of incorrect HTML
             *
             * 4. Set $limit = $nearest_br + 4
             *  -> $limit = 8
             *
             * Result: 1234<br> which is correct!
             */
            $index = $maxNoCharacters - 4;
            $nearest_br = strpos($txt, '<br>', $index);

            if ($nearest_br !== FALSE && $nearest_br < $maxNoCharacters) {

                $maxNoCharacters = strpos($txt, '>', $nearest_br) + 1;
            }

            $txt = ( substr($txt, 0, $maxNoCharacters));
            $overflow = true;
        } else {

            $overflow = false;
            $txt = strip_tags(($txt), "<br>");
            $txt = rtrim($txt, '<br>');
        }

        $txt = preg_replace("/(<br\s*\/?>\\n<br\s*\/?>\s*)+/", "<br/>", $txt);
        $xcerpt = nl2br(ltrim($txt));

        if ($img != '') {

            $img = "<br>" . $img;
        }

        return array('message' => $xcerpt . $img, 'overflow' => $overflow);
    }

    public static function imessage($mesg) {

        //$mesg = str_replace("<", "&lt;", $mesg);
        //$mesg = str_replace(">", "&gt;", $mesg);

        //$mesg = htmlentities($mesg, ENT_NOQUOTES, "UTF-8");
        return $mesg;
    }

    public static function title($mesg) {

        //$mesg = str_replace("<", "&lt;", $mesg);
        //$mesg = str_replace(">", "&gt;", $mesg);
        //$mesg = htmlspecialchars($mesg);
        $mesg = htmlentities($mesg, ENT_NOQUOTES, "UTF-8");
        return $mesg;
    }

    public static function parseBBCode($str) {

        $validator = new htmlValidator();


        $parser = new BBCodeParser();

        $parser->addCodeDefinitionSet(new \JBBCode\DefaultCodeDefinitionSet());

        /* [s] <strike> */
        $builder = new BBCodeBuilder('s', '<strike>{param}</strike>');
        $parser->addCodeDefinition($builder->build());
        /* [pre] <pre> */
        $builder = new BBCodeBuilder('pre', '<pre>{param}</pre>');
        $parser->addCodeDefinition($builder->build());
        /* [pre] <pre> */
        $builder = new BBCodeBuilder('sup', '<sup>{param}</sup>');
        $parser->addCodeDefinition($builder->build());
        /* [pre] <pre> */
        $builder = new BBCodeBuilder('sub', '<sub>{param}</sub>');
        $parser->addCodeDefinition($builder->build());

        /* [li] <li> */
        $builder = new BBCodeBuilder('li', '<li>{param}</li>');
        $parser->addCodeDefinition($builder->build());

        $builder = new BBCodeBuilder('list', '<ul>{param}</ul>');
        $builder->setUseOption(false)->setParseContent(true);
        $parser->addCodeDefinition($builder->build());

        $builder = new BBCodeBuilder('list', '<ol>{param}</ol>');
        $builder->setUseOption(true)->setParseContent(true);
        $parser->addCodeDefinition($builder->build());

        /* [move] <marquee> */
        $builder = new BBCodeBuilder('move', '<marquee>{param}</marquee>');
        $parser->addCodeDefinition($builder->build());

        /* [center] <div text-align: center> */
        $builder = new BBCodeBuilder('center', '<div style="text-align: center">{param}</div>');
        $parser->addCodeDefinition($builder->build());
        /* [left] <div text-align: left> */
        $builder = new BBCodeBuilder('left', '<div style="text-align: left">{param}</div>');
        $parser->addCodeDefinition($builder->build());
        /* [right] <div text-align: right> */
        $builder = new BBCodeBuilder('right', '<div style="text-align: right">{param}</div>');
        $parser->addCodeDefinition($builder->build());


        $parser->parse($str);

        return $parser->getAsHTML();
    }

    public static function br2nl($string) {

        $breaks = array("<br />", "<br>", "<br/>", "<br />", "&lt;br /&gt;", "&lt;br/&gt;", "&lt;br&gt;");
        return str_ireplace($breaks, "\r\n", $string);
    }

    public static function omessage($mesg) {

        $html = new \Ext\Html();

        
        //$imesg; no escaping required
        $mesg = $html->filter(str_replace("STARTCODOTAG", "<", $mesg));

        return $mesg;
    }

}
