# API-MeCab

TwitterAPIとMeCabを使ってTF-IDFでツイートを分析するためのライブラリ 
 
### 事前準備
* [MeCab](https://www.mlab.im.dendai.ac.jp/~yamada/ir/MorphologicalAnalyzer/MeCab.html "MeCab") ・・・日本語形態素解析エンジン 
* [php-mecab](https://github.com/rsky/php-mecab "php-mecab")・・・php用のMeCabモジュール
* [TwistOAuth](https://github.com/mpyw/TwistOAuth "TwistOAuth")・・・TwitterAPIで操作するライブラリ

### 設定ファイルの書き方 
事前に取得したTwitterのAPIキーなどをconfig.php内に以下のように記述してください。 

    $consumer_key = 'Consumer Key (API Key)';
    $consumer_secret = 'Consumer Secret (API Secret)';
    $access_token = 'Access Token';
    $access_token_secret = 'Access Token Secret';

### 検索条件の設定方法 
search.php内の以下の箇所で検索条件を設定してください。 
※取得ツイート数はデフォルトで5000件です。 

    $params = array(
	    'q' => '検索キーワード',
	    'count' => '取得したいツイート件数',
	    'lang' => '言語'
    );
 
### 自動生成されるCSVファイル 
実行すると以下のCSVが自動生成されます。 
* data.csv・・・TwitterAPIにより取得したデータを格納。 
* tfidf_avg.csv・・・各単語の平均TF-IDF値を格納。  
* tfidf_list.csv・・・各ツイートのTF-IDFの合計値を格納。data.csvと対応。 

### 集計対象 
* 一部の記号（、。,.…）、アカウント名（例：@test）を除く単語 
* 英数字だけ、1文字だけの単語は集計対象ですが、出力されません 
