$(document).ready(function(){
    var first = $("li.is-file:first").attr("data-tab");
    $("."+first).show();

    $(".left-nav").click(function(event){
        $(".left-nav li").removeClass("selected");
        var li = $(event.target);
        if( li.parent().is("li"))
            li = li.parent();

        if( li.is("li") ){
            var class_tab = $("."+li.attr("data-tab"));//.replace(".","-"));
            if( class_tab.length > 0 )
            {
                $(".class_tap").hide();
                class_tab.show();
            }
            li.addClass("selected");
            var ul = li.next("li").find("ul");
            if( ul.length > 0 )
            {
                if(!ul.is(":hidden")) {
                    ul.slideUp(function(){
                        li.next("li").hide();
                        if( li.hasClass("bg") ){
                            li.find("img").eq(0).attr("src","img/a.png");
                        }
                    });
                }else{
                    li.next("li").show();
                    ul.slideDown("slow");
                    if( li.hasClass("bg") ){
                        li.find("img").eq(0).attr("src","img/d.png");
                    }

                }
            }
        }
    });
    $(".left-nav").on("mouseover",function(event){
        if( $(event.target).is("li") ){
            $(event.target).addClass("hover");
        }
        if( $(event.target).parent().is("li") ){
            $(event.target).parent().addClass("hover");
        }
    });
    $(".left-nav").on("mouseout",function(event){
        if( $(event.target).is("li") ){
            $(event.target).removeClass("hover");
        }
        if( $(event.target).parent().is("li") ){
            $(event.target).parent().removeClass("hover");
        }
    });

    var is_drag = false;
    var win_width = $(".header").width();
    var left  = $(".left-nav");
    var mid   = $(".drag");
    var right = $(".right-content");
    var mid_width = win_width*0.01;

    $(".drag").mousedown(function(){
        is_drag = true;
    });
    $(document).mouseup(function(){
        is_drag = false;
    });
    $(document).mousemove(function(event){
        if( is_drag ){
            var left_width = event.clientX;
            left.css("width",left_width+"px");
            mid.css("left",left_width+"px");
            right.css("width",(win_width-left_width-mid_width)+"px");
            right.css("left",(left_width+mid_width)+"px");

        }
    })
});
