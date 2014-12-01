function toggle_tabs(tab) {
    //hide "page data"'s buttons:
    $("#show_btn").hide();
    $("#show_btn_2").hide();

    var ul_menu = document.getElementById("tabs");
    var lis = ul_menu.children;
    var temp_id, temp_id2, temp_id3, form;
    for (var i = 0; i < lis.length; i++) {
        temp_id = lis[i].id;
        temp_id2 = temp_id + "a";
        temp_id3 = temp_id + "f";

        if (temp_id != tab.id) {
            document.getElementById(temp_id).setAttribute("class", "");
            document.getElementById(temp_id2).setAttribute("class", "hidden_tab_content");

            form = document.getElementById(temp_id3);
            if (form) {
                form.reset();
            }

        } else {
            document.getElementById(temp_id).setAttribute("class", "active");
            document.getElementById(temp_id2).setAttribute("class", "active_tab_content");
        }
    }
}