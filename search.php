<?php
require_once('config.php');
require_once('TwistOAuth/build/TwistOAuth.phar');

// キーワードによるツイート検索
$connection = new TwistOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
$params = array(
	'q' => '街コン AND 参加　AND -http AND -https AND -RT AND -拡散',
	'count' => '100',
	'lang' => 'ja'
);
$request_num = 10;
$export_file = fopen('data.csv', 'w');
for ($i = 0; $i < $request_num; $i++) {
	$tweets_obj = $connection->get('search/tweets', $params);
	if($tweets_obj){
		$tweets = $tweets_obj->statuses;
		// 取得結果をCSVに出力
		if($tweets){
			foreach ($tweets as $tweet) {
				// アカウント, 名前, ツイート
				$target_tweets = array($tweet->user->screen_name, $tweet->user->name, $tweet->text);
				fputcsv($export_file, $target_tweets);	
			}
		}
		// 次の結果を取得
		$next_results = preg_replace('/^\?/', '', $tweets_obj->search_metadata->next_results);
		if(!$next_results) break;
		parse_str($next_results, $params);
	}
}
fclose($export_file);
?>