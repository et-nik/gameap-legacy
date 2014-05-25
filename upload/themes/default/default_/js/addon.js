$_speed_online ="slow";
$_speed_menu ="fast";
$(document).ready(function(){
   $("#menu_contener > ul > li:has(ul)").bind("mouseenter", function(){
     $("#menu_contener > ul > li:has(ul)").find("ul").css({"display":"none"});
     $(this).find("ul:eq(0)").slideDown($_speed_menu);
     }).bind("mouseleave", function(){
       $(this).find("ul:eq(0)").css({"display":"none"});
       }).addClass("folder");

       $("#menu_contener > ul > li > ul > li:has(ul)").bind("mouseenter", function(){
        /* var offset =  $(this).find("a:eq(0)").height(); */
        /*  var nh=   offset-2;
         $(this).css({"height":nh+"px"});
         $(this).css({"height":offset+"px"}); */
     $("#menu_contener > ul > li > ul > li:has(ul)").find("ul").css({"display":"none"});
                     /* $(this).find("ul:eq(0)").top=-offset;   */

    // $(this).find("ul:eq(0)").slideDown($_speed_menu);
     $(this).find("ul:eq(0)").css({"display":"block"});
     }).bind("mouseleave", function(){
       $(this).find("ul:eq(0)").css({"display":"none"});
       });




});
$(document).ready(function(){
   $(".online_block > .top > a").click(function(){
         if($(".online_block > .center").css("display")=="none"){
            $(".online_block > .center").slideDown($_speed_online);
         } else{
              $(".online_block > .center").slideUp($_speed_online);
         }
   });
});

/*$(function() {
var zIndexNumber = 1000;
$('div').each(function() {
$(this).css('zIndex', zIndexNumber);
zIndexNumber -= 10;
});
    *
});  */
