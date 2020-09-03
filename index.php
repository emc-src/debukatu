<?php

require('function.php');

if (!empty($_POST)) :
  debug('ゲームスタート。プレイページへ遷移します。');
  $_SESSION['startFlg'] = true;
  header("Location:play.php");
  exit();
endif;

?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>debukatu</title>
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>
  <header class="l-header">
  </header>

  <div class="l-site-wrap page-index">
    <main class="p-index u-m-auto">
      <div class="p-index__title u-m-auto u-txt-c">
        <img class="p-index__title-img" src="images/titleImg.png" alt="デブカツ！！">
      </div>
      <div class="p-index__body u-txt-c">
        <p>ひたすら食べまくって</p>
        <p>所持金がなくなるのが先か？</p>
        <p>カロリーオーバーになるのが先か？</p>
      </div>
      <div class="p-index__footer l-flex l-flex-xyc u-txt-c">
        <img class="p-index__img-fork p-index__img-fork--left" src="images/fork.png" alt="フォーク">
        <form action="" method="post" class="game-start">
          <div class="p-index__footer-btn-wrap">
            <input class="c-btn c-btn-lg c-btn-lg--y u-txt-c" type="submit" name="game_start"
              value="▶&emsp;GAME&ensp;START">
          </div>
        </form>
        <img class="p-index__img-fork p-index__img-fork--right" src="images/fork.png" alt="フォーク">
      </div>
    </main>
  </div>
  <script src="jquery.min.js"></script>
  <script src="main.js"></script>
</body>

<footer id="footer">
</footer>

</html>