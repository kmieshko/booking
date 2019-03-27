$(document).ready(function() {
    $("#btn").click(
        function(){
            sendAjaxForm('result_form', 'ajax_form', '/main/ajax');
            return false;
        }
    );
});

function extractJSON(str) {
    var firstOpen, firstClose, candidate;
    firstOpen = str.indexOf('{', firstOpen + 1);
    while(firstOpen != -1) {
        firstClose = str.lastIndexOf('}');
        if(firstClose <= firstOpen) {
            return null;
        }
        while(firstClose > firstOpen) {
            candidate = str.substring(firstOpen, firstClose + 1);
            try {
                var res = JSON.parse(candidate);
                return res;
            }
            catch(e) {
            }
            firstClose = str.substr(0, firstClose).lastIndexOf('}');
        }
        firstOpen = str.indexOf('{', firstOpen + 1);
    }
}

function sendAjaxForm(result_form, ajax_form, url) {
    $.ajax({
        url:     url,
        type:     "POST",
        dataType: "html",
        data: $("#"+ajax_form).serialize(),
        success: function(response) {
            result = extractJSON(response);
            $('#result_form').html(result.html);
        },
        error: function(response) {
            $('#result_form').html('Error.');
        }
    });
}
