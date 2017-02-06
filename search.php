<?php
// キーワードによるツイート検索
require_once('config.php');
require_once('TwistOAuth/build/TwistOAuth.phar');

$connection = new TwistOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

$params = array(
	'q' => '街コン AND -http AND -https AND -RT AND -拡散',
	'count' => '100',
	'lang' => 'ja'
);
$request_num = 10;
$file = fopen('data.csv', 'w');
for ($i = 0; $i < $request_num; $i++) {
	$tweets_obj = $connection->get('search/tweets', $params);
	if($tweets_obj){
		$tweets = $tweets_obj->statuses;
		// 取得結果をCSVに出力
		if($tweets){
			foreach ($tweets as $tweet) {
				// アカウント, 名前, ツイート
				$target_tweets = array($tweet->user->screen_name, $tweet->user->name, $tweet->text);
				fputcsv($file, $target_tweets);	
			}
		}
		// 次の結果を取得
		$next_results = preg_replace('/^\?/', '', $tweets_obj->search_metadata->next_results);
		if(!$next_results) break;
		parse_str($next_results, $params);
	}
}
fclose($file);

$file = new SplFileObject('data.csv');
$file->setFlags(SplFileObject::READ_CSV);
if($file){
	$i = 0;

	$tf_list = array();
	$line_cnt = array();
	foreach ($file as $index => $line) {
		list($account, $user, $tweet) = $line;
		$words = \MeCab\split($tweet);
		// TF計算
		$word_cnt = array();
		foreach ($words as $word) {
			if(array_key_exists($word, $word_cnt)){
				$word_cnt[$word] += 1;
			}else{
				$word_cnt += array($word => 1);
			}
		}
		if($word_cnt){
			$tf = array();
			foreach ($word_cnt as $word => $cnt) {
				// 1行あたりの出現回数 / 1行あたりの単語数
				$tf += array($word => $cnt / count($words));
			}
		}
		$tf_list += array($index => $tf);

		// 単語が出現した行数
		$words = array_unique($words);
		foreach ($words as $key => $word) {
			if(!empty($word)){
				if(array_key_exists($word, $line_cnt)){
					$line_cnt[$word] += 1;
				}else{
					$line_cnt += array($word => 1);
				}
			}
		}
		$i++;
	}

	// IDF計算
	$idf = array();
	if($line_cnt){
		foreach ($line_cnt as $word => $cnt) {
			// log(総行数 / 単語が出現した行数) + 1
			$idf += array($word => log($i / $cnt) + 1);
		}
	}

	// TF・IDF計算
	$tfidf_list = array();
	$file = fopen('tfidf_list.csv', 'w');
	foreach ($tf_list as $index => $tf) {
		$tfidf_val = 0;
		foreach ($tf as $key => $value) {
			// TF * IDF
			$tfidf = $value * $idf[$key];
			if(array_key_exists($key, $tfidf_list)){
				$tfidf_list[$key] += $tfidf;
			}else{
				$tfidf_list += array($key => $tfidf);
			}
			// TF・IDF値を行ごとに求める
			$tfidf_val += $tfidf;
		}
		$tfidf_line = array($index + 1, round($tfidf_val, 4));
		fputcsv($file, $tfidf_line);
	}
	fclose($file);

	// 各単語のTF・IDF平均値
	$file = fopen('tfidf_avg.csv', 'w');
	foreach ($tfidf_list as $word => $value) {
		// 半角英数字、１文字だけのワードを排除
		if(!ctype_alnum(mb_convert_kana($word, 'a', 'UTF-8')) && mb_strlen($word) > 1){
			$tfidf_avg = array($word, round($value / $line_cnt[$word], 4));
			fputcsv($file, $tfidf_avg);
		}
	}
	fclose($file);
}
?>