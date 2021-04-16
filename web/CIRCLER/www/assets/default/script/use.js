var x, i, j, selElmnt, a, b, c;
/*look for any elements with the class "custom-select":*/
x = document.getElementsByClassName("custom-select");
for (i = 0; i < x.length; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
    /*for each element, create a new DIV that will act as the selected item:*/
    a = document.createElement("DIV");
    a.setAttribute("class", "select-selected");
    a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
    x[i].appendChild(a);
    /*for each element, create a new DIV that will contain the option list:*/
    b = document.createElement("DIV");
    b.setAttribute("class", "select-items select-hide");
    for (j = 1; j < selElmnt.length; j++) {
        /*for each option in the original select element,
        create a new DIV that will act as an option item:*/
        c = document.createElement("DIV");
        c.innerHTML = selElmnt.options[j].innerHTML;
        c.addEventListener("click", function(e) {
            /*when an item is clicked, update the original select box,
            and the selected item:*/
            var y, i, k, s, h;
            s = this.parentNode.parentNode.getElementsByTagName("select")[0];
            h = this.parentNode.previousSibling;
            for (i = 0; i < s.length; i++) {
                if (s.options[i].innerHTML == this.innerHTML) {
                    s.selectedIndex = i;
                    h.innerHTML = this.innerHTML;
                    y = this.parentNode.getElementsByClassName("same-as-selected");
                    for (k = 0; k < y.length; k++) {
                        y[k].removeAttribute("class");
                    }
                    this.setAttribute("class", "same-as-selected");
                    break;
                }
            }
            h.click();
        });
        b.appendChild(c);
    }
    x[i].appendChild(b);
    a.addEventListener("click", function(e) {
        /*when the select box is clicked, close any other select boxes,
        and open/close the current select box:*/
        e.stopPropagation();
        closeAllSelect(this);
        this.nextSibling.classList.toggle("select-hide");
        this.classList.toggle("select-arrow-active");
    });
}
function closeAllSelect(elmnt) {
    /*a function that will close all select boxes in the document,
    except the current select box:*/
    var x, y, i, arrNo = [];
    x = document.getElementsByClassName("select-items");
    y = document.getElementsByClassName("select-selected");
    for (i = 0; i < y.length; i++) {
        if (elmnt == y[i]) {
            arrNo.push(i)
        } else {
            y[i].classList.remove("select-arrow-active");
        }
    }
    for (i = 0; i < x.length; i++) {
        if (arrNo.indexOf(i)) {
            x[i].classList.add("select-hide");
        }
    }
}
/*if the user clicks anywhere outside the select box,
then close all select boxes:*/
document.addEventListener("click", closeAllSelect);

var page = 2;
$(window).scroll(function () {
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {

        var contents_params = {pageNum : page , pageSize : 10 , start_date : $('form.use_form input[name=start_date]').val() , end_date : $('form.use_form input[name=end_date]').val() , point_type : $('form.use_form select[name=point_type] option:selected').val()};
        var contents_data = $.runsync('/mypage/usage' ,contents_params , 'json' , false);
        //console.log(contents_data);
        //return;
        if(contents_data.data.rows.length > 0){
            var contents_html = "";
            $(contents_data.data.rows).each(function (key ,val) {

                contents_html += '<tr>\n' +
                    '                <td>\n' +
                    '                    <div class="'+ (val.point_type == 1 ? "useway" : "saveway" )+'">\n' +
                    '                        '+ (val.point_type == 1 ? "사용" : "적립" ) +'                    </div>\n' +
                    '                </td>\n' +
                    '                <td class="align_left"><a href="#">'+ val.info +'</a></td><td>'+ val.point_type_str +'</td><td>'+ val.regdate +'</td><td><span class="usetxt">'+ val.number_format_point_str +'</span></td>\n' +
                    '            </tr>';

            });
            if(contents_html){
                //console.log(contents_html)
                $('.usagelist  > tbody:last').append(contents_html);
                ++page;
            }

        }
        //$("ul.contentsList").append('<div class="big-box"><h1>Page ' + page + '</h1></div>');

    }
});