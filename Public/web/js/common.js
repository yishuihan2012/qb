function reCall(){//自适应屏幕布局
  var html = document.documentElement;
  var screenWidth = html.clientWidth;
  var fz = screenWidth/750*100 + "px";
  html.style.fontSize=fz;
}
function showPage(){
  $("body").css({'visibility':"visible"});
}
$(function(){
  var s_height = $(document).height();
  //var h_height = $("header.common").height();
  //s_height = s_height-h_height;
  $(".help-container").height(s_height);
  $(".total-btn").click(function(){
    $(this).addClass('total-active').siblings('.total-btn').removeClass('total-active');
  });
  //累计分润
  $("#fenrunNav li,#fenrunNav002 li").click(function(){
  	$(this).find("a").css({"color":"#1e82d2","border-bottom":"1px solid #1e82d2"});
  	$(this).siblings().find("a").css({"color":"#333","border-bottom":"none"});
  	var index = $(this).index();
  	if(index==0){
  	  $(".guli-box").hide();
  	  $(".fenrun-box").show();
  	}else{
  	  $(".fenrun-box").hide();
  	  $(".guli-box").show();
  	}
  });
  //会员返佣
  $("#fanyongNav li").click(function(){
    $(this).find("a").css({"color":"#1e82d2","border-bottom":"1px solid #1e82d2"});
    $(this).siblings().find("a").css({"color":"#333","border-bottom":"none"});
    var index = $(this).index();
    if(index==0){
      $(".zhijie-box").hide();
      $(".jianjie-box").show();
    }else{
      $(".zhijie-box").hide();
      $(".jianjie-box").show();
    }
  });
  //贷款大全creditListNav
  $("#creditListNav li").click(function(){
    $(this).find("a").css({"color":"#1e82d2","border-bottom":"1px solid #1e82d2"});
    $(this).siblings().find("a").css({"color":"#333","border-bottom":"none"});
    var index = $(this).index();
    if(index==0){
      $(".credit-box").hide();
      $(".hot-box").show();
    }else if(index==1){
      $(".credit-box").hide();
      $(".quick-loan-box").show();
    }else if(index==2){
      $(".credit-box").hide();
      $(".ease-of-money-box").show();
    }else if(index==3){
      $(".credit-box").hide();
      $(".large-amount-box").show();
    }
  });
  //贷款通道

  $(".application-instr-btn").click(function(){
    $(this).parent().next(".application-instr-detail").slideToggle("fast");
    $(this).parent().parent().siblings().find(".application-instr-detail").slideUp("fast");
    $(this).parent().parent().siblings().find(".application-instr-detail").slideUp("fast");
  });
  $(".internal-card-btn").click(function(){
    $(this).parent().next(".application-instr-detail").slideToggle("fast");
    $(this).parent().parent().siblings().find(".application-instr-detail").slideUp("fast");
    $(this).parent().parent().siblings().find(".application-instr-detail").slideUp("fast");
  });
  $(".application-close-btn").click(function(){
    $(this).parent().slideUp("fast");
  });
  // 素材显示更多
  $(".share-sucai-show-more").click(function(){
    $(this).hide();
    $(this).parent().find(".share-sucai-show-hide").show();
    $(this).parent().find(".share-sucai-dest").removeClass("hid-text");
  });
  //素材收起
   $(".share-sucai-show-hide").click(function(){
    $(this).hide();
    $(this).parent().find(".share-sucai-show-more").show();
    $(this).parent().find(".share-sucai-dest").addClass("hid-text");
  });
});
