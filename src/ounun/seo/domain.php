<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\seo;


class domain
{
    /** 模板数量 */
    const tpl_count = 1;

    /** 二级顶级域名 */
    const pix_sub = [
        'com.af', 'net.af', 'org.af', 'com.ag', 'net.ag', 'nom.ag', 'org.ag', 'com.ar', 'co.at', 'or.at', 'com.au', 'id.au', 'net.au', 'org.au',
        'com.br', 'co.bz', 'com.bz', 'net.bz', 'org.bz', 'co.cm', 'com.cm', 'net.cm',
        'ac.cn', 'ah.cn', 'bj.cn', 'com.cn', 'cq.cn', 'fj.cn', 'gd.cn', 'gov.cn', 'gs.cn', 'gx.cn', 'gz.cn', 'ha.cn', 'hb.cn', 'he.cn', 'hi.cn', 'hk.cn', 'hl.cn', 'hn.cn', 'jl.cn', 'js.cn', 'jx.cn', 'ln.cn', 'mo.cn', 'net.cn', 'nm.cn', 'nx.cn', 'org.cn', 'qh.cn', 'sc.cn', 'sd.cn', 'sh.cn', 'sn.cn', 'sx.cn', 'tj.cn', 'tw.cn', 'xj.cn', 'xz.cn', 'yn.cn', 'zj.cn',
        'com.co',
        'ar.com', 'br.com', 'cn.com', 'de.com', 'eu.com', 'gb.com', 'gr.com', 'hu.com', 'jpn.com', 'kr.com', 'no.com', 'qc.com', 'ru.com', 'sa.com', 'se.com', 'uk.com', 'us.com', 'uy.com', 'za.com',
        'net.co', 'nom.co', 'com.de',
        'com.ec', 'fin.ec', 'info.ec', 'med.ec', 'net.ec', 'pro.ec', 'com.es', 'nom.es', 'org.es', 'co.gg', 'net.gg', 'org.gg', 'co.gl', 'com.gl', 'net.gl', 'org.gl', 'com.gr',
        'co.gy', 'com.gy', 'net.gy', 'com.hk', 'com.hn', 'net.hn', 'org.hn', 'com.ht', 'info.ht', 'net.ht', 'org.ht', 'co.im', 'com.im', 'net.im', 'org.im', 'co.in', 'firm.in', 'gen.in', 'ind.in', 'net.in', 'org.in', 'co.je', 'net.je', 'org.je', 'biz.ki', 'com.ki', 'info.ki', 'net.ki', 'org.ki', 'co.kr', 'co.lc', 'com.lc', 'net.lc', 'org.lc', 'com.lv', 'net.lv', 'org.lv', 'co.mg', 'com.mg', 'net.mg', 'org.mg', 'co.ms', 'com.ms', 'org.ms', 'co.mu', 'com.mu', 'net.mu', 'org.mu', 'com.mx', 'org.mx', 'gb.net', 'hu.net', 'in.net', 'jp.net', 'se.net', 'uk.net', 'co.nl', 'co.nz', 'kiwi.nz', 'net.nz', 'org.nz', 'com.pe', 'net.pe', 'nom.pe', 'org.pe', 'com.ph', 'net.ph', 'org.ph', 'biz.pl', 'com.pl', 'info.pl', 'net.pl', 'org.pl', 'com.pt', 'com.ro', 'org.ro', 'com.ru', 'net.ru', 'org.ru', 'pp.ru', 'com.sb', 'net.sb', 'org.sb', 'com.sc', 'net.sc', 'org.sc', 'com.sg', 'com.so', 'net.so', 'org.so', 'club.tw', 'com.tw', 'ebiz.tw', 'game.tw', 'idv.tw', 'co.uk', 'me.uk', 'org.uk',
        'com.vc', 'net.vc', 'org.vc', 'ac.vn', 'com.vn', 'gov.vn', 'health.vn', 'info.vn', 'int.vn', 'name.vn', 'net.vn', 'org.vn', 'pro.vn',
        'co.za'
    ];

    /** 顶级域名 */
    const pix = [
        'ac', 'accountant', 'ae', 'aero', 'af', 'ag', 'am', 'as', 'asia', 'at', 'auction', 'audio', 'auto',
        'band', 'bar', 'be', 'best', 'bi', 'bid', 'bike', 'biz', 'black', 'blue', 'business', 'bz',
        'ca', 'cab', 'cafe', 'camera', 'car', 'cards', 'cars', 'cash', 'cc', 'center', 'ceo', 'ch', 'chat', 'city', 'cl', 'click', 'clothing', 'club', 'cm', 'cn', 'co', 'coffee', 'college', 'com', 'company', 'construction', 'cool', 'credit', 'cricket', 'cx', 'cz',
        'date', 'de', 'desi', 'design', 'diet', 'dk', 'dm', 'dog', 'domains', 'download',
        'ec', 'ee', 'email', 'engineer', 'equipment', 'es', 'estate', 'eu', 'expert',
        'faith', 'family', 'fans', 'feedback', 'fi', 'fish', 'fit', 'flowers', 'fm', 'fr', 'fun', 'fund', 'fyi',
        'game', 'games', 'gd', 'gg', 'gift', 'gives', 'gl', 'gold', 'gr', 'group', 'gs', 'guru', 'gy',
        'haus', 'help', 'hk', 'hn', 'holiday', 'host', 'hosting', 'house', 'ht', 'hu',
        'ie', 'im', 'in', 'info', 'ink', 'io', 'is', 'it',
        'je', 'jobs', 'jp', 'ki', 'kim', 'kr', 'la', 'land', 'lawyer', 'lc', 'li', 'life', 'link', 'live', 'loan', 'lol', 'love', 'lt', 'ltd', 'lu', 'lv',
        'market', 'marketing', 'mba', 'md', 'me', 'media', 'men', 'mg', 'mn', 'mobi', 'mom', 'money', 'ms', 'mu', 'mx', 'my',
        'name', 'net', 'network', 'news', 'nf', 'ninja', 'nl', 'no', 'nu',
        'online', 'ooo', 'org', 'party', 'pe', 'pet', 'ph', 'photo', 'photography', 'photos', 'pics', 'pink', 'pk', 'pl', 'plus', 'pm', 'press', 'pro', 'property', 'ps', 'pt', 'pub', 'pw',
        'racing', 're', 'red', 'ren', 'rent', 'review', 'reviews', 'rip', 'ro', 'rocks', 'ru', 'run',
        'sale', 'sc', 'school', 'science', 'se', 'services', 'sex', 'sexy', 'sg', 'sh', 'shop', 'show', 'si', 'site', 'sk', 'so', 'social', 'software', 'solutions', 'space', 'store', 'studio', 'style', 'su', 'support', 'sx',
        'taipei', 'tax', 'tc', 'team', 'tech', 'tel', 'tf', 'tips', 'tk', 'tl', 'tm', 'today', 'tools', 'top', 'town', 'toys', 'trade', 'travel', 'tt', 'tv', 'tw',
        'ua', 'us', 'uz', 'vc', 'ventures', 'vet', 'vg', 'video', 'vin', 'vip', 'vn',
        'wang', 'watch', 'webcam', 'website', 'wf', 'wiki', 'win', 'wine', 'work', 'works', 'world', 'ws', 'wtf',
        'xin', 'xxx', 'xyz', 'yoga', 'yt', 'zone'
    ];

    /** 单词二级域名 */
    const sld = [
        'liquid', 'dust', 'nuan', 'wake', 'service', 'afraid', 'vote', 'mi', 'jing', 'family', 'ka', 'rock', 'inch', 'steel', 'wood', 'qing', 'trick', 'xie', 'spoon', 'defend', 'ride',
        'white', 'total', 'whistle', 'low', 'me', 'gaiety', 'ink', 'about', 'honest', 'chicken', 'lid', 'happen', 'money', 'ye', 'form', 'maybe', 'mention', 'ambitious', 'ni', 'scissors',
        'treasury', 'material', 'college', 'drown', 'scold', 'complete', 'zhuan', 'shade', 'expense', 'lun', 'half', 'lie', 'struggle', 'gen', 'represent', 'object', 'every', 'astonish',
        'swing', 'approval', 'disrespect', 'pa', 'sake', 'vessel', 'center', 'poison', 'kneel', 'now', 'worth', 'thin', 'cu', 'bunch', 'aside', 'peng', 'verse', 'except', 'entrance', 'scale',
        'bucket', 'applause', 'citizen', 'kong', 'discomfort', 'say', 'mystery', 'treasure', 'cow', 'prevent', 'message', 'agent', 'basket', 'deal', 'best', 'vnang', 'critic', 'advantage', 'good', 'niece', 'shuang', 'excess',
        'splendid', 'stripe', 'button', 'neighbor', 'yesterday', 'ti', 'zhong', 'push', 'future', 'passage', 'nail', 'government', 'shang', 'toe', 'holy', 'whisper', 'xiong', 'queen', 'walk', 'military', 'shan', 'jealousy', 'street',
        'bundle', 'action', 'slippery', 'chuan', 'root', 'rivalry', 'sorry', 'camp', 'package', 'baggage', 'wai', 'road', 'cage', 'tear', 'trust', 'bay', 'never', 'dissatisfaction', 'fun', 'bribery', 'round', 'dissatisfy', 'fold', 'admit',
        'black', 'nai', 'multiplication', 'key', 'iu', 'barber', 'harden', 'hurrah', 'hun', 'inn', 'here', 'cultivate', 'hit', 'brush', 'kuo', 'gun', 'digestion', 'boil', 'pink', 'ditch', 'extraordinary', 'lead', 'border', 'confident', 'destroy',
        'nou', 'zao', 'loyalty', 'effect', 'perhaps', 'shorten', 'ou', 'when', 'ambition', 'advice', 'modest', 'respect', 'argue', 'comfort51.committee', 'shilling', 'broken', 'zhou', 'spring', 'imitate', 'either', 'za', 'pleasure', 'frighten', 'mountain',
        'pi', 'former', 'railroad', 'disease', 'disregard', 'descendant', 'pity', 'kuan', 'land', 'tian', 'practice', 'inside', 'knee', 'off', 'reflect', 'yuan', 'long', 'heavy', 'shou', 'game', 'down', 'stay', 'real', 'sugar', 'possession', 'ju', 'hollow',
        'hide', 'descend', 'politician', 'music', 'each', 'nian', 'dou', 'guide', 'gong', 'spend', 'rival', 'pipe', 'hei', 'ownership', 'moral', 'away', 'nowhere', 'strike', 'continue', 'while', 'fright', 'hua', 'widow', 'te', 'shock', 'treat', 'type',
        'discipline', 'some', 'solemn', 'ornament', 'solid', 'corner', 'beard', 'sensitive', 'month', 'reserve', 'lack', 'circle', 'simple', 'alive', 'test', 'pu', 'length', 'instrument', 'evil', 'love', 'count', 'guo', 'repetition', 'boy', 'stiffen',
        'wisdom', 'resist', 'as', 'hearing', 'confidence', 'station', 'shoulder', 'convenient', 'regard', 'deepen', 'niang', 'less', 'ei', 'swallow', 'girl', 'patient', 'south', 'crowd', 'arrange', 'anywhere', 'thank', 'pearl', 'zou', 'cushion', 'live',
        'run', 'ying', 'east', 'division', 'wage', 'content', 'skin', 'century', 'fertile', 'ao', 'sudden572basic', 'cough', 'headache', 'bitter', 'chance', 'omit', 'truth', 'shua', 'everlasting', 'under', 'chao', 'observe', 'public', 'framework', 'until',
        'urge', 'tomorrow', 'supper', 'network', 'lve', 'deep', 'wash', 'actor', 'quarrel', 'expression', 'ing', 'scratch', 'cause', 'special', 'toward', 'pan', 'speak', 'lift', 'match', 'something', 'skirt', 'z', 'charm', 'bian', 'visit', 'sing', 'flag', 'try',
        'sow', 'only', 'cattle', 'kind', 'butter', 'better', 'cheap', 'hire', 'rake', 'accuse', 'out', 'per', 'landlord', 'district', 'lean', 'lip', 'will', 'over', 'prejudice', 'jiang', 'mere', 'place', 'today', 'else', 'spill', 'monkey', 'saddle', 'charge',
        'watch', 'headdress', 'snow', 'mie', 'cui', 'but', 'ma', 'water', 'suffer', 'die', 'complex', 'possible', 'double', 'have', 'school', 'flat', 'yan', 'fix', 'title', 'feed', 'office', 'freeze', 'house', 'like', 'bend', 'sh', 'no', 'fo', 'pump', 'tube',
        'seize', 'lia', 'where', 'tuan', 'current', 'pigeon', 'church', 'everywhere', 'chalk', 'madden', 'nu', 'horizontal', 'elect', 'peculiar', 'to', 'gentleman', 'spirit', 'enclose', 'reference', 'permit', 'rent', 'threat', 'opinion', 'xia', 'plant', 'gai',
        'grain', 'iron', 'classify', 'quart', 'level', 'pressure', 'honor', 'accord', 'many', 'various', 'ming', 'lump', 'meat', 'kan', 'learn', 'si', 'ahead', 'serious', 'direct', 'curious', 'complain', 'whiten', 'almost', 'basin', 'store', 'roar', 'recommend',
        'feel', 'provide', 'bottle', 'belong', 'hall', 'develop', 'ping', 'bone', 'history', 'control', 'cai', 'brass', 'suo', 'influential', 'qiong', 'mass', 'g', 'lazy', 'encourage', 'pen', 'mother', 'lai', 'forbid', 'zhun', 'apply', 'bush', 'kingdom', 'cliff',
        'veil', 'humor', 'profit', 'trade', 'country', 'determine', 'for', 'mei', 'shower', 'right', 'cou', 'between', 'resistance', 'gain', 'fast', 'newspaper', 'before', 'avoidance', 'invention', 'notebook', 'fruit', 'attend', 'marry', 'scent', 'bedroom', 'fei',
        'from', 'nose', 'look', 'ashamed', 'size', 'park', 'drop', 'season', 'na', 'cry', 'habit', 'convenience', 'master', 'explore', 'imitation', 'temper', 'more', 'invent', 'fu', 'compare', 'crash', 'mine', 'zhan', 'jewel', 'ge', 'tide', 'party', 'beast', 'fond',
        'widen', 'court', 'tribe', 'whip', 'rod', 'nest', 'into', 'weekday', 'example', 'swim', 'kao', 'strict', 'telegraph', 'along', 'feast', 'way', 'xiao', 'medicine', 'desire', 'loosen', 'behave', 'although', 'underneath', 'machinery', 'fashion', 'whom', 'youth',
        'sister', 'ca', 'clerk', 'ruo', 'hold', 'copy', 'anyway', 'ordinary', 'engineer', 'whole', 'soft', 'library', 'worry', 'send', 'sacrifice', 'expansion', 'secretary', 'plan', 'paste', 'advance', 'boast', 'f', 'la', 'just', 'river', 'may', 'wet', 'field', 'attract',
        'use', 'rank', 'friendship', 'lin', 'least', 'learning', 'wherever', 'soldier', 'bei', 'zh', 'wa', 'pay', 'stamp', 'same', 'i', 'razor', 'zhe', 'conquer', 'needle', 'xuan', 'flower', 'trunk', 'spade', 'pray', 'both', 'boundary', 'bite', 'applaud', 'take', 'work',
        'another', 'keng', 'humble', 'remind', 'insure', 'bad', 'words', 'bell', 'ray', 'ong', 'da', 'room', 'seed', 'ribbon', 'scarce', 'ci', 'liu', 'date', 'leaf', 'top', 'point', 'free', 'sailor', 'block', 'collection', 'punctual', 'press', 'receive', 'nen', 'describe',
        'priest', 'guess', 'sure', 'anyhow', 'cuan', 'popular', 'excellence', 'cang', 'ce', 'fan', 'complicate', 'shao', 'slope', 'huai', 'deserve', 'moreover', 'civilize', 'objection', 'prepare', 'restaurant', 'dine', 'bank', 'du', 'forgive', 'bit', 'equal', 'instant',
        'appearance', 'collector', 'in', 'cape', 'everyone', 'quantity', 'with', 'se', 'society', 'self', 'taste', 'relation', 'satisfy', 'photography', 'dog', 'jiu', 'who', 'procession', 'pei', 'curl', 'loyal', 'ugly', 'zhuang', 'educate', 'cost', 'most', 'scientific',
        'operation', 'break', 'cut', 'and', 'niao', 'people', 'shake', 'guai', 'preserve', 'paper', 'band', 'adoption', 'dark', 'leadership', 'reproduce', 'whoever', 'advertise', 'courage', 'evening', 'stiff', 'cloud', 'protection', 'limb', 'pupil', 'deer', 'blow', 'mixed',
        'lord', 'ideal', 'agree', 'jin', 'tune', 'account', 'among', 'power', 'fierce', 'he', 'forward', 'sheep', 'young', 'up', 'day', 'anxious', 'overflow', 'turn', 'xue', 'reflection', 'tend', 'fa', 'review', 'color', 'depend', 'heighten', 'limit', 'enemy', 'examine', 'darken',
        'flow', 'main', 'flavor', 's', 'pool', 'elephant', 'grip', 'foot', 'refer', 'confidential', 'suan', 'hotel', 'connect', 'substance', 'messenger', 'cultivation', 'hui', 'bin', 'kun', 'zhang', 'unity', 'tie', 'rao', 'son', 'dui', 'election', 'driving', 'problem', 'reward', 'note',
        'whatever', 'tui', 'ha', 'selfish', 'upright', 'liar', 'ear', 'rescue', 'lawyer', 'qie', 'strengthen', 'mixture', 'false', 'moonlight', 'word', 'base', 'tea', 'neither', 'stove', 'health', 'stomach', 'condition', 'doctor', 'extent', 'usual', 'hair', 'difficult', 'suit', 'delay',
        'important', 'clay', 'protect', 'straw', 'geng', 'picture', 'opposition', 'immense', 'offense', 'dull', 'd', 'lipstick', 'favorite', 'actual', 'move', 'jealous', 'cart', 'tu', 'listen', 'everybody', 'human', 'reasonable', 'ancient', 'fixed', 'fear', 'nouns', 'succeed', 'dozen',
        'lonely', 'you', 'mix', 'sweat', 'luan', 'roast', 'body', 'waste', 'sheng', 'chuo', 'chi', 'plate', 'tender', 'lou', 'meantime', 'advise', 'crack', 'frame', 'cultivator', 'fasten', 'electric', 'leather', 'worse', 'clear', 'separation', 'lodge', 'alone', 'reputation', 'chang',
        'whenever', 'occasion', 'dive', 'servant', 'they', 'below', 'criminal', 'wire', 'experience', 'nursery', 'peace', 'education', 'ze', 'news', 'defense', 'bold', 'interest', 'nuisance', 'finish', 'weed', 'upper', 'stream', 'uv', 'parcel', 'ruan', 'bao', 'suspect', 'settle', 'chen',
        'calm', 'taxi', 'bai', 'surprise', 'political', 'scrape', 'language', 've', 'companion', 'egg', 'poor', 'seem', 'wound', 'variety', 'modern', 'nie', 'wo', 'castle', 'coward', 'remark', 'wise', 'island', 'conquest', 'essential', 'distance', 'keep', 'suspicion', 'rug', 'qiang', 'eye',
        'en', 'unite', 'broadcast', 'bu', 'hao', 'propose', 'mill', 'shirt', 'sale', 'world', 'rain', 'after', 'thicken', 'side', 'boat', 'could', 'pure', 'necessary', 'plow', 'dear', 'tent', 'rou', 'husband', 'recent', 'h', 'chuang', 'uppermost', 'zuan', 'empty', 'wrap', 'because', 'far',
        'why', 'depth', 'tailor', 'please', 'reason', 'shen', 'di', 'idle', 'hurry', 'worship', 'pass', 'soil', 'purple', 'branch', 'apart', 'difference', 'politics', 'rub', 'suggestion', 'breakfast', 'agreement', 'calculator', 'coal', 'attention', 'offer', 'ice', 'able', 'anyone', 'seng',
        'tap', 'second', 'coarse', 'journey', 'disturb', 'ling', 'hot', 'science', 'wall', 'description', 'battle', 'companionship', 'failure', 'thirst', 'burn', 'tour', 'sadden', 'fen', 'rang', 'recognition', 'circular', 'build', '100signal', 'classification', 'pang', 'stand', 'weekend',
        'clock', 'rule', 'preference', 'proud', 'nation', 'father', 'apple', 'hand', 'bravery', 'bus', 'grass', 'bless', 'nor', 'cheng', 'trouble', 'translate', 'invite', 'employ', 'moderation', 'spin', 'might', 'this', 'forth', 'flour', 'backward', 'mao', 'loss', 'error', 'wipe', 'blind',
        'believe', 'western', 'soul', 'card', 'company', 'tall', 'cupboard', 'ke', 'knock', 'suggest', 'name', 'god', 'thing', 'view', 'lighten', 'mistake', 'horizon', 'dollar', 'rest', 'since', 'businesslike', 'add', 'harbor', 'outward', 'extensive', 'design', 'meanwhile', 'diu', 'scenery',
        'address', 'seldom', 'care', 'overcome', 'gradual', 'delight', 'gas', 'ash', 'discussion', 'homecoming', 'insult', 'yo', 'parallel', 'grave', 'gui', 'angry', 'dip', 'p', 'representative', 'telephone', 'find', 'canvas', 'reng', 'omission', 'pinch', 'manufacture', 'that', 'she', 'term',
        'pack', 'wish', 'su', 'not', 'harmony', 'exact', 'plain', 'cun', 'ought', 'lunch', 'curse', 'shall', 'attentive', 'fry', 'christmas', 'surface', 'crop', 'republic', 'float', 'exist', 'tun', 'wheel', 'zeng', 'unit', 'matter', 'pretense', 'creature', 'grace', 'donkey', 'operate', 'want',
        'weather', 'coffee', 'relative', 'shield', 'customer', 'feng', 'discovery', 'dry', 'nothing', 'prize', 'jiong', 'jian', 'composition', 'que', 'moderate', 'bargain', 'general', 'net', 'someone', 'verb', 'paint', 'wrong', 'theatrical', 'bie', 'valley', 'membership', 'obedience', 'bring',
        'comfort', 'quality', 'oar', 'production', 'drawer', 'smile', 'course', 'neat', 'nice', 'cap', 'empire', 'fence', 'familiar', 'cheer', 'tidy', 'dao', 'opportunity', 'anger', 'cen', 'confess', 'case', 'crown', 'easy', 'zu', 'balance', 'origin', 'nv', 'brave', 'appear', 'motherly', 'melt',
        'child', 'homemade', 'noon', 'damage', 'zhui', 'film', 'mu', 'produce', 'xi', 'flame', 'time', 'hurt', 'population', 'pronunciation', 'gate', 'ta', 'program', 'play', 'green', 'ache', 'beat', 'pot', 'rich', 'start', 'engine', 'blade', 'guard', 'cork', 'ask', 'member', 'bed', 'grease',
        'heavenly', 'wei', 'space', 'read', 'zhai', 'animal', 'town', 'jue', 'behavior', 'preach', 'expensive', 'handshake', 'narrow', 'reproduction', 'sun', 'sticky', 'purpose', 'feather', 'moment', 'zei', 'instead', 'patience', 'thunder', 'bird', 'stocking', 'awake', 'profession', 'tired',
        'frequent', 'fortunate', 'religion', 'ago', 'quite', 'essence', 'introduce', 'mat', 'excellent', 'return', 'latter', 'shuo', 'sound', 'need', 'zhua', 'enclosure', 'hu', 'hanging', 'bottom', 'camera', 'till', 'arrest', 'aunt', 'practical', 'foreign', 'boiling', 'cover', 'trip', 'these',
        'death', 'consider', 'rate', 'threaten', 'temperature', 'price', 'fur', 'welcome', 'marriage', 'inventor', 'cao', 'duan', 'dismiss', 'aloud', 'express', 'noise', 'reach', 'handwriting', 'therefore', 'offend', 'funeral', 'translation', 'write', 'educator', 'promise', 'common', 'appoint',
        'fly', 'there', 'gan', 'fancy', 'log', 'kuang', 'stain', 'ie', 'lady', 'cook', 'hard', 'waist', 'search', 'poem', 'jaw', 'contain', 'wreck', 'ji', 'sell', 'miao', 'perfection', 'nin', 'zhen', 'wan', 'automatic', 'brain', 'possess', 'happy', 'authority', 'rubbish', 'record', 'ai', 'ball',
        'bleed', 'flash', 'chimney', 'drum', 'ri', 'can', 'nature', 'end', 'fresh', 'thread', 'cream', 'sharpen', 'track', 'rob', 'admission', 'un', 'capital', 'orange', 'thumb', 'coin', 'flesh', 'safe', 'shi', 'sui', 'cat', 'light', 'present', 'stick', 'vowel', 'furnish', 'married', 'interference',
        'than', 'chan', 'prison', 'certainty', 'polite', 'anything', 'neighborhood', 'baby', 'ui', 'apparatus', 'pleasant', 'bath', 'ever', 'recognize', 'northern', 'rejoice', 'ran', 'nang', 'golden', 'know', 'minute', 'across', 'birth', 'dirt', 'sky', 'tax', 'yin', 'fault', 'hat', 'qualify', 'increase',
        'q', 'impossible', 'lao', 'if', 'zan', 'judge', 'mile', 'mechanic', 'photograph', 'sand', 'trial', 'we', 'sting', 'universe', 'grammar', 'afford', 'daily', 'heaven', 'fortune', 'qi', 'tan', 'police', 'shame', 'miss', 'sort', 'race', 'brother', 'position', 'straight', 'zhao', 'min', 'social', 'mouse',
        'study', 'credit', 'weight', 'pretend', 'fact', 'lot', 'zhi', 'tin', 'none', 'wonder', 'chairman', 'different', 'bent', 'edge', 'deceive', 'club', 'rice', 'disapprove', 'argument', 'nuo', 'call', 'librarian', 'tai', 'strip', 'fellow', 'beak', 'silk', 'bare', 'adjectives', 'yes', 'lv', 'somehow',
        'grateful', 'enough', 'delivery', 'absent', 'harvest', 'pound', 'sauce', 'cent', 'secret', 'fatten', 'tonight', 'situation', 'theater', 'request', 'eat', 'competition', 'mend', 'tool', 'universal', 'excuse', 'qun', 'blood', 'chai', 'royalty', 'business', 'safety', 'faint', 'ting', 'spare', 'pick',
        'private', 'funny', 'immediate', 'relief', 'xing', 'dead', 'y', 'roll', 'declare', 'property', 'model', 'war', 'adopt', 'cong', 'rabbit', 'somebody', 'efficient', 'sign', 'toy', 'greed', 'plenty', 'single', 'sudden', 'lay', 'small', 'defendant', 'linen', 'skill', 'slow', 'hunger', 'large', 'middle',
        'yet', 'yue', 'steep', 'measure', 'living', 'bi', 'then', 'diamond', 'governor', 'competitor', 'che', 'ripe', 'solve', 'autumn', 'extend', 'pad', 'murder', 'garage', 'new', 'fill', 'together', 'grey', 'sleep', 'gallon', 'report', 'furniture', 'machine', 'yield', 'collar', 'include', 'snake', 'puzzle',
        'suspicious', 'though', 'adventure', 'fou', 'open', 'ready', 'international', 'lengthen', 'tighten', 'sit', 'bake', 'enter', 'effort', 'excite', 'choice', 'president', 'clever', 'rather', 'pile', 'precious', 'arise', 'travel', 'big', 'beautiful', 'act', 'swell', 'those', 'prompt', 'old', 'wind', 'completion',
        'exchange', 'great', 'absence', 'vain', 'explosion', 'correction', 'waiter', 'next', 'acid', 'introduction', 'wu', 'adjustment', 'straighten', 'b', 'kill', 'university', 'bread', 'refresh', 'average', 'hill', 'character', 'demand', 'mud', 'firm', 'x', 'within', 'wave', 'brick', 'throw', 'descent', 'zhu', 'tray',
        'spread', 'claim', 'liberty', 'handkerchief', 'must', 'support', 'expert', 'soften', 'crime', 'abroad', 'shape', 'pastry', 'go', 'wen', 'screw', 'heat', 'certain', 'during', 'po', 'curtain', 'wax', 'loose', 'eastern', 'concern', 'somewhere', 'hesitate', 'expect', 'possessor', 'cotton', 'opposite', 'frequency',
        'class', 'ding', 'king', 'inquiry', 'ship', 'attack', 'distribution', 'manage', 'so', 'ease', 'ripen', 'le', 'winter', 'fiction', 'pocket', 'sour', 'rude', 'misery', 'temple', 'past', 'lian', 'width', 'row', 'other', 'be', 'what', 'bright', 'stupid', 'sweep', 'disappearance', 'answer', 'department', 'hello',
        'remain', 'post', 'once', 'reduce', 'airplane', 'formal', 'too', 'rotten', 'awkward', 'hour', 'visitor', 'poverty', 'qin', 'qu', 'mad', 'back', 'bury', 'knowledge', 'lovely', 'carriage', 'defeat', 'clothe', 'well', 'soon', 'help', 'debt', 'cheese', 'finger', 'discontent', 'accident', 'chua', 'royal', 'childhood',
        'xin', 'accept', 'afternoon', 'tight', 'anxiety', 'congratulate', 'idea', 'woolen', 'tail', 'loud', 'due', 'map', 'n', 'freedom', 'warmth', 'beside', 'bicycle', 'tempt', 'agency', 'combine', 'suppose', 'breadth', 'connection', 'female', 'xun', 'kui', 'talk', 'nowadays', 'wide', 'compose', 'healthy', 'correct',
        'strength', 'upon', 'steady', 'win', 'shoot', 'artificial', 'home', 'prevention', 'floor', 'k', 'qia', 'stair', 'hay', 'without', 'figure', 'sincere', 'hole', 'besides', 'pride', 'bind', 'however', 'glad', 'paw', 'shoe', 'audience', 'robbery', 'hut', 'sick', 'dan', 'drive', 'cave', 'leave', 'earn', 'sword', 'juice',
        'conscious', 'see', 'signature', 'such', 'sweeten', 'ru', 'goat', 'metal', 'cha', 'kitchen', 'redden', 'waiting', 'corn', 'normal', 'tower', 'board', 'slight', 'separate', 'proof', 'luo', 'ken', 'importance', 'seat', 'miserable', 'through', 'lock', 'surround', 'glory', 'wait', 'organize', 'secrecy', 'dream', 'motor',
        'experiment', 'teach', 'creep', 'foolish51.free', 'explosive', 'true', 'huo', 'particle', 'lung', 'deaf', 'slip', 'cheat', 'jiao', 'hou', 'cool', 'being', 'addition', 'parent', 'spit', 'difficulty', 'any', 'how', 'already', 'congratulation', 'sample', 'terrible', 'ton', 'numerous', 'dong', 'stop', 'commercial',
        'everyday', 'earnest', 'host', 'rong', 'victory', 'direction', 'first', 'would', 'moon', 'chemical', 'mark', 'pou', 'altogether', 'one', 'scatter', 'angle', 'excessive', 'dependent', 'bathe', 'subject', 'ya', 'minister', 'box', 'discover', 'disappear', 'conscience', 'friendly', 'pair', 'full', 'near', 'fair',
        'praise', 'feeble', 'bear', 'performance', 'qian', 'busy', 'yellow', 'pardon', 'mang', 'advertisement', 'salt', 'zang', 'men', 'stage', 'week', 'hook', 'merry', 'star', 'weng', 'blue', 'commerce', 'beyond', 'gay', 'violence', 'split', 'gather', 'pig', 'dance', 'detail', 'ocean', 'noun', 'duty', 'old-fashioned',
        'operator', 'highway', 'wander', 'yu', 'jia', 'huan', 'memory', 'fork', 'glass', 'hope', 'replace', 'mian', 'gang', 'tip', 'spoil', 'deng', 'indeed', 'exercise', 'weave', 'sea', 'stock', 'kou', 'deliver', 'letter', 'chuai', 'several', 'joke', 'diao', 'coat', 'ceremony', 'begin', 'ounce', 'despair', 'last', 'justice',
        'urgent', 'radio', 'union', 'han', 'shave', 'sympathy', 'bag', 'city', 'nei', 'ren', 'qualification', 'even', 'grind', 'often', 'piao', 'xu', 'hear', 'table', 'load', 'thick', 'sew', 'native', 'wrist', 'destruction', 'persuade', 'calculation', 'it', 'morning', 'saw', 'path', 'wife', 'inward', 'tire', 'ga', 'fellowship',
        'nut', 'air', 'success', 'train', 'c', 'intend', 'worm', 'carry', 'imaginary', 'also', 'revenge', 'repeat', 'tuo', 'drink', 'disappoint', 'hong', 'indoor', 'laughter', 'tell', 'harm', 'daughter', 'tobacco', 'proper', 'come', 'neck', 'probable', 'lu', 'violent', 'salary', 'degree', 'stone', 'chain', 'fate', 'slide', 'nve',
        'english', 'conversation', 'cure', 'modesty', 'literary', 'clean', 'handle', 'meal', 'share', 'sometime', 'multiply', 'notice', 'song', 'speed', 'let', 'broad', 'sha', 'tooth', 'whether', 'danger', 'sorrow', 'captain', 'square', 'dai', 'very', 'roof', 'ladder', 'homework', 'ring', 'do', 'confuse', 'stuff', 'head',
        'bar', 'whichever', 'businessman', 'put', 'window', 'barrel', 'decay', 'wing', 'sight', 'north', 'annoy', 'accustom', 'drag', 'story', 'change', 'hate', 'trap', 'against', 'e', 'affair', 'blame', 'lei', 'haste', 'system', 'j', 'oppose', 'shui', 'towel', 'wear', 'colony', 'short', 'event', 'polish', 'follow', 'shuan',
        'honesty', 'bill', 'height', 'sir', 'aim', 'hunt', 'extension', 'fade', 'neng', 'distinguish', 'xiang', 'beam', 'likely', 'manner', 'rush', 'admire', 'factory', 'bing', 'o', 'summer', 'receipt', 'product', 'age', 'allowance', 'duo', 'zuo', 'sense', 'late', 'beauty', 'storm', 'chui', 'arrow', 'interfere', 'become',
        'get', 'joy', 'cross', 'jun', 'destructive', 'fame', 'amount', 'confusion', 'mind', 'yong', 'ruin', 'pai', 'prove', 'quick', 'strap', 'kuai', 'particular', 'attempt', 'food', 'favor', 'silence', 'hasten', 'hen', 'sharp', 'sweet', 'left', 'steer', 'heng', 'lessen', 'li', 'pour', 'plural', 'rail', 'persuasion', 'bean',
        'serve', 'medical', 'building', 'lately', 'dig', 'guan', 'approve', 'line', 'den', 'inclusive', 'mai', 'owe', 'stir', 'valuable', 'command', 'sang', 'meeting', 'er', 'understand', 'smoke', 'grammatical', 'prefer', 'beneath', 'v', 'thorn', 'tong', 'widower', 'nephew', 'gold', 'jie', 'dang', 'sometimes', 'coast', 'law',
        'rough', 'sympathetic', 'face', 'improve', 'close', 'cousin', 'yard', 'fail', 'garden', 'mercy', 'dirty', 'sao', 'caution', 'scientist', 'retire', 'string', 'loaf', 'early', 'presence', 'above', 'fire', 'govern', 'zai', 'feeling', 'luck', 'shop', 'student', 'influence', 'pencil', 'heap', 'much', 'cold', 'liang', 'desk',
        'deafen', 'lend', 'art', 'sacred', 'envy', 'throat', 'hesitation', 'deed', 'pretty', 'fall', 'attractive', 'zhuo', 'san', 'regret', 'scorn', 'page', 'staff', 'strong', 'joint', 'employee', 'soup', 'explain', 'flood', 'qiu', 'fine', 'fat', 'natural', 'smooth', 'weapon', 'explode', 'perform', 'book', 'annoyance',
        'wang', 'passenger', 'bribe', 'step', 'complaint', 'farm', 'electrician', 'whose', 'imaginative', 'entertain', 'chun', 'unless', 'weigh', 'gao', 'berry', 'official', 'bridge', 'stroke', 'lang', 'teng', 'everything', 'chong', 'mou', 'give', 'physical', 'always', 'earth', 'penny', 'thief', 'nao', 'saucer', 'hardly', 'yao',
        'ticket', 'arm', 'liao', 'literature', 'brown', 'red', 'knife', 'leng', 'zong', 'shout', 'warn', 'sen', 'len', 'beg', 'warm', 'belief', 'beng', 'qiao', 'allow', 'march', 'xiu', 'envelope', 'dictionary', 'ning', 'postpone', 'zhuai', 'chair', 'male', 'decide', 'supply', 'again', 'escape', 'shine', 'tough', 'juan', 'holiday',
        'extreme', 'nong', 'progress', 'application', 'sou', 'pin', 'mean', 'axe', 'industry', 'front', 'officer', 'by', 'twist', 'print', 'all', 'ne', 'jelly', 'mankind', 'rust', 'committee', 'shu', 'piece', 'jump', 'customary', 'gift', 'fool', 'guilt', 'obedient', 'responsible', 'momentary', 'weak', 'hinder', 'suck', 'milk',
        'council', 'rapid', 'kiss', 'tao', 'extra', 'tiao', 'pao', 'rope', 'an', 'ground', 'collect', 'fang', 'ill', 'bound', 'translator', 'insurance', 'attraction', 'disgust', 'shell', 'ch', 'enjoy', 'grand', 'ban', 'desert', 'breath', 'growth', 'group', 'rot', 'spite', 'yang', 'flight', 'gentle', 'alike', 'dun', 'raise', 'damp',
        'car', 'forest', 'quiet', 'cloth', 'merchant', 'outline', 'otherwise', 'niu', 'l', 'person', 'manager', 'weaken', 'wooden', 'show', 'satisfaction', 'insect', 'order', 'the', 'sad', 'spell', 'fever', 'dish', 'complication', 'should', 'punish', 'loan', 'strange', 'bang', 'article', 'pet', 'quarter', 'shore', 'simplicity',
        'tang', 'remember', 'plaster1714arch', 'rid', 'spot', 'meng', 'entire', 'intention', 'amongst', 'force', 'amusement', 'yun', 'quan', 'night', 'existence', 'tree', 'cowardice', 'tou', 'silver', 'which', 'mail', 'interrupt', 'lan', 'high', 'musician', 'shadow', 'deceit', 'on', 'brighten', 'hospital', 'woman', 'pint', 'knot',
        'dot', 'kick', 'tame', 'question', 'value', 'permanent', 'impulse', 'dinner', 'comb', 'relieve', 'village', 'gua', 'necessity', 'mechanism', 'hindrance', 'avoid', 'r', 'discuss', 'perfect', 'decision', 'friend', 'thus', 'stem', 'raw', 'elsewhere', 'development', 'gou', 'pale', 'wine', 'buy', 'm', 'repair', 'proposal',
        'forget', 'lose', 'mild', 'lamp', 'leg', 'pian', 'devil', 'little', 'hammer', 'relate', 'draw', 'neglect', 'efficiency', 'nan', 'zero', 'heart', 'catch', 'kang', 'dare', 'shut', 'lesson', 'satisfactory', 'result', 'sink', 'gei', 'burst', 'heal', 'rubber', 'generous', 'curve', 'cautious', 'compete', 'wheat', 'zui', 'meet',
        'dress', 'inform', 'shuai', 'canal', 'list', 'gap', 'market', 'ben', 'director', 'remedy', 'pie', 'sa', 'organ', 'rise', '7back', 'climb', 'number', 'inquire', 'sentence', 'shelf', 'around', 'dei', 'mouth', 'year', 'at', 'amuse', 'guang', 'pause', 'partner', 'xian', 'resign', 'rare', 'yi', 'breathe', 'mineral', 'imagine',
        'motion', 'avenue', 'ku', 'soap', 'zheng', 'copper', 'decrease', 'basis', 'active', 'regular', 'miu', 'faith', 'distant', 'anybody', 'stretch', 'life', 'dian', 'cruel', 'think', 'thorough', 'part', 'ability', 'elastic', 'grow', 'crush', 'door', 'duck', 'steam', 'outside', 'association', 'save', 'hatred', 'speech', 'standard',
        'set', 'flatten', 'asleep', 'pull', 'fit', 'sock', 'cuo', 'guest', 'zi', 'sail', 'swear', 'zha', 'man', 'risk', 'pronounce', 'bow', 'elder', 'biao', 'delicate', 'bowl', 'nobody', 'own', 'de', 'cup', 'wild', 'silent', 'conqueror', 'still', 'decisive', 'tongue', 'zen', 'shai', 'virtue', 'motherhood', 'comparison', 'mo', 'eng',
        'west', 'sore', 'realize', 'reduction', 'voice', 'a', 'refuse', 'wool', 'apology', 'make', 'state', 'chest', 'borrow', 'shallow', 'nun', 'salesman', 'agriculture', 'shun', 'shelter', 'actress', 'army', 'absolute', 'basic', 'few', 'confession', 'hang', 'noble', 'ceng', 'reply', 'behind', 'disagree', 'custom', 'greet', 'choose',
        'horse', 'divide', 'pattern', 'laugh', 'wicked', 'chief', 'dependence', 'join', 'eager', 'chou', 'poet', 'oil', 'pain', 'cottage', 'exception', 'ba', 'hai', 'effective', 'patriotic', 'powder', 'witness', 'umbrella', 'huang', 'fish', 'scene', 'voyage', 'belt', 'smell', 'sheet', 'doubt', 're', 'w', 'permission', 'chu', 'gu',
        'lake', 'cake', 'slave', 'of', 'especially', 'daylight', 'slavery', 'kua', 'gray', 'upset', 'fight', 'local', 'sport', 'interruption', 'zun', 'wealth', 'sai', 'uncle', 'touch', 'bo', 't', 'arrive', 'solution', 'screen', 'nurse', 'tremble', 'or', 'kai', 'further', 'check', 'calculate', 'severe', 'onto', 'obe',
    ];

    /** 二级域名 字符串 */
    const str36 = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p',
        'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z',
        'x', 'c', 'v', 'b', 'n', 'm'
    ];

    /**
     * 取得根域名
     *
     * @param string $host 域名
     * @return string 返回根域名
     */
    public static function domain(string $host): string
    {
        $array_domain = explode('.', explode(':', $host)[0]);
        $array_num    = count($array_domain);
        if ($array_num <= 2) {
            return $host;
        } else {
            foreach (self::pix_sub as $v) {
                if ($v == $array_domain[$array_num - 2] . '.' . $array_domain[$array_num - 1]) {
                    return $array_domain[$array_num - 3] . '.' . $v;
                }
            }
            foreach (self::pix as $v) {
                if ($v == $array_domain[$array_num - 1]) {
                    return $array_domain[$array_num - 2] . '.' . $v;
                }
            }
        }
        return $host;
    }

    /**
     * 端口
     *
     * @param string $host
     * @return string
     */
    public static function port(string $host): string
    {
        $array_port = explode(':', $host);
        $array_num  = count($array_port);
        if ($array_num < 2) {
            return '';
        }
        return $array_port[1];
    }
}
