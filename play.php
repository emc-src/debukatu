<?php


require('function.php');


if (empty($_SESSION['startFlg']) && empty($_SESSION['player'])) :
  debug('パラメータ不正操作あり。トップページへ遷移します。');
  header("Location:index.php");
  exit();
endif;

// $_SESSION = array();


$tpgCategory = '';        // トッピングのカテゴリ
$matchingFlg = false;     // トッピングとの相性フラグ
$possibleOder = '';       // オーダー可否フラグ
$possibleGym = '';         // トレーニング可否フラグ
$foods = array();
$toppings = array();

// プレイヤークラス
class Player
{
  protected $money;
  protected $kcal;
  protected $maxKcal;

  // コンストラクタ
  public function __construct($money, $kcal, $maxKcal)
  {
    $this->money = $money;
    $this->kcal = $kcal;
    $this->maxKcal = $maxKcal;
  }
  // セッター
  public function setMoney($num)
  {
    $this->money = (int) filter_var($num, FILTER_VALIDATE_FLOAT);
  }
  public function setKcal($num)
  {
    $this->kcal = (int) filter_var($num, FILTER_VALIDATE_FLOAT);
  }
  // ゲッター
  public function getMoney()
  {
    if ($this->money <= 0) :
      return 0;
    else :
      return $this->money;
    endif;
  }
  public function getKcal()
  {
    return $this->kcal;
  }
  public function getMaxKcal()
  {
    return $this->maxKcal;
  }
}


// 抽象クラス - - メニュークラス：親フードの親
abstract class Menu
{
  protected $name;
  protected $price;
  protected $kcal;
  protected $category;


  // セッター
  public function setName($str)
  {
    $this->name = $str;
  }

  public function setPrice($num)
  {
    $this->price = (int) filter_var($num, FILTER_VALIDATE_FLOAT);
  }

  public function setKcal($num)
  {
    $this->kcal = (int) filter_var($num, FILTER_VALIDATE_FLOAT);
  }

  // ゲッター
  public function getName()
  {
    return $this->name;
  }

  public function getPrice()
  {
    return $this->price;
  }

  public function getKcal()
  {
    return $this->kcal;
  }

  public function getCategory()
  {
    return $this->category;
  }
}

// 継承 - - 各フードクラスの親クラス
class Foods extends Menu
{
  protected $img;
  public function __construct($name, $price, $kcal, $category, $img)
  {
    $this->name = $name;
    $this->price = $price;
    $this->kcal = $kcal;
    $this->category = $category;
    $this->img = $img;
  }
  // ゲッター
  public function getImg()
  {
    return $this->img;
  }

  // 共通メソッド - - 注文して食べる
  public function order($matchingFlg, $foodObj)
  {
    $upValue = '';
    $downValue = '';
    $orderFlg = true;

    // 所持金確認
    if ($_SESSION['player']->getMoney() < $this->getPrice()) :
      History::set('所持金不足のため注文できません。');
      $orderFlg = false;
    endif;
    // 摂取カロリー確認
    if ($_SESSION['player']->getKcal() >= $_SESSION['player']->getMaxKcal()) :
      History::set('摂取カロリーオーバーのため注文できません。');
      History::set('ジムに行ってカロリーを消費しよう！');
      $orderFlg = false;
    endif;
    // オーダー可否のフラグに結果を代入
    global $possibleOder;
    $possibleOder = ($orderFlg) ? true : false;
    // オーダー可の場合はトッピング相性判定および食べる
    if ($orderFlg) :
      switch (true):
          // 相性：good
        case $matchingFlg == 1:
          $upValue = mt_rand(10, 20);
          History::set('「トッピングとの相性バツグン♡♡」');
          History::set('ウマすぎて食べすぎた');
          History::set('摂取カロリー、支払額ともに' . $upValue . '%UP！');
          $upKcal = $foodObj->getKcal() + (int) (($foodObj->getKcal() * $upValue) / 100);
          $upPrice = $foodObj->getPrice() + (int) (($foodObj->getPrice() * $upValue) / 100);
          break;
          // 相性：normal
        case $matchingFlg == 2:
          $upValue = 0;
          History::set('「まぁ、、普通？ナシではないかな？」');
          History::set('トッピングとの相性は普通。');
          $upKcal = $foodObj->getKcal() + $_SESSION['topping']->getKcal();
          $upPrice = $foodObj->getPrice() + $_SESSION['topping']->getPrice();
          break;
          // 相性：bad
        case $matchingFlg == 3:
          $upValue = 5;
          $downValue = 20;
          History::set('「まずっっっ！！食えたもんじゃねェ！！！」');
          History::set('トッピングとの相性最悪！');
          History::set('食べ残したペナルティで支払額' . $downValue . '%加算！');
          $upKcal = (int) (($foodObj->getKcal() * $upValue) / 100);
          $upPrice = $foodObj->getPrice() + (int) (($foodObj->getPrice() * $downValue) / 100);
          break;
          // トッピングなし
        case $matchingFlg == 0:
          $upValue = 0;
          $upKcal = $foodObj->getKcal();
          $upPrice = $foodObj->getPrice();
          // トッピングとの相性メッセージなし
          break;
      endswitch;
      $_SESSION['player']->setKcal($_SESSION['player']->getKcal() + $upKcal);
      $_SESSION['player']->setMoney($_SESSION['player']->getMoney() - $upPrice);
      History::set(number_format($upKcal) . 'kcal のカロリー摂取！');
      History::set(number_format($upPrice) . '円 のお支払い！');
      $_SESSION['eatCount'] += 1;
    // History::set('次のメニューを選択してください。');
    endif;
  }
}

// 継承 - - フードクラス：洋食クラス
class FoodWestern extends Foods
{
  public function __construct($name, $price, $kcal, $category, $img)
  {
    parent::__construct($name, $price, $kcal, $category, $img);
  }

  // トッピング相性ジャッジ
  public function tpgJudge($tpgCategory)
  {
    switch (true) {
      case $tpgCategory == 0:
        $this->order(0, $this);
        break;
      case $tpgCategory == 1:
        $this->order(1, $this);
        break;
      case $tpgCategory == 2:
        $this->order(3, $this);
        break;
      case $tpgCategory == 3:
        $this->order(2, $this);
        break;
      case $tpgCategory == 4:
        $this->order(3, $this);
        break;
    }
  }
}

// 継承 - - フードクラス：丼クラス
class FoodDonburi extends Foods
{
  public function __construct($name, $price, $kcal, $category, $img)
  {
    parent::__construct($name, $price, $kcal, $category, $img);
  }

  // トッピング相性ジャッジ
  public function tpgJudge($tpgCategory)
  {
    switch (true) {
      case $tpgCategory == 0:
        $this->order(0, $this);
        break;
      case $tpgCategory == 1:
        $this->order(1, $this);
        break;
      case $tpgCategory == 2:
        $this->order(1, $this);
        break;
      case $tpgCategory == 3:
        $this->order(1, $this);
        break;
      case $tpgCategory == 4:
        $this->order(3, $this);
        break;
    }
  }
}

// 継承 - - フードクラス：和食クラス
class FoodJapanese extends Foods
{
  public function __construct($name, $price, $kcal, $category, $img)
  {
    parent::__construct($name, $price, $kcal, $category, $img);
  }
  // トッピング相性ジャッジ
  public function tpgJudge($tpgCategory, $foodName)
  {
    switch (true) {
      case $tpgCategory == 0:
        $this->order(0, $this);
        break;
      case $tpgCategory == 1:
        $this->order(2, $this);
        break;
      case $tpgCategory == 2:
        if ($foodName === '寿司') :
          $this->order(2, $this);
        else :
          $this->order(1, $this);
        endif;
        break;
      case $tpgCategory == 3:
        if ($foodName === '寿司') :
          $this->order(2, $this);
        else :
          $this->order(1, $this);
        endif;
        break;
      case $tpgCategory == 4:
        $this->order(3, $this);
        break;
    }
  }
}

// 継承 - - フードクラス：その他クラス
class FoodEtc extends Foods
{
  public function __construct($name, $price, $kcal, $category, $img)
  {
    parent::__construct($name, $price, $kcal, $category, $img);
  }

  // トッピング相性ジャッジ
  public function tpgJudge($tpgCategory)
  {
    switch (true) {
      case $tpgCategory == 0:
        $this->order(0, $this);
        break;
      case $tpgCategory == 1:
        $this->order(1, $this);
        break;
      case $tpgCategory == 2:
        $this->order(1, $this);
        break;
      case $tpgCategory == 3:
        $this->order(2, $this);
        break;
      case $tpgCategory == 4:
        $this->order(3, $this);
        break;
    }
  }
}

// 継承 - - フードクラス：スイーツクラス
class FoodSweets extends Foods
{
  public function __construct($name, $price, $kcal, $category, $img)
  {
    parent::__construct($name, $price, $kcal, $category, $img);
  }

  // トッピング相性ジャッジ
  public function tpgJudge($tpgCategory)
  {
    switch (true) {
      case $tpgCategory == 0:
        $this->order(0, $this);
        break;
      case $tpgCategory == 1:
        $this->order(3, $this);
        break;
      case $tpgCategory == 2:
        $this->order(3, $this);
        break;
      case $tpgCategory == 3:
        $this->order(3, $this);
        break;
      case $tpgCategory == 4:
        $this->order(1, $this);
        break;
    }
  }
}

// 継承 - - トッピングクラス
class Topping extends Menu
{
  // コンストラクタ
  public function __construct($name, $price, $kcal, $category)
  {
    $this->name = $name;
    $this->price = $price;
    $this->kcal = $kcal;
    $this->category = $category;
  }

  public function addTopping()
  {
    History::set('ロシアントッピング追加！');
    History::set('何が追加されたかは食べるまでのお楽しみ♡');
  }
  public function jsTpgMsg()
  {
    return 'トッピング追加！<br />' . $this->name;
  }
}

// ジムクラス
class Gym
{
  protected $price;
  protected $img;

  // コンストラクタ
  public function __construct($price, $img)
  {
    $this->price = $price;
    $this->img = $img;
  }

  // セッター
  public function setPrice($num)
  {
    $this->price = (int) filter_var($num, FILTER_VALIDATE_FLOAT);
  }

  // ゲッター
  public function getPrice()
  {
    return $this->price;
  }
  public function getImg()
  {
    return $this->img;
  }
  // メソッド
  public function training($playerObj)
  {
    $trainingFlg = true;
    if ($playerObj->getMoney() < $this->getPrice()) :
      History::set('所持金不足のためトレーニングできません。');
      $trainingFlg = false;
    endif;
    if ($playerObj->getKcal() == 0) :
      History::set('ジムで消費するカロリーがありません。');
      $trainingFlg = false;
    endif;

    global $possibleGym;
    $possibleGym = ($trainingFlg) ? true : false;
    if ($trainingFlg) :
      // ５分の１の確率でサービスデー
      if (!mt_rand(0, 4)) :
        $trainingPrice = $this->getPrice() - (int) $this->getPrice() * 0.15;
        History::set('☆ 本日サービスデー！利用料金15%OFF ☆');
      else :
        $trainingPrice = $this->getPrice();
      endif;
      $trainingKcal = mt_rand(500, 1200);

      $afterKcal = (($playerObj->getKcal() - $trainingKcal) <= 0) ? 0 : 1;
      $afterMoney = (($playerObj->getMoney() - $trainingPrice) <= 0) ? 0 : 1;
      // トレーニング後のカロリーが０以下の場合は摂取カロリーを０にする
      if ($afterKcal <= 0) :
        $playerObj->setKcal(0);
      else :
        $playerObj->setKcal($playerObj->getKcal() - $trainingKcal);
      endif;
      // トレーニング後の所持金が０以下の場合は所持金を０にする
      if ($afterMoney <= 0) :
        $playerObj->setMoney(0);
      else :
        $playerObj->setMoney($playerObj->getMoney() - $trainingPrice);
      endif;
      $playerObj->setMoney($playerObj->getMoney() - $trainingPrice);
      History::set(number_format($trainingPrice) . '円 の支払い');
      History::set(number_format($trainingKcal) . 'kcal を消費した！');
    endif;
    return ($trainingFlg) ? 'トレーニング！！' : '';
  }
}



// インターフェイス
interface HistoryInterface
{
  public static function set($str);
  public static function clear();
  public static function clearOne();
}

// インターフェイス - - クラス
class History implements HistoryInterface
{
  public static function set($str)
  {
    if (empty($_SESSION['history'])) :
      $_SESSION['history'] = '';
    endif;
    if (empty($_SESSION['historyOne'])) :
      $_SESSION['historyOne'] = '';
    endif;
    $_SESSION['history'] .= $str . '<br />';
    $_SESSION['historyOne'] .= $str . '<br />';
  }
  public static function clear()
  {
    $_SESSION['history'] = '';
  }
  public static function clearOne()
  {
    $_SESSION['historyOne'] = '';
  }
}

// インスタンス生成
$foods[] = new FoodWestern('デミハンバーグセット', 1450, 1300, 1, 'images/hamburg.jpg');
$foods[] = new FoodWestern('オムライス', 880, 840, 1, 'images/omeletterice.jpg');
$foods[] = new FoodDonburi('ステーキ丼', 1200, 850, 2, 'images/stakedon.jpg');
$foods[] = new FoodDonburi('牛丼', 490, 780, 2, 'images/gyudon.jpg');
$foods[] = new FoodJapanese('寿司', 1350, 600, 3, 'images/sushi.jpg');
$foods[] = new FoodJapanese('ざるそば', 790, 290, 3, 'images/soba.jpg');
$foods[] = new FoodEtc('ホルモン焼き', 780, 850, 4, 'images/horumon.jpg');
$foods[] = new FoodEtc('たこ焼き', 550, 680, 4, 'images/takoyaki.jpg');
$foods[] = new FoodEtc('餃子', 400, 360, 4, 'images/gyoza.jpg');
$foods[] = new FoodSweets('パンケーキ', 1280, 890, 5, 'images/pancake.jpg');
$foods[] = new FoodSweets('デニッシュ', 680, 950, 5, 'images/danish.jpg');
$foods[] = new FoodSweets('いちごパフェ', 780, 470, 5, 'images/parfait.jpg');

$toppings[] = new Topping('チーズ', 200, 130, 1);
$toppings[] = new Topping('マヨネーズ', 120, 180, 1);
$toppings[] = new Topping('おろしポン酢', 180, 28, 2);
$toppings[] = new Topping('温泉たまご', 130, 95, 3);
$toppings[] = new Topping('キャラメルソース', 60, 68, 4);
$toppings[] = new Topping('黒みつきな粉', 120, 98, 4);

$player = new Player(20000, 0, 5000);
$gym = new Gym(1800, 'images/training.jpg');

$_SESSION['maxKcal'] = $player->getKcal();


function init()
{
  History::clear();
  History::clearOne();
  gameOver();
  global $player;
  global $startFlg;
  $_SESSION['player'] = $player;
  $_SESSION['maxMoney'] = $player->getMoney();
  $_SESSION['eatCount'] = 0;
  $_SESSION['startFlg'] = '';
  $startFlg = true;
}

function initOne()
{
  History::clearOne();
}

function gameOver()
{
  $_SESSION = array();
}

// スタートフラグがある場合はプレイヤーセッションに初期値を代入
if (!empty($_SESSION['startFlg'])) :
  init();
endif;

// $showImg = $gym->getImg();
$showImg = 'images/commonBack.png';


// POST送信した場合
if (!empty($_POST)) :
  if (!empty($_POST['gameEnd'])) :
    debug('ゲーム終了。トップページへ遷移します。');
    gameOver();
    header("Location:index.php");
    exit();
  endif;

  // スタートフラグリセット
  $startFlg = false;

  // リセットボタンの場合
  if (!empty($_POST['reset'])) :
    init();
  endif;

  // 選択するボタンの場合
  if (!empty($_POST['select'])) :
    initOne();
    $selectFlg = true;
    $_SESSION['food'] = $foods[mt_rand(0, count($foods) - 1)];
    $_SESSION['topping'] = '';
  else :
    $selectFlg = false;
  endif;

  // トッピング追加ボタンの場合
  if (!empty($_POST['topping']) && !empty($_SESSION['food'])) :
    initOne();
    $toppingFlg = true;
    $_SESSION['topping'] = $toppings[mt_rand(0, count($toppings) - 1)];
    $_SESSION['topping']->addTopping();
  else :
    $toppingFlg = false;
  endif;

  // 注文して食べるボタンの場合
  if (!empty($_POST['order']) && !empty($_SESSION['food'])) :
    initOne();
    $orderFlg = true;
    $jsMsg = ($_SESSION['topping']) ? $_SESSION['topping']->jsTpgMsg() : '';
    debug('$jsMSGの中身：' . print_r($jsMsg, true));

    $toppingCgr = (!empty($_SESSION['topping'])) ? $_SESSION['topping']->getCategory() : 0;
    if ($_SESSION['food']->getCategory() == 3) :
      $_SESSION['food']->tpgJudge($toppingCgr, $_SESSION['food']->getName());
    else :
      $_SESSION['food']->tpgJudge($toppingCgr);
    endif;
  else :
    $orderFlg = false;
    // ジムへ行くボタンのあとに注文した場合
    if (empty($orderFlg) && empty($_SESSION['topping'])) :
      initOne();
    endif;
  endif;

  // ジムへ行くボタンの場合
  if (!empty($_POST['gym'])) :
    initOne();
    $gymFlg = true;
    $_SESSION['gym'] = $gym;
    $gymMsg = $_SESSION['gym']->training($_SESSION['player']);
    $showImg = $_SESSION['gym']->getImg();
    $jsMsg = ($gymMsg) ? $gymMsg : '';
    $_SESSION['food'] = array();
    $_SESSION['topping'] = array();
  else :
    $gymFlg = false;
  endif;
  if (!empty($_SESSION['history']) && !empty($orderFlg)) :
    $_SESSION['history'] .= '<br />';
  endif;
endif;


$foodName = (!empty($_SESSION['food'])) ? $_SESSION['food']->getName() : '';
$foodPrice = (!empty($_SESSION['food'])) ? $_SESSION['food']->getPrice() : '';
$foodKcal = (!empty($_SESSION['food'])) ? $_SESSION['food']->getKcal() : '';
$foodImg = (!empty($_SESSION['food'])) ? $_SESSION['food']->getImg() : '';
$toppingName = (!empty($_SESSION['topping'])) ? $_SESSION['topping']->getName() : '';
$playerKcal = (!empty($_SESSION['player'])) ? $_SESSION['player']->getKcal() : '';
$playerMoney = (!empty($_SESSION['player'])) ? $_SESSION['player']->getMoney() : '';
$gymImg = (!empty($_SESSION['gym']) ? $_SESSION['gym']->getImg() : '');
$eatCount = (!empty($_SESSION['eatCount'])) ? $_SESSION['eatCount'] : 0;


$no_kcal = (!empty($_SESSION['gym']) && ($_SESSION['player']->getKcal() <= 0)) ? false : true;
$no_money = (!empty($_SESSION['gym']) && ($_SESSION['player']->getMoney() < $_SESSION['gym']->getPrice())) ? false : true;
$no_gym = (!empty($no_kcal) || !empty($no_money) ? false : true);

// $no_order = (!empty($_SESSION['gym']) && !empty($_SESSION['food']) && ($_SESSION['player']->getMoney() < $_SESSION['food']->getPrice())) ? false : true;

$history = (!empty($_SESSION['history'])) ? $_SESSION['history'] : '';
$historyOne = (!empty($_SESSION['historyOne'])) ? $_SESSION['historyOne'] : '';

$instances[] = array($foodName, $foodPrice, $foodKcal, $foodImg, $toppingName, $playerKcal, $playerMoney, $gymImg);
debug('$_SESSIONの中身：' . print_r($_SESSION, true));


?>



<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>debukatu | ゲームプレイ</title>
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>
  <header class="l-header">
  </header>

  <div class="l-site-wrap page-play">
    <main class="l-main">
      <div class="p-action-area l-flex">
        <!-- - - -  メニューエリア  - - - -->
        <section class="p-menu-wrap">
          <div class="p-menu-wrap__img-wrap">
            <!-- - - -  デフォルト、メニュー、ジム画像表示  - - - -->
            <img class="p-menu-wrap__img-wrap-img" src="<?php if (!empty($gymImg) && !empty($gymFlg) && !empty($possibleGym)) :
                                                          echo $gymImg;
                                                        elseif (!empty($foodImg)) :
                                                          echo $foodImg;
                                                        else :
                                                          echo 'images/default.png';
                                                        endif; ?>" alt="">
            <!-- - - -  トッピングおよびジムのコメント表示  - - - -->
            <p class="<?php if (!empty($toppingName)) : echo '';
                      elseif (!empty($gymImg)) : echo 'text-2';
                      endif; ?>">
              <?php if (!empty($toppingName) && !empty($possibleOder)) : echo '〜 トッピング 〜<br />' . $toppingName;
              elseif (!empty($gymImg) && !empty($possibleGym)) : echo 'トレーニング！！';
              endif; ?></p>
          </div>
          <!-- - - -  メニュー、プレイ インフォメーション  - - - -->
          <div class="p-menu-wrap__menu-info">
            <p class="p-menu-wrap__menu-info-food-name" style="<?php if (!empty($historyOne)) : echo 'display:none;';
                                                                endif; ?>">
              <?php if (empty($foodName) && empty($historyOne)) :
                echo 'いらっしゃいませ♡<br />メニューを選択してください';
              elseif (!empty($foodName) && empty($historyOne)) :
                echo $foodName;
              endif; ?>
            </p>
            <?php if (!empty($historyOne)) : ?>
            <p class="p-menu-wrap__menu-info-game-info">
              <?php echo $historyOne; ?>
            </p>
            <?php endif; ?>
            <?php if (!empty($foodName) && empty($historyOne)) : ?>
            <span class="p-menu-wrap__menu-info-food-info"><?php if (!empty($foodName)) : echo number_format($foodPrice) . '&thinsp;円&emsp;/&emsp;' . number_format($foodKcal) . '&thinsp;kcal';
                                                              endif; ?></span>
            <?php endif; ?>
          </div>
        </section>
        <!-- - - -  右サイドバー  - - - -->
        <section class="p-side-info">
          <div class="p-side-info__board">
            <p class="p-side-info__board-menu-name">
              <?php if (!empty($startFlg)) :
                echo '<br />Welcome!';
              elseif (!empty($foodName) && empty($startFlg)) :
                echo $foodName;
              endif; ?>
            </p>
            <p class="p-side-info__board-menu-price"><?php if (!empty($foodPrice)) : echo number_format($foodPrice) . '&thinsp;円';
                                                      endif; ?></p>
            <p class="p-side-info__board-menu-kcal"><?php if (!empty($foodKcal)) : echo number_format($foodKcal) . '&thinsp;kcal';
                                                    endif; ?></p>
            <div class="p-side-info__board-topping">
              <p class="p-side-info__board-topping-title">
                <?php if (!empty($toppingName)) :
                  echo '*トッピング追加*';
                endif; ?>
              </p>
              <p class="p-side-info__board-topping-name">
                <?php if (!empty($startFlg)) :
                  echo '&thinsp;デブ活レストラン<br /><br />へようこそ！';
                endif; ?>
                <?php if (!empty($toppingName) && empty($orderFlg)) :
                  echo '注文してからの<br />お楽しみ♡';
                endif; ?>
                <?php if (!empty($toppingName) && !empty($orderFlg)) :
                  echo $toppingName;
                endif; ?>
              </p>
            </div>
          </div>
          <div class="p-side-info__player-info">
            <p class=""><?php if (isset($playerKcal)) : echo 'カロリー： ' . number_format($playerKcal) . ' / ' . number_format($_SESSION['player']->getMaxKcal()) . '&thinsp;kcal';
                        endif; ?></p>
            <p><?php if (isset($playerMoney)) : echo '所持金： ' . number_format($playerMoney) . ' / ' . number_format($_SESSION['maxMoney']) . '&thinsp;円';
                endif; ?></p>
            <p><?php if (isset($playerMoney)) : echo '食べた品数： ' . number_format($eatCount) . '&thinsp;品';
                endif; ?></p>
          </div>
        </section>
      </div>
      <!-- - - -  ボタンエリア  - - - -->
      <form action="" method="post" class="p-form-area ">
        <section class="p-btn-wrap">
          <ul class="l-flex l-flex-xyc l-flex-xar">
            <li>
              <input class="p−btn-circle ｃ−btn-circle ｃ−btn-circle--yg u-pl15 u-pr15" type="submit" name="select"
                value="メニューを選択">
            </li>
            <li>
              <input class="p−btn-circle ｃ−btn-circle ｃ−btn-circle--yg u-pl15 u-pr15" type="submit" name="topping"
                value="トッピング追加">
            </li>
            <li>
              <input class="p−btn-circle ｃ−btn-circle ｃ−btn-circle--yg u-pl20 u-pr20 js-order" type="submit"
                name="order" value="注文して食べる">
            </li>
            <li>
              <input class="p−btn-circle ｃ−btn-circle ｃ−btn-circle--yg js-goGym" type="submit" name="gym" value="ジムへ行く">
            </li>
            <li>
              <input class="p−btn-circle ｃ−btn-circle ｃ−btn-circle--yg" type="submit" name="reset" value="リセット">
            </li>
            <li>
              <input class="p−btn-circle ｃ−btn-circle ｃ−btn-circle--yg" type="submit" name="gameEnd" value="ゲーム終了">
            </li>
          </ul>
        </section>
        <!-- - - -  履歴エリア  - - - -->
        <section class="p-history">
          <h2 class="p-history__title">○ ○ ○ デ ブ 活 り れ き ○ ○ ○</h2>
          <div class="p-history__history js-history">
            <p><?php if (!empty($history)) : echo $history;
                endif; ?></p>
          </div>
        </section>

      </form>

    </main>
  </div>
  <script src="jquery.min.js"></script>
  <script src="main.js"></script>
</body>

<footer id="footer">
</footer>

</html>