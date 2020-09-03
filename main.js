$(function () {
  // １アクションの結果スクロール表示
  var $areaScroll = $(".p-menu-wrap__menu-info");
  $areaScroll.delay(100).animate(
    {
      scrollTop: $areaScroll.height(),
    },
    6000
  );

  // 結果履歴スクロール表示
  var $hisScroll = $(".js-history");
  $hisScroll.animate(
    {
      scrollTop: $hisScroll[0].scrollHeight,
    },
    "fast"
  );

  // jQ閉じカッコ
});
