$(document).ready(function(){
    //dialog窗口
    $(".dialog-show").click(function(){
        console.log(123);
        $("#dialog").removeClass("dialog-none")
    });
    $(".dialog-close").click(function(){
        $("#dialog").addClass("dialog-none")
    });
    $("#dialog-bottom-button").click(function(){
        $("#dialog").addClass("dialog-none")
    });

        //$("button").click(function(){
        //    $(this).css("border-color","#dadce6")
        //});
     $(".cc-btn").click(function(){
         $(".cc-box").toggle();
     });


})