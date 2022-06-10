$(function() {
    // Fix footer
    fixFooter();
    setInterval(fixFooter, 500);
    
    // Enables tooltip
    $("[data-toggle='tooltip']").tooltip();
    
    $(".price").mask('#,##0.00', { reverse: true });
})

function fixFooter() {
    if ($("body").hasClass("mCS_no_scrollbar") || $("#mCSB_1_container").hasClass("mCS_no_scrollbar_y")) {
        $("footer").css("position", "fixed").css("bottom", 0)
        $("main").height($('footer').offset().top-50)
    }
    else {
        $("footer").css("position", "")
        $("main").height("100%")
    }
}
