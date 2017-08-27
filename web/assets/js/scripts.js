/**
 * Created by khuen on 19-Aug-17.
 */

var lastUserInput = "";

/**
 * Input message to the bot
 * @param el
 * @param event
 */
function inputMessage(el, event) {
    var botres = jQuery("#botres");
    var botport = jQuery("#botport");

    el = jQuery(el);
    var userInput = el.val();

    if (event.keyCode === 38) {
        el.val(lastUserInput);
    }

    if (event.keyCode === 13) {

        if (!event.shiftKey) {
            // Block default action
            event.preventDefault();

            // Send the message to server and request for answer
            botres.append("<span class='conversation user-input'><b>[User] >>></b> " + userInput + "</span><br/>");
            botres.append("<div id='tenor'></div>");
            botres.scrollTop(botres.scrollHeight);
            botres.animate({ scrollTop: botres[0].scrollHeight}, 500);

            // Bot response
            jQuery.ajax({
                method: 'POST',
                url: jQuery("#answer-url").val(),
                data: {input: userInput},
                dataType: 'json',
                success: function(data) {
                    console.log(data.answer);
                    botres.append("<span class='conversation bot-answer'><b>[Miri] >>></b> " + data.answer + "</span><br/>");
                    var portrait = data.emotion + ".png";
                    jQuery("#tenor").remove();
                    botres.animate({ scrollTop: botres[0].scrollHeight}, 500);
                    botport.css("background", 'url(../assets/portraits/' + portrait + ')');
                }
            });

            // Clear input
            el.val("");
        } else {
            el.append("<br/>");
        }

        lastUserInput = userInput;
    }


}