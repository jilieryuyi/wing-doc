$(document).ready(function(){
    //console.log(window.location.hash);
    //history.pushState(null, null, window.location.href+"#!test")
    var hash = window.location.hash;
    if( !hash )
        hash = $("li.is-file:first").attr("data-tab");
    else
        hash = hash.substr(1)
    //console.log(hash);
    $("."+ hash).show();
    var litap = $('.li-'+hash);

    if( litap.length > 0 ) {
        litap.addClass("selected");

        $("html,body").stop(true);
        $(".left-nav").animate({scrollTop: litap.offset().top - 130}, 1000);
    }


    $(".left-nav").click(function(event){
        $(".left-nav li").removeClass("selected");
        var li = $(event.target);
        if( li.parent().is("li"))
            li = li.parent();

        if( li.is("li") ){
            var data_tab = li.attr("data-tab");


            var class_tab = $("."+data_tab);//.replace(".","-"));
            if( class_tab.length > 0 )
            {
                $(".class_tap").hide();
                class_tab.show();
                history.pushState(null, null,
                    window.location.origin+
                    window.location.pathname+
                    "#"+data_tab)
            }
            li.addClass("selected");
            var ul = li.next("li").children("ul");
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
    var search = $(".search");

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
            search.css("width",left_width+"px");
            mid.css("left",left_width+"px");
            right.css("width",(win_width-left_width-mid_width)+"px");
            right.css("left",(left_width+mid_width)+"px");

        }
    })
});

