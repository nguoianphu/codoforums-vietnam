<?php

#   Copyright (C) 2006-2012 Tobias Leupold <tobias.leupold@web.de>
#
#   This file is part of the b8 package
#
#   This program is free software; you can redistribute it and/or modify it
#   under the terms of the GNU Lesser General Public License as published by
#   the Free Software Foundation in version 2.1 of the License.
#
#   This program is distributed in the hope that it will be useful, but
#   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
#   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
#   License for more details.
#
#   You should have received a copy of the GNU Lesser General Public License
#   along with this program; if not, write to the Free Software Foundation,
#   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

/**
 * Copyright (C) 2006-2012 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL 2.1
 * @access public
 * @package b8
 * @author Tobias Leupold
 * @author Oliver Lillie (aka buggedcom) (original PHP 5 port)
 */
class b8_lexer_default {

    const LEXER_TEXT_NOT_STRING = 'LEXER_TEXT_NOT_STRING';
    const LEXER_TEXT_EMPTY = 'LEXER_TEXT_EMPTY';

    public $config = array(
        'min_size' => 3,
        'max_size' => 30,
        'allow_numbers' => FALSE,
        'get_uris' => TRUE,
        'old_get_html' => TRUE,
        'get_html' => FALSE,
        'get_bbcode' => FALSE
    );
    private $_tokens = NULL;
    private $_processed_text = NULL;

    # The regular expressions we use to split the text to tokens
    public $regexp = array(
        'raw_split' => '/[\s,\.\/"\:;\|<>\-_\[\]{}\+=\)\(\*\&\^%]+/',
        'ip' => '/([A-Za-z0-9\_\-\.]+)/',
        'uris' => '/([A-Za-z0-9\_\-]*\.[A-Za-z0-9\_\-\.]+)/',
        'html' => '/(<.+?>)/',
        'bbcode' => '/(\[.+?\])/',
        'tagname' => '/(.+?)\s/',
        'numbers' => '/^[0-9]+$/'
    );

    /**
     * Constructs the lexer.
     *
     * @access public
     * @return void
     */
    function __construct($config) {

        # Validate config data
        $this->commonwords = array();

        $this->commonwords[0] = 'the';
        $this->commonwords[1] = 'be';
        $this->commonwords[2] = 'and';
        $this->commonwords[3] = 'of';
        $this->commonwords[4] = 'a';
        $this->commonwords[5] = 'in';
        $this->commonwords[6] = 'to';
        $this->commonwords[7] = 'have';
        $this->commonwords[8] = 'it';
        $this->commonwords[9] = 'I';
        $this->commonwords[10] = 'that';
        $this->commonwords[11] = 'for';
        $this->commonwords[12] = 'you';
        $this->commonwords[13] = 'he';
        $this->commonwords[14] = 'with';
        $this->commonwords[15] = 'on';
        $this->commonwords[16] = 'do';
        $this->commonwords[17] = 'say';
        $this->commonwords[18] = 'this';
        $this->commonwords[19] = 'they';
        $this->commonwords[20] = 'at';
        $this->commonwords[21] = 'but';
        $this->commonwords[22] = 'we';
        $this->commonwords[23] = 'his';
        $this->commonwords[24] = 'from';
        $this->commonwords[25] = 'not';
        $this->commonwords[26] = 'by';
        $this->commonwords[27] = 'she';
        $this->commonwords[28] = 'or';
        $this->commonwords[29] = 'as';
        $this->commonwords[30] = 'what';
        $this->commonwords[31] = 'go';
        $this->commonwords[32] = 'their';
        $this->commonwords[33] = 'can';
        $this->commonwords[34] = 'who';
        $this->commonwords[35] = 'get';
        $this->commonwords[36] = 'if';
        $this->commonwords[37] = 'would';
        $this->commonwords[38] = 'her';
        $this->commonwords[39] = 'all';
        $this->commonwords[40] = 'my';
        $this->commonwords[41] = 'make';
        $this->commonwords[42] = 'about';
        $this->commonwords[43] = 'know';
        $this->commonwords[44] = 'will';
        $this->commonwords[45] = 'up';
        $this->commonwords[46] = 'one';
        $this->commonwords[47] = 'time';
        $this->commonwords[48] = 'there';
        $this->commonwords[49] = 'year';
        $this->commonwords[50] = 'so';
        $this->commonwords[51] = 'think';
        $this->commonwords[52] = 'when';
        $this->commonwords[53] = 'which';
        $this->commonwords[54] = 'them';
        $this->commonwords[55] = 'some';
        $this->commonwords[56] = 'me';
        $this->commonwords[57] = 'people';
        $this->commonwords[58] = 'take';
        $this->commonwords[59] = 'out';
        $this->commonwords[60] = 'into';
        $this->commonwords[61] = 'just';
        $this->commonwords[62] = 'see';
        $this->commonwords[63] = 'him';
        $this->commonwords[64] = 'your';
        $this->commonwords[65] = 'come';
        $this->commonwords[66] = 'could';
        $this->commonwords[67] = 'now';
        $this->commonwords[68] = 'than';
        $this->commonwords[69] = 'like';
        $this->commonwords[70] = 'other';
        $this->commonwords[71] = 'how';
        $this->commonwords[72] = 'then';
        $this->commonwords[73] = 'its';
        $this->commonwords[74] = 'our';
        $this->commonwords[75] = 'two';
        $this->commonwords[76] = 'more';
        $this->commonwords[77] = 'these';
        $this->commonwords[78] = 'want';
        $this->commonwords[79] = 'way';
        $this->commonwords[80] = 'look';
        $this->commonwords[81] = 'first';
        $this->commonwords[82] = 'also';
        $this->commonwords[83] = 'new';
        $this->commonwords[84] = 'because';
        $this->commonwords[85] = 'day';
        $this->commonwords[86] = 'use';
        $this->commonwords[87] = 'no';
        $this->commonwords[88] = 'man';
        $this->commonwords[89] = 'find';
        $this->commonwords[90] = 'here';
        $this->commonwords[91] = 'thing';
        $this->commonwords[92] = 'give';
        $this->commonwords[93] = 'many';
        $this->commonwords[94] = 'well';
        $this->commonwords[95] = 'only';
        $this->commonwords[96] = 'those';
        $this->commonwords[97] = 'tell';
        $this->commonwords[98] = 'very';
        $this->commonwords[99] = 'even';
        $this->commonwords[100] = 'back';
        $this->commonwords[101] = 'any';
        $this->commonwords[102] = 'good';
        $this->commonwords[103] = 'woman';
        $this->commonwords[104] = 'through';
        $this->commonwords[105] = 'us';
        $this->commonwords[106] = 'life';
        $this->commonwords[107] = 'child';
        $this->commonwords[108] = 'work';
        $this->commonwords[109] = 'down';
        $this->commonwords[110] = 'may';
        $this->commonwords[111] = 'after';
        $this->commonwords[112] = 'should';
        $this->commonwords[113] = 'call';
        $this->commonwords[114] = 'world';
        $this->commonwords[115] = 'over';
        $this->commonwords[116] = 'school';
        $this->commonwords[117] = 'still';
        $this->commonwords[118] = 'try';
        $this->commonwords[119] = 'last';
        $this->commonwords[120] = 'ask';
        $this->commonwords[121] = 'need';
        $this->commonwords[122] = 'too';
        $this->commonwords[123] = 'feel';
        $this->commonwords[124] = 'three';
        $this->commonwords[125] = 'state';
        $this->commonwords[126] = 'never';
        $this->commonwords[127] = 'become';
        $this->commonwords[128] = 'between';
        $this->commonwords[129] = 'high';
        $this->commonwords[130] = 'really';
        $this->commonwords[131] = 'something';
        $this->commonwords[132] = 'most';
        $this->commonwords[133] = 'another';
        $this->commonwords[134] = 'much';
        $this->commonwords[135] = 'family';
        $this->commonwords[136] = 'own';
        $this->commonwords[137] = 'leave';
        $this->commonwords[138] = 'put';
        $this->commonwords[139] = 'old';
        $this->commonwords[140] = 'while';
        $this->commonwords[141] = 'mean';
        $this->commonwords[142] = 'keep';
        $this->commonwords[143] = 'student';
        $this->commonwords[144] = 'why';
        $this->commonwords[145] = 'let';
        $this->commonwords[146] = 'great';
        $this->commonwords[147] = 'same';
        $this->commonwords[148] = 'big';
        $this->commonwords[149] = 'group';
        $this->commonwords[150] = 'begin';
        $this->commonwords[151] = 'seem';
        $this->commonwords[152] = 'country';
        $this->commonwords[153] = 'help';
        $this->commonwords[154] = 'talk';
        $this->commonwords[155] = 'where';
        $this->commonwords[156] = 'turn';
        $this->commonwords[157] = 'problem';
        $this->commonwords[158] = 'every';
        $this->commonwords[159] = 'start';
        $this->commonwords[160] = 'hand';
        $this->commonwords[161] = 'might';
        $this->commonwords[162] = 'American';
        $this->commonwords[163] = 'show';
        $this->commonwords[164] = 'part';
        $this->commonwords[165] = 'against';
        $this->commonwords[166] = 'place';
        $this->commonwords[167] = 'such';
        $this->commonwords[168] = 'again';
        $this->commonwords[169] = 'few';
        $this->commonwords[170] = 'case';
        $this->commonwords[171] = 'week';
        $this->commonwords[172] = 'company';
        $this->commonwords[173] = 'system';
        $this->commonwords[174] = 'each';
        $this->commonwords[175] = 'right';
        $this->commonwords[176] = 'program';
        $this->commonwords[177] = 'hear';
        $this->commonwords[178] = 'question';
        $this->commonwords[179] = 'during';
        $this->commonwords[180] = 'play';
        $this->commonwords[181] = 'government';
        $this->commonwords[182] = 'run';
        $this->commonwords[183] = 'small';
        $this->commonwords[184] = 'number';
        $this->commonwords[185] = 'off';
        $this->commonwords[186] = 'always';
        $this->commonwords[187] = 'move';
        $this->commonwords[188] = 'night';
        $this->commonwords[189] = 'live';
        $this->commonwords[190] = 'Mr';
        $this->commonwords[191] = 'point';
        $this->commonwords[192] = 'believe';
        $this->commonwords[193] = 'hold';
        $this->commonwords[194] = 'today';
        $this->commonwords[195] = 'bring';
        $this->commonwords[196] = 'happen';
        $this->commonwords[197] = 'next';
        $this->commonwords[198] = 'without';
        $this->commonwords[199] = 'before';
        $this->commonwords[200] = 'large';
        $this->commonwords[201] = 'million';
        $this->commonwords[202] = 'must';
        $this->commonwords[203] = 'home';
        $this->commonwords[204] = 'under';
        $this->commonwords[205] = 'water';
        $this->commonwords[206] = 'room';
        $this->commonwords[207] = 'write';
        $this->commonwords[208] = 'mother';
        $this->commonwords[209] = 'area';
        $this->commonwords[210] = 'national';
        $this->commonwords[211] = 'money';
        $this->commonwords[212] = 'story';
        $this->commonwords[213] = 'young';
        $this->commonwords[214] = 'fact';
        $this->commonwords[215] = 'month';
        $this->commonwords[216] = 'different';
        $this->commonwords[217] = 'lot';
        $this->commonwords[218] = 'study';
        $this->commonwords[219] = 'book';
        $this->commonwords[220] = 'eye';
        $this->commonwords[221] = 'job';
        $this->commonwords[222] = 'word';
        $this->commonwords[223] = 'though';
        $this->commonwords[224] = 'business';
        $this->commonwords[225] = 'issue';
        $this->commonwords[226] = 'side';
        $this->commonwords[227] = 'kind';
        $this->commonwords[228] = 'four';
        $this->commonwords[229] = 'head';
        $this->commonwords[230] = 'far';
        $this->commonwords[231] = 'black';
        $this->commonwords[232] = 'long';
        $this->commonwords[233] = 'both';
        $this->commonwords[234] = 'little';
        $this->commonwords[235] = 'house';
        $this->commonwords[236] = 'yes';
        $this->commonwords[237] = 'since';
        $this->commonwords[238] = 'provide';
        $this->commonwords[239] = 'service';
        $this->commonwords[240] = 'around';
        $this->commonwords[241] = 'friend';
        $this->commonwords[242] = 'important';
        $this->commonwords[243] = 'father';
        $this->commonwords[244] = 'sit';
        $this->commonwords[245] = 'away';
        $this->commonwords[246] = 'until';
        $this->commonwords[247] = 'power';
        $this->commonwords[248] = 'hour';
        $this->commonwords[249] = 'game';
        $this->commonwords[250] = 'often';
        $this->commonwords[251] = 'yet';
        $this->commonwords[252] = 'line';
        $this->commonwords[253] = 'political';
        $this->commonwords[254] = 'end';
        $this->commonwords[255] = 'among';
        $this->commonwords[256] = 'ever';
        $this->commonwords[257] = 'stand';
        $this->commonwords[258] = 'bad';
        $this->commonwords[259] = 'lose';
        $this->commonwords[260] = 'however';
        $this->commonwords[261] = 'member';
        $this->commonwords[262] = 'pay';
        $this->commonwords[263] = 'law';
        $this->commonwords[264] = 'meet';
        $this->commonwords[265] = 'car';
        $this->commonwords[266] = 'city';
        $this->commonwords[267] = 'almost';
        $this->commonwords[268] = 'include';
        $this->commonwords[269] = 'continue';
        $this->commonwords[270] = 'set';
        $this->commonwords[271] = 'later';
        $this->commonwords[272] = 'community';
        $this->commonwords[273] = 'name';
        $this->commonwords[274] = 'five';
        $this->commonwords[275] = 'once';
        $this->commonwords[276] = 'white';
        $this->commonwords[277] = 'least';
        $this->commonwords[278] = 'president';
        $this->commonwords[279] = 'learn';
        $this->commonwords[280] = 'real';
        $this->commonwords[281] = 'change';
        $this->commonwords[282] = 'team';
        $this->commonwords[283] = 'minute';
        $this->commonwords[284] = 'best';
        $this->commonwords[285] = 'several';
        $this->commonwords[286] = 'idea';
        $this->commonwords[287] = 'kid';
        $this->commonwords[288] = 'body';
        $this->commonwords[289] = 'information';
        $this->commonwords[290] = 'nothing';
        $this->commonwords[291] = 'ago';
        $this->commonwords[292] = 'lead';
        $this->commonwords[293] = 'social';
        $this->commonwords[294] = 'understand';
        $this->commonwords[295] = 'whether';
        $this->commonwords[296] = 'watch';
        $this->commonwords[297] = 'together';
        $this->commonwords[298] = 'follow';
        $this->commonwords[299] = 'parent';
        $this->commonwords[300] = 'stop';
        $this->commonwords[301] = 'face';
        $this->commonwords[302] = 'anything';
        $this->commonwords[303] = 'create';
        $this->commonwords[304] = 'public';
        $this->commonwords[305] = 'already';
        $this->commonwords[306] = 'speak';
        $this->commonwords[307] = 'others';
        $this->commonwords[308] = 'read';
        $this->commonwords[309] = 'level';
        $this->commonwords[310] = 'allow';
        $this->commonwords[311] = 'add';
        $this->commonwords[312] = 'office';
        $this->commonwords[313] = 'spend';
        $this->commonwords[314] = 'door';
        $this->commonwords[315] = 'health';
        $this->commonwords[316] = 'person';
        $this->commonwords[317] = 'art';
        $this->commonwords[318] = 'sure';
        $this->commonwords[319] = 'war';
        $this->commonwords[320] = 'history';
        $this->commonwords[321] = 'party';
        $this->commonwords[322] = 'within';
        $this->commonwords[323] = 'grow';
        $this->commonwords[324] = 'result';
        $this->commonwords[325] = 'open';
        $this->commonwords[326] = 'morning';
        $this->commonwords[327] = 'walk';
        $this->commonwords[328] = 'reason';
        $this->commonwords[329] = 'low';
        $this->commonwords[330] = 'win';
        $this->commonwords[331] = 'research';
        $this->commonwords[332] = 'girl';
        $this->commonwords[333] = 'guy';
        $this->commonwords[334] = 'early';
        $this->commonwords[335] = 'food';
        $this->commonwords[336] = 'moment';
        $this->commonwords[337] = 'himself';
        $this->commonwords[338] = 'air';
        $this->commonwords[339] = 'teacher';
        $this->commonwords[340] = 'force';
        $this->commonwords[341] = 'offer';
        $this->commonwords[342] = 'enough';
        $this->commonwords[343] = 'education';
        $this->commonwords[344] = 'across';
        $this->commonwords[345] = 'although';
        $this->commonwords[346] = 'remember';
        $this->commonwords[347] = 'foot';
        $this->commonwords[348] = 'second';
        $this->commonwords[349] = 'boy';
        $this->commonwords[350] = 'maybe';
        $this->commonwords[351] = 'toward';
        $this->commonwords[352] = 'able';
        $this->commonwords[353] = 'age';
        $this->commonwords[354] = 'policy';
        $this->commonwords[355] = 'everything';
        $this->commonwords[356] = 'love';
        $this->commonwords[357] = 'process';
        $this->commonwords[358] = 'music';
        $this->commonwords[359] = 'including';
        $this->commonwords[360] = 'consider';
        $this->commonwords[361] = 'appear';
        $this->commonwords[362] = 'actually';
        $this->commonwords[363] = 'buy';
        $this->commonwords[364] = 'probably';
        $this->commonwords[365] = 'human';
        $this->commonwords[366] = 'wait';
        $this->commonwords[367] = 'serve';
        $this->commonwords[368] = 'market';
        $this->commonwords[369] = 'die';
        $this->commonwords[370] = 'send';
        $this->commonwords[371] = 'expect';
        $this->commonwords[372] = 'sense';
        $this->commonwords[373] = 'build';
        $this->commonwords[374] = 'stay';
        $this->commonwords[375] = 'fall';
        $this->commonwords[376] = 'oh';
        $this->commonwords[377] = 'nation';
        $this->commonwords[378] = 'plan';
        $this->commonwords[379] = 'cut';
        $this->commonwords[380] = 'college';
        $this->commonwords[381] = 'interest';
        $this->commonwords[382] = 'death';
        $this->commonwords[383] = 'course';
        $this->commonwords[384] = 'someone';
        $this->commonwords[385] = 'experience';
        $this->commonwords[386] = 'behind';
        $this->commonwords[387] = 'reach';
        $this->commonwords[388] = 'local';
        $this->commonwords[389] = 'kill';
        $this->commonwords[390] = 'six';
        $this->commonwords[391] = 'remain';
        $this->commonwords[392] = 'effect';
        $this->commonwords[393] = 'yeah';
        $this->commonwords[394] = 'suggest';
        $this->commonwords[395] = 'class';
        $this->commonwords[396] = 'control';
        $this->commonwords[397] = 'raise';
        $this->commonwords[398] = 'care';
        $this->commonwords[399] = 'perhaps';
        $this->commonwords[400] = 'late';
        $this->commonwords[401] = 'hard';
        $this->commonwords[402] = 'field';
        $this->commonwords[403] = 'else';
        $this->commonwords[404] = 'pass';
        $this->commonwords[405] = 'former';
        $this->commonwords[406] = 'sell';
        $this->commonwords[407] = 'major';
        $this->commonwords[408] = 'sometimes';
        $this->commonwords[409] = 'require';
        $this->commonwords[410] = 'along';
        $this->commonwords[411] = 'development';
        $this->commonwords[412] = 'themselves';
        $this->commonwords[413] = 'report';
        $this->commonwords[414] = 'role';
        $this->commonwords[415] = 'better';
        $this->commonwords[416] = 'economic';
        $this->commonwords[417] = 'effort';
        $this->commonwords[418] = 'decide';
        $this->commonwords[419] = 'rate';
        $this->commonwords[420] = 'strong';
        $this->commonwords[421] = 'possible';
        $this->commonwords[422] = 'heart';
        $this->commonwords[423] = 'drug';
        $this->commonwords[424] = 'leader';
        $this->commonwords[425] = 'light';
        $this->commonwords[426] = 'voice';
        $this->commonwords[427] = 'wife';
        $this->commonwords[428] = 'whole';
        $this->commonwords[429] = 'police';
        $this->commonwords[430] = 'mind';
        $this->commonwords[431] = 'finally';
        $this->commonwords[432] = 'pull';
        $this->commonwords[433] = 'return';
        $this->commonwords[434] = 'free';
        $this->commonwords[435] = 'military';
        $this->commonwords[436] = 'price';
        $this->commonwords[437] = 'less';
        $this->commonwords[438] = 'according';
        $this->commonwords[439] = 'decision';
        $this->commonwords[440] = 'explain';
        $this->commonwords[441] = 'son';
        $this->commonwords[442] = 'hope';
        $this->commonwords[443] = 'develop';
        $this->commonwords[444] = 'view';
        $this->commonwords[445] = 'relationship';
        $this->commonwords[446] = 'carry';
        $this->commonwords[447] = 'town';
        $this->commonwords[448] = 'road';
        $this->commonwords[449] = 'drive';
        $this->commonwords[450] = 'arm';
        $this->commonwords[451] = 'TRUE';
        $this->commonwords[452] = 'federal';
        $this->commonwords[453] = 'break';
        $this->commonwords[454] = 'difference';
        $this->commonwords[455] = 'thank';
        $this->commonwords[456] = 'receive';
        $this->commonwords[457] = 'value';
        $this->commonwords[458] = 'international';
        $this->commonwords[459] = 'building';
        $this->commonwords[460] = 'action';
        $this->commonwords[461] = 'full';
        $this->commonwords[462] = 'model';
        $this->commonwords[463] = 'join';
        $this->commonwords[464] = 'season';
        $this->commonwords[465] = 'society';
        $this->commonwords[466] = 'tax';
        $this->commonwords[467] = 'director';
        $this->commonwords[468] = 'position';
        $this->commonwords[469] = 'player';
        $this->commonwords[470] = 'agree';
        $this->commonwords[471] = 'especially';
        $this->commonwords[472] = 'record';
        $this->commonwords[473] = 'pick';
        $this->commonwords[474] = 'wear';
        $this->commonwords[475] = 'paper';
        $this->commonwords[476] = 'special';
        $this->commonwords[477] = 'space';
        $this->commonwords[478] = 'ground';
        $this->commonwords[479] = 'form';
        $this->commonwords[480] = 'support';
        $this->commonwords[481] = 'event';
        $this->commonwords[482] = 'official';
        $this->commonwords[483] = 'whose';
        $this->commonwords[484] = 'matter';
        $this->commonwords[485] = 'everyone';
        $this->commonwords[486] = 'center';
        $this->commonwords[487] = 'couple';
        $this->commonwords[488] = 'site';
        $this->commonwords[489] = 'project';
        $this->commonwords[490] = 'hit';
        $this->commonwords[491] = 'base';
        $this->commonwords[492] = 'activity';
        $this->commonwords[493] = 'star';
        $this->commonwords[494] = 'table';
        $this->commonwords[495] = 'court';
        $this->commonwords[496] = 'produce';
        $this->commonwords[497] = 'eat';
        $this->commonwords[498] = 'teach';
        $this->commonwords[499] = 'oil';
        $this->commonwords[500] = 'half';
        $this->commonwords[501] = 'situation';
        $this->commonwords[502] = 'easy';
        $this->commonwords[503] = 'cost';
        $this->commonwords[504] = 'industry';
        $this->commonwords[505] = 'figure';
        $this->commonwords[506] = 'street';
        $this->commonwords[507] = 'image';
        $this->commonwords[508] = 'itself';
        $this->commonwords[509] = 'phone';
        $this->commonwords[510] = 'either';
        $this->commonwords[511] = 'data';
        $this->commonwords[512] = 'cover';
        $this->commonwords[513] = 'quite';
        $this->commonwords[514] = 'picture';
        $this->commonwords[515] = 'clear';
        $this->commonwords[516] = 'practice';
        $this->commonwords[517] = 'piece';
        $this->commonwords[518] = 'land';
        $this->commonwords[519] = 'recent';
        $this->commonwords[520] = 'describe';
        $this->commonwords[521] = 'product';
        $this->commonwords[522] = 'doctor';
        $this->commonwords[523] = 'wall';
        $this->commonwords[524] = 'patient';
        $this->commonwords[525] = 'worker';
        $this->commonwords[526] = 'news';
        $this->commonwords[527] = 'test';
        $this->commonwords[528] = 'movie';
        $this->commonwords[529] = 'certain';
        $this->commonwords[530] = 'north';
        $this->commonwords[531] = 'personal';
        $this->commonwords[532] = 'simply';
        $this->commonwords[533] = 'third';
        $this->commonwords[534] = 'technology';
        $this->commonwords[535] = 'catch';
        $this->commonwords[536] = 'step';
        $this->commonwords[537] = 'baby';
        $this->commonwords[538] = 'computer';
        $this->commonwords[539] = 'type';
        $this->commonwords[540] = 'attention';
        $this->commonwords[541] = 'draw';
        $this->commonwords[542] = 'film';
        $this->commonwords[543] = 'Republican';
        $this->commonwords[544] = 'tree';
        $this->commonwords[545] = 'source';
        $this->commonwords[546] = 'red';
        $this->commonwords[547] = 'nearly';
        $this->commonwords[548] = 'organization';
        $this->commonwords[549] = 'choose';
        $this->commonwords[550] = 'cause';
        $this->commonwords[551] = 'hair';
        $this->commonwords[552] = 'century';
        $this->commonwords[553] = 'evidence';
        $this->commonwords[554] = 'window';
        $this->commonwords[555] = 'difficult';
        $this->commonwords[556] = 'listen';
        $this->commonwords[557] = 'soon';
        $this->commonwords[558] = 'culture';
        $this->commonwords[559] = 'billion';
        $this->commonwords[560] = 'chance';
        $this->commonwords[561] = 'brother';
        $this->commonwords[562] = 'energy';
        $this->commonwords[563] = 'period';
        $this->commonwords[564] = 'summer';
        $this->commonwords[565] = 'realize';
        $this->commonwords[566] = 'hundred';
        $this->commonwords[567] = 'available';
        $this->commonwords[568] = 'plant';
        $this->commonwords[569] = 'likely';
        $this->commonwords[570] = 'opportunity';
        $this->commonwords[571] = 'term';
        $this->commonwords[572] = 'short';
        $this->commonwords[573] = 'letter';
        $this->commonwords[574] = 'condition';
        $this->commonwords[575] = 'choice';
        $this->commonwords[576] = 'single';
        $this->commonwords[577] = 'rule';
        $this->commonwords[578] = 'daughter';
        $this->commonwords[579] = 'administration';
        $this->commonwords[580] = 'south';
        $this->commonwords[581] = 'husband';
        $this->commonwords[582] = 'Congress';
        $this->commonwords[583] = 'floor';
        $this->commonwords[584] = 'campaign';
        $this->commonwords[585] = 'material';
        $this->commonwords[586] = 'population';
        $this->commonwords[587] = 'economy';
        $this->commonwords[588] = 'medical';
        $this->commonwords[589] = 'hospital';
        $this->commonwords[590] = 'church';
        $this->commonwords[591] = 'close';
        $this->commonwords[592] = 'thousand';
        $this->commonwords[593] = 'risk';
        $this->commonwords[594] = 'current';
        $this->commonwords[595] = 'fire';
        $this->commonwords[596] = 'future';
        $this->commonwords[597] = 'wrong';
        $this->commonwords[598] = 'involve';
        $this->commonwords[599] = 'defense';
        $this->commonwords[600] = 'anyone';
        $this->commonwords[601] = 'increase';
        $this->commonwords[602] = 'security';
        $this->commonwords[603] = 'bank';
        $this->commonwords[604] = 'myself';
        $this->commonwords[605] = 'certainly';
        $this->commonwords[606] = 'west';
        $this->commonwords[607] = 'sport';
        $this->commonwords[608] = 'board';
        $this->commonwords[609] = 'seek';
        $this->commonwords[610] = 'per';
        $this->commonwords[611] = 'subject';
        $this->commonwords[612] = 'officer';
        $this->commonwords[613] = 'private';
        $this->commonwords[614] = 'rest';
        $this->commonwords[615] = 'behavior';
        $this->commonwords[616] = 'deal';
        $this->commonwords[617] = 'performance';
        $this->commonwords[618] = 'fight';
        $this->commonwords[619] = 'throw';
        $this->commonwords[620] = 'top';
        $this->commonwords[621] = 'quickly';
        $this->commonwords[622] = 'past';
        $this->commonwords[623] = 'goal';
        $this->commonwords[624] = 'bed';
        $this->commonwords[625] = 'order';
        $this->commonwords[626] = 'author';
        $this->commonwords[627] = 'fill';
        $this->commonwords[628] = 'represent';
        $this->commonwords[629] = 'focus';
        $this->commonwords[630] = 'foreign';
        $this->commonwords[631] = 'drop';
        $this->commonwords[632] = 'blood';
        $this->commonwords[633] = 'upon';
        $this->commonwords[634] = 'agency';
        $this->commonwords[635] = 'push';
        $this->commonwords[636] = 'nature';
        $this->commonwords[637] = 'color';
        $this->commonwords[638] = 'recently';
        $this->commonwords[639] = 'store';
        $this->commonwords[640] = 'reduce';
        $this->commonwords[641] = 'sound';
        $this->commonwords[642] = 'note';
        $this->commonwords[643] = 'fine';
        $this->commonwords[644] = 'near';
        $this->commonwords[645] = 'movement';
        $this->commonwords[646] = 'page';
        $this->commonwords[647] = 'enter';
        $this->commonwords[648] = 'share';
        $this->commonwords[649] = 'common';
        $this->commonwords[650] = 'poor';
        $this->commonwords[651] = 'natural';
        $this->commonwords[652] = 'race';
        $this->commonwords[653] = 'concern';
        $this->commonwords[654] = 'series';
        $this->commonwords[655] = 'significant';
        $this->commonwords[656] = 'similar';
        $this->commonwords[657] = 'hot';
        $this->commonwords[658] = 'language';
        $this->commonwords[659] = 'usually';
        $this->commonwords[660] = 'response';
        $this->commonwords[661] = 'dead';
        $this->commonwords[662] = 'rise';
        $this->commonwords[663] = 'animal';
        $this->commonwords[664] = 'factor';
        $this->commonwords[665] = 'decade';
        $this->commonwords[666] = 'article';
        $this->commonwords[667] = 'shoot';
        $this->commonwords[668] = 'east';
        $this->commonwords[669] = 'save';
        $this->commonwords[670] = 'seven';
        $this->commonwords[671] = 'artist';
        $this->commonwords[672] = 'scene';
        $this->commonwords[673] = 'stock';
        $this->commonwords[674] = 'career';
        $this->commonwords[675] = 'despite';
        $this->commonwords[676] = 'central';
        $this->commonwords[677] = 'eight';
        $this->commonwords[678] = 'thus';
        $this->commonwords[679] = 'treatment';
        $this->commonwords[680] = 'beyond';
        $this->commonwords[681] = 'happy';
        $this->commonwords[682] = 'exactly';
        $this->commonwords[683] = 'protect';
        $this->commonwords[684] = 'approach';
        $this->commonwords[685] = 'lie';
        $this->commonwords[686] = 'size';
        $this->commonwords[687] = 'dog';
        $this->commonwords[688] = 'fund';
        $this->commonwords[689] = 'serious';
        $this->commonwords[690] = 'occur';
        $this->commonwords[691] = 'media';
        $this->commonwords[692] = 'ready';
        $this->commonwords[693] = 'sign';
        $this->commonwords[694] = 'thought';
        $this->commonwords[695] = 'list';
        $this->commonwords[696] = 'individual';
        $this->commonwords[697] = 'simple';
        $this->commonwords[698] = 'quality';
        $this->commonwords[699] = 'pressure';
        $this->commonwords[700] = 'accept';
        $this->commonwords[701] = 'answer';
        $this->commonwords[702] = 'resource';
        $this->commonwords[703] = 'identify';
        $this->commonwords[704] = 'left';
        $this->commonwords[705] = 'meeting';
        $this->commonwords[706] = 'determine';
        $this->commonwords[707] = 'prepare';
        $this->commonwords[708] = 'disease';
        $this->commonwords[709] = 'whatever';
        $this->commonwords[710] = 'success';
        $this->commonwords[711] = 'argue';
        $this->commonwords[712] = 'cup';
        $this->commonwords[713] = 'particularly';
        $this->commonwords[714] = 'amount';
        $this->commonwords[715] = 'ability';
        $this->commonwords[716] = 'staff';
        $this->commonwords[717] = 'recognize';
        $this->commonwords[718] = 'indicate';
        $this->commonwords[719] = 'character';
        $this->commonwords[720] = 'growth';
        $this->commonwords[721] = 'loss';
        $this->commonwords[722] = 'degree';
        $this->commonwords[723] = 'wonder';
        $this->commonwords[724] = 'attack';
        $this->commonwords[725] = 'herself';
        $this->commonwords[726] = 'region';
        $this->commonwords[727] = 'television';
        $this->commonwords[728] = 'box';
        $this->commonwords[729] = 'TV';
        $this->commonwords[730] = 'training';
        $this->commonwords[731] = 'pretty';
        $this->commonwords[732] = 'trade';
        $this->commonwords[733] = 'election';
        $this->commonwords[734] = 'everybody';
        $this->commonwords[735] = 'physical';
        $this->commonwords[736] = 'lay';
        $this->commonwords[737] = 'general';
        $this->commonwords[738] = 'feeling';
        $this->commonwords[739] = 'standard';
        $this->commonwords[740] = 'bill';
        $this->commonwords[741] = 'message';
        $this->commonwords[742] = 'fail';
        $this->commonwords[743] = 'outside';
        $this->commonwords[744] = 'arrive';
        $this->commonwords[745] = 'analysis';
        $this->commonwords[746] = 'benefit';
        $this->commonwords[747] = 'sex';
        $this->commonwords[748] = 'forward';
        $this->commonwords[749] = 'lawyer';
        $this->commonwords[750] = 'present';
        $this->commonwords[751] = 'section';
        $this->commonwords[752] = 'environmental';
        $this->commonwords[753] = 'glass';
        $this->commonwords[754] = 'skill';
        $this->commonwords[755] = 'sister';
        $this->commonwords[756] = 'PM';
        $this->commonwords[757] = 'professor';
        $this->commonwords[758] = 'operation';
        $this->commonwords[759] = 'financial';
        $this->commonwords[760] = 'crime';
        $this->commonwords[761] = 'stage';
        $this->commonwords[762] = 'ok';
        $this->commonwords[763] = 'compare';
        $this->commonwords[764] = 'authority';
        $this->commonwords[765] = 'miss';
        $this->commonwords[766] = 'design';
        $this->commonwords[767] = 'sort';
        $this->commonwords[768] = 'act';
        $this->commonwords[769] = 'ten';
        $this->commonwords[770] = 'knowledge';
        $this->commonwords[771] = 'gun';
        $this->commonwords[772] = 'station';
        $this->commonwords[773] = 'blue';
        $this->commonwords[774] = 'strategy';
        $this->commonwords[775] = 'clearly';
        $this->commonwords[776] = 'discuss';
        $this->commonwords[777] = 'indeed';
        $this->commonwords[778] = 'truth';
        $this->commonwords[779] = 'song';
        $this->commonwords[780] = 'example';
        $this->commonwords[781] = 'democratic';
        $this->commonwords[782] = 'check';
        $this->commonwords[783] = 'environment';
        $this->commonwords[784] = 'leg';
        $this->commonwords[785] = 'dark';
        $this->commonwords[786] = 'various';
        $this->commonwords[787] = 'rather';
        $this->commonwords[788] = 'laugh';
        $this->commonwords[789] = 'guess';
        $this->commonwords[790] = 'executive';
        $this->commonwords[791] = 'prove';
        $this->commonwords[792] = 'hang';
        $this->commonwords[793] = 'entire';
        $this->commonwords[794] = 'rock';
        $this->commonwords[795] = 'forget';
        $this->commonwords[796] = 'claim';
        $this->commonwords[797] = 'remove';
        $this->commonwords[798] = 'manager';
        $this->commonwords[799] = 'enjoy';
        $this->commonwords[800] = 'network';
        $this->commonwords[801] = 'legal';
        $this->commonwords[802] = 'religious';
        $this->commonwords[803] = 'cold';
        $this->commonwords[804] = 'final';
        $this->commonwords[805] = 'main';
        $this->commonwords[806] = 'science';
        $this->commonwords[807] = 'green';
        $this->commonwords[808] = 'memory';
        $this->commonwords[809] = 'card';
        $this->commonwords[810] = 'above';
        $this->commonwords[811] = 'seat';
        $this->commonwords[812] = 'cell';
        $this->commonwords[813] = 'establish';
        $this->commonwords[814] = 'nice';
        $this->commonwords[815] = 'trial';
        $this->commonwords[816] = 'expert';
        $this->commonwords[817] = 'spring';
        $this->commonwords[818] = 'firm';
        $this->commonwords[819] = 'Democrat';
        $this->commonwords[820] = 'radio';
        $this->commonwords[821] = 'visit';
        $this->commonwords[822] = 'management';
        $this->commonwords[823] = 'avoid';
        $this->commonwords[824] = 'imagine';
        $this->commonwords[825] = 'tonight';
        $this->commonwords[826] = 'huge';
        $this->commonwords[827] = 'ball';
        $this->commonwords[828] = 'finish';
        $this->commonwords[829] = 'yourself';
        $this->commonwords[830] = 'theory';
        $this->commonwords[831] = 'impact';
        $this->commonwords[832] = 'respond';
        $this->commonwords[833] = 'statement';
        $this->commonwords[834] = 'maintain';
        $this->commonwords[835] = 'charge';
        $this->commonwords[836] = 'popular';
        $this->commonwords[837] = 'traditional';
        $this->commonwords[838] = 'onto';
        $this->commonwords[839] = 'reveal';
        $this->commonwords[840] = 'direction';
        $this->commonwords[841] = 'weapon';
        $this->commonwords[842] = 'employee';
        $this->commonwords[843] = 'cultural';
        $this->commonwords[844] = 'contain';
        $this->commonwords[845] = 'peace';
        $this->commonwords[846] = 'pain';
        $this->commonwords[847] = 'apply';
        $this->commonwords[848] = 'measure';
        $this->commonwords[849] = 'wide';
        $this->commonwords[850] = 'shake';
        $this->commonwords[851] = 'fly';
        $this->commonwords[852] = 'interview';
        $this->commonwords[853] = 'manage';
        $this->commonwords[854] = 'chair';
        $this->commonwords[855] = 'fish';
        $this->commonwords[856] = 'particular';
        $this->commonwords[857] = 'camera';
        $this->commonwords[858] = 'structure';
        $this->commonwords[859] = 'politics';
        $this->commonwords[860] = 'perform';
        $this->commonwords[861] = 'bit';
        $this->commonwords[862] = 'weight';
        $this->commonwords[863] = 'suddenly';
        $this->commonwords[864] = 'discover';
        $this->commonwords[865] = 'candidate';
        $this->commonwords[866] = 'production';
        $this->commonwords[867] = 'treat';
        $this->commonwords[868] = 'trip';
        $this->commonwords[869] = 'evening';
        $this->commonwords[870] = 'affect';
        $this->commonwords[871] = 'inside';
        $this->commonwords[872] = 'conference';
        $this->commonwords[873] = 'unit';
        $this->commonwords[874] = 'style';
        $this->commonwords[875] = 'adult';
        $this->commonwords[876] = 'worry';
        $this->commonwords[877] = 'range';
        $this->commonwords[878] = 'mention';
        $this->commonwords[879] = 'deep';
        $this->commonwords[880] = 'edge';
        $this->commonwords[881] = 'specific';
        $this->commonwords[882] = 'writer';
        $this->commonwords[883] = 'trouble';
        $this->commonwords[884] = 'necessary';
        $this->commonwords[885] = 'throughout';
        $this->commonwords[886] = 'challenge';
        $this->commonwords[887] = 'fear';
        $this->commonwords[888] = 'shoulder';
        $this->commonwords[889] = 'institution';
        $this->commonwords[890] = 'middle';
        $this->commonwords[891] = 'sea';
        $this->commonwords[892] = 'dream';
        $this->commonwords[893] = 'bar';
        $this->commonwords[894] = 'beautiful';
        $this->commonwords[895] = 'property';
        $this->commonwords[896] = 'instead';
        $this->commonwords[897] = 'improve';
        $this->commonwords[898] = 'stuff';
        $this->commonwords[899] = 'detail';
        $this->commonwords[900] = 'method';
        $this->commonwords[901] = 'somebody';
        $this->commonwords[902] = 'magazine';
        $this->commonwords[903] = 'hotel';
        $this->commonwords[904] = 'soldier';
        $this->commonwords[905] = 'reflect';
        $this->commonwords[906] = 'heavy';
        $this->commonwords[907] = 'sexual';
        $this->commonwords[908] = 'bag';
        $this->commonwords[909] = 'heat';
        $this->commonwords[910] = 'marriage';
        $this->commonwords[911] = 'tough';
        $this->commonwords[912] = 'sing';
        $this->commonwords[913] = 'surface';
        $this->commonwords[914] = 'purpose';
        $this->commonwords[915] = 'exist';
        $this->commonwords[916] = 'pattern';
        $this->commonwords[917] = 'whom';
        $this->commonwords[918] = 'skin';
        $this->commonwords[919] = 'agent';
        $this->commonwords[920] = 'owner';
        $this->commonwords[921] = 'machine';
        $this->commonwords[922] = 'gas';
        $this->commonwords[923] = 'ahead';
        $this->commonwords[924] = 'generation';
        $this->commonwords[925] = 'commercial';
        $this->commonwords[926] = 'address';
        $this->commonwords[927] = 'cancer';
        $this->commonwords[928] = 'item';
        $this->commonwords[929] = 'reality';
        $this->commonwords[930] = 'coach';
        $this->commonwords[931] = 'Mrs';
        $this->commonwords[932] = 'yard';
        $this->commonwords[933] = 'beat';
        $this->commonwords[934] = 'violence';
        $this->commonwords[935] = 'total';
        $this->commonwords[936] = 'tend';
        $this->commonwords[937] = 'investment';
        $this->commonwords[938] = 'discussion';
        $this->commonwords[939] = 'finger';
        $this->commonwords[940] = 'garden';
        $this->commonwords[941] = 'notice';
        $this->commonwords[942] = 'collection';
        $this->commonwords[943] = 'modern';
        $this->commonwords[944] = 'task';
        $this->commonwords[945] = 'partner';
        $this->commonwords[946] = 'positive';
        $this->commonwords[947] = 'civil';
        $this->commonwords[948] = 'kitchen';
        $this->commonwords[949] = 'consumer';
        $this->commonwords[950] = 'shot';
        $this->commonwords[951] = 'budget';
        $this->commonwords[952] = 'wish';
        $this->commonwords[953] = 'painting';
        $this->commonwords[954] = 'scientist';
        $this->commonwords[955] = 'safe';
        $this->commonwords[956] = 'agreement';
        $this->commonwords[957] = 'capital';
        $this->commonwords[958] = 'mouth';
        $this->commonwords[959] = 'nor';
        $this->commonwords[960] = 'victim';
        $this->commonwords[961] = 'newspaper';
        $this->commonwords[962] = 'threat';
        $this->commonwords[963] = 'responsibility';
        $this->commonwords[964] = 'smile';
        $this->commonwords[965] = 'attorney';
        $this->commonwords[966] = 'score';
        $this->commonwords[967] = 'account';
        $this->commonwords[968] = 'interesting';
        $this->commonwords[969] = 'audience';
        $this->commonwords[970] = 'rich';
        $this->commonwords[971] = 'dinner';
        $this->commonwords[972] = 'vote';
        $this->commonwords[973] = 'western';
        $this->commonwords[974] = 'relate';
        $this->commonwords[975] = 'travel';
        $this->commonwords[976] = 'debate';
        $this->commonwords[977] = 'prevent';
        $this->commonwords[978] = 'citizen';
        $this->commonwords[979] = 'majority';
        $this->commonwords[980] = 'none';
        $this->commonwords[981] = 'front';
        $this->commonwords[982] = 'born';
        $this->commonwords[983] = 'admit';
        $this->commonwords[984] = 'senior';
        $this->commonwords[985] = 'assume';
        $this->commonwords[986] = 'wind';
        $this->commonwords[987] = 'key';
        $this->commonwords[988] = 'professional';
        $this->commonwords[989] = 'mission';
        $this->commonwords[990] = 'fast';
        $this->commonwords[991] = 'alone';
        $this->commonwords[992] = 'customer';
        $this->commonwords[993] = 'suffer';
        $this->commonwords[994] = 'speech';
        $this->commonwords[995] = 'successful';
        $this->commonwords[996] = 'option';
        $this->commonwords[997] = 'participant';
        $this->commonwords[998] = 'southern';
        $this->commonwords[999] = 'fresh';
        $this->commonwords[1000] = 'are';
        $this->commonwords[1001] = 'were';
        $this->commonwords[1002] = 'is';
        $this->commonwords[1003] = 'was';
        $this->commonwords[] = 'http';
        $this->commonwords[] = 'www';
        $this->commonwords[] = 'https';
        $this->commonwords[] = 'com';
        $this->commonwords[] = 'has';
        $this->commonwords[] = 'going';
        $this->commonwords[] = 'dont';
        $this->commonwords[] = 'had';
        $this->commonwords[] = 'got';
        $this->commonwords[] = 'been';

        foreach ($config as $name => $value) {

            switch ($name) {

                case 'min_size':
                case 'max_size':
                    $this->config[$name] = (int) $value;
                    break;

                case 'allow_numbers':
                case 'get_uris':
                case 'old_get_html':
                case 'get_html':
                case 'get_bbcode':
                    $this->config[$name] = (bool) $value;
                    break;

                default:
                    throw new Exception("b8_lexer_default: Unknown configuration key: \"$name\"");
            }
        }
    }

    /**
     * Splits a text to tokens.
     *
     * @access public
     * @param string $text
     * @return mixed Returns a list of tokens or an error code
     */
    public function get_tokens($text) {

        # Check if we actually have a string ...
        if (is_string($text) === FALSE)
            return self::LEXER_TEXT_NOT_STRING;

        # ... and if it's empty
        if (empty($text) === TRUE)
            return self::LEXER_TEXT_EMPTY;

        # Re-convert the text to the original characters coded in UTF-8, as
        # they have been coded in html entities during the post process
        $this->_processed_text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $this->_processed_text = preg_replace('/(^|\b)@\S*($|\b)/', '', $this->_processed_text); // remove @someone
        $this->_processed_text = preg_replace('/(^|\b)#\S*($|\b)/', '', $this->_processed_text); // remove hashtags                

        #Remove special chars
        $this->_processed_text = preg_replace('/[^A-Za-z0-9 \\n\-]/', '', $this->_processed_text);

        # Reset the token list
        $this->_tokens = array();
        if ($this->config['get_uris'] === TRUE) {
            # Get URIs
            $this->_get_uris($this->_processed_text);
        }

        if ($this->config['old_get_html'] === TRUE) {
            # Get HTML - the old way without removing the found tags
            $this->_old_get_html($this->_processed_text);
        }

        if ($this->config['get_html'] === TRUE) {
            # Get HTML
            $this->_get_markup($this->_processed_text, $this->regexp['html']);
        }

        if ($this->config['get_bbcode'] === TRUE) {
            # Get BBCode
            $this->_get_markup($this->_processed_text, $this->regexp['bbcode']);
        }

        # We always want to do a raw split of the (remaining) text, so:
        $this->_raw_split($this->_processed_text);

        # Be sure not to return an empty array
        if (count($this->_tokens) == 0)
            $this->_tokens['b8*no_tokens'] = 1;

        foreach ($this->_tokens as $token => $times) {

            $token = str_replace("'", "", $token);
            if (in_array(strtolower($token), $this->commonwords)) {

                unset($this->_tokens[$token]);
            }
        }

        # Return a list of all found tokens
        return $this->_tokens;
    }

    /**
     * Validates a token.
     *
     * @access private
     * @param string $token The token string.
     * @return boolean Returns TRUE if the token is valid, otherwise returns FALSE.
     */
    private function _is_valid($token) {

        # Just to be sure that the token's name won't collide with b8's internal variables
        if (substr($token, 0, 3) == 'b8*')
            return FALSE;

        # Validate the size of the token

        $len = strlen($token);

        if ($len < $this->config['min_size'] or $len > $this->config['max_size'])
            return FALSE;

        # We may want to exclude pure numbers
        if ($this->config['allow_numbers'] === FALSE) {
            if (preg_match($this->regexp['numbers'], $token) > 0)
                return FALSE;
        }

        # Token is okay
        return TRUE;
    }

    /**
     * Checks the validity of a token and adds it to the token list if it's valid.
     *
     * @access private
     * @param string $token
     * @param bool $remove When set to TRUE, the string given in $word_to_remove is removed from the text passed to the lexer.
     * @param string $word_to_remove
     * @return void
     */
    private function _add_token($token, $remove, $word_to_remove) {

        # Check the validity of the token
        if ($this->_is_valid($token) === FALSE)
            return;

        # Add it to the list or increase it's counter
        if (isset($this->_tokens[$token]) === FALSE)
            $this->_tokens[$token] = 1;
        else
            $this->_tokens[$token] += 1;


        # If requested, remove the word or it's original version from the text
        if ($remove === TRUE)
            $this->_processed_text = str_replace($word_to_remove, '', $this->_processed_text);
    }

    /**
     * Gets URIs.
     *
     * @access private
     * @param string $text
     * @return void
     */
    private function _get_uris($text) {

        # Find URIs
        preg_match_all($this->regexp['uris'], $text, $raw_tokens);

        foreach ($raw_tokens[1] as $word) {

            # Remove a possible trailing dot
            $word = rtrim($word, '.');

            # Try to add the found tokens to the list
            $this->_add_token($word, TRUE, $word);

            # Also process the parts of the found URIs
            $this->_raw_split($word);
        }
    }

    /**
     * Gets HTML or BBCode markup, depending on the regexp used.
     *
     * @access private
     * @param string $text
     * @param string $regexp
     * @return void
     */
    private function _get_markup($text, $regexp) {

        # Search for the markup
        preg_match_all($regexp, $text, $raw_tokens);

        foreach ($raw_tokens[1] as $word) {

            $actual_word = $word;

            # If the tag has parameters, just use the tag itself
            if (strpos($word, ' ') !== FALSE) {
                preg_match($this->regexp['tagname'], $word, $match);
                $actual_word = $match[1];
                $word = "$actual_word..." . substr($word, -1);
            }

            # Try to add the found tokens to the list
            $this->_add_token($word, TRUE, $actual_word);
        }
    }

    /**
     * The function to get HTML code used til b8 0.5.2.
     *
     * @access private
     * @param string $text
     * @return void
     */
    private function _old_get_html($text) {

        # Search for the markup
        preg_match_all($this->regexp['html'], $text, $raw_tokens);

        foreach ($raw_tokens[1] as $word) {

            # If the tag has parameters, just use the tag itself
            if (strpos($word, ' ') !== FALSE) {
                preg_match($this->regexp['tagname'], $word, $match);
                $word = "{$match[1]}...>";
            }

            # Try to add the found tokens to the list
            $this->_add_token($word, FALSE, NULL);
        }
    }

    /**
     * Does a raw split.
     *
     * @access private
     * @param string $text
     * @return void
     */
    private function _raw_split($text) {
        foreach (preg_split($this->regexp['raw_split'], $text) as $word) {
            # Check the word and add it to the token list if it's valid
            $this->_add_token($word, FALSE, NULL);
        }
    }

}

?>
